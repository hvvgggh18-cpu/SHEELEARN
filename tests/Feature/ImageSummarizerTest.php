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

class ImageSummarizerTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_upload_uses_vision_model_when_configured(): void
    {
        Storage::fake('public');

        config(['services.groq.vision_model' => 'vision-test-model']);

        $user = User::factory()->create();
        $this->actingAs($user);

        // Create a fake PNG file from a tiny base64-encoded 1x1 pixel image (avoids GD dependency)
        $tinyPng = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR4nGNgYAAAAAMAASsJTYQAAAAASUVORK5CYII='
        );
        $file = UploadedFile::fake()->createWithContent('document-photo.png', $tinyPng);

        $uploadResponse = $this->postJson(route('ai-chat.upload'), [
            'file' => $file,
        ]);

        $uploadResponse->assertOk();
        $uploadResponse->assertJson(['success' => true]);

        $document = Document::first();
        $this->assertNotNull($document);
        $this->assertSame('uploaded', $document->status);

        $mockAi = Mockery::mock(AIServiceInterface::class);
        $mockAi->shouldReceive('chat')
            ->once()
            ->withArgs(function (array $messages, string $model, string $mode, $attachment) {
                // Ensure we are using the configured vision model
                $this->assertSame('vision-test-model', $model);
                $this->assertSame('summarize', $mode);
                // attachment should be present for images
                $this->assertIsArray($attachment);
                $this->assertArrayHasKey('path', $attachment);
                $this->assertArrayHasKey('mime', $attachment);
                return true;
            })
            ->andReturn('Image summary result');

        $mockUsage = Mockery::mock(AIUsageLimiterService::class);
        $mockUsage->shouldReceive('ensureCanUse')->once()->with($user);
        $mockUsage->shouldReceive('incrementUsage')->once()->with($user);

        $this->app->instance(AIServiceInterface::class, $mockAi);
        $this->app->instance(AIUsageLimiterService::class, $mockUsage);

        $processResponse = $this->postJson(route('ai-chat.documents.process', $document));

        $processResponse->assertOk();
        $processResponse->assertJson(['success' => true]);

        $document->refresh();
        $this->assertSame('processed', $document->status);
        $this->assertSame('Image summary result', $document->summary);
    }
}
