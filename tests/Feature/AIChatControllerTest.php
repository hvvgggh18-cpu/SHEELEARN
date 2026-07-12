<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIChatControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_clear_ai_chat_history(): void
    {
        $user = User::factory()->create();
        $conversation = ChatConversation::create([
            'user_id' => $user->id,
            'title' => 'Test Conversation',
        ]);

        ChatMessage::create([
            'user_id' => $user->id,
            'conversation_id' => $conversation->id,
            'role' => 'user',
            'content' => 'Hello',
        ]);

        $this->actingAs($user);

        $response = $this->delete(route('ai-chat.clear-history'));

        $response->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('chat_conversations', ['id' => $conversation->id]);
        $this->assertDatabaseMissing('chat_messages', ['conversation_id' => $conversation->id]);
    }

    public function test_build_prompt_returns_raw_input(): void
    {
        $controller = new \App\Http\Controllers\AIChatController();
        $reflection = new \ReflectionMethod($controller, 'buildPrompt');
        $reflection->setAccessible(true);

        $input = 'Find the number of positive integers n such that n^2 + 1 is divisible by 5.';
        $prompt = $reflection->invoke($controller, 'math', $input);

        $this->assertSame($input, $prompt);
    }
}
