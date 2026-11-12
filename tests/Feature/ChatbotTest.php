<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    public function test_chatbot_ask_returns_reply(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($user)->postJson(route('chatbot.ask'), [
            'message' => 'articles disponibles',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['reply', 'suggestions']);
    }

    public function test_chatbot_stats(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);
        $response = $this->actingAs($user)->postJson(route('chatbot.ask'), [
            'message' => 'statistiques',
        ]);
        $response->assertOk();
        $this->assertStringContainsString('Tableau de bord', $response->json('reply'));
    }
}
