<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use App\Services\AIServiceInterface;
use App\Services\AIUsageLimiterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class DocumentSummarizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_upload_document_and_generate_summary(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $this->actingAs($user);

        $documentText = 'Photosynthesis converts light energy into chemical energy. It includes light reactions and the Calvin cycle.';
        $file = UploadedFile::fake()->createWithContent('photosynthesis.txt', $documentText);

        $uploadResponse = $this->postJson(route('ai-chat.upload'), [
            'file' => $file,
        ]);

        $uploadResponse->assertOk();
        $uploadResponse->assertJson(['success' => true]);

        $document = Document::first();
        $this->assertNotNull($document);
        $this->assertSame('photosynthesis.txt', $document->original_name);
        $this->assertSame($user->id, $document->user_id);
        $this->assertSame('processed', $document->status);
        $this->assertStringContainsString('Photosynthesis converts light energy', $document->extracted_text);

        $mockAi = Mockery::mock(AIServiceInterface::class);
        $mockAi->shouldReceive('chat')
            ->once()
            ->withArgs(function (array $messages, string $model, string $mode, $attachment) use ($documentText) {
                $this->assertSame('summarize', $mode);
                $this->assertIsString($model);
                $this->assertNull($attachment);

                $combined = implode(' ', array_map(fn($message) => $message['content'], $messages));
                $this->assertStringContainsString('Photosynthesis converts light energy into chemical energy.', $combined);
                $this->assertStringContainsString('Summarize the information accurately and concisely', $combined);

                return true;
            })
            ->andReturn('Photosynthesis turns light energy into chemical energy, using light reactions and the Calvin cycle.');

        $mockUsage = Mockery::mock(AIUsageLimiterService::class);
        $mockUsage->shouldReceive('ensureCanUse')->once()->with($user);
        $mockUsage->shouldReceive('incrementUsage')->once()->with($user);

        $this->app->instance(AIServiceInterface::class, $mockAi);
        $this->app->instance(AIUsageLimiterService::class, $mockUsage);

        $processResponse = $this->postJson(route('ai-chat.documents.process', $document));

        $processResponse->assertOk();
        $processResponse->assertJson(['success' => true]);

        $document->refresh();
        $this->assertSame('Photosynthesis turns light energy into chemical energy, using light reactions and the Calvin cycle.', $document->summary);
        $this->assertSame('processed', $document->status);
    }
}
