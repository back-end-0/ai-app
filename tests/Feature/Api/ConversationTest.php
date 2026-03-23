<?php

use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\ConversationStore;

beforeEach(function () {
    $this->store = app(ConversationStore::class);
});

test('can list conversations', function () {
    $this->store->storeConversation(null, 'First Chat');
    $this->store->storeConversation(null, 'Second Chat');
    $this->store->storeConversation(null, 'Third Chat');

    $this->getJson('/api/conversations')
        ->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure(['data' => [['id', 'title', 'created_at', 'updated_at']]]);
});

test('conversations are paginated', function () {
    for ($i = 0; $i < 20; $i++) {
        $this->store->storeConversation(null, "Chat $i");
    }

    $this->getJson('/api/conversations')
        ->assertSuccessful()
        ->assertJsonCount(15, 'data')
        ->assertJsonStructure(['meta' => ['current_page', 'last_page', 'total']]);
});

test('can create a conversation with a title', function () {
    $this->postJson('/api/conversations', ['title' => 'My Chat'])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'My Chat');

    expect(DB::table('agent_conversations')->count())->toBe(1);
});

test('can create a conversation without a title', function () {
    $this->postJson('/api/conversations')
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'New Conversation');
});

test('can show a conversation with messages', function () {
    $conversationId = $this->store->storeConversation(null, 'Test Chat');

    DB::table('agent_conversation_messages')->insert([
        'id' => fake()->uuid(),
        'conversation_id' => $conversationId,
        'user_id' => null,
        'agent' => 'App\Ai\Agents\ChatBot',
        'role' => 'user',
        'content' => 'Hello!',
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '[]',
        'meta' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->getJson("/api/conversations/{$conversationId}")
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Test Chat')
        ->assertJsonCount(1, 'data.messages');
});

test('returns 404 for nonexistent conversation', function () {
    $this->getJson('/api/conversations/nonexistent-id')
        ->assertNotFound();
});

test('can delete a conversation', function () {
    $conversationId = $this->store->storeConversation(null, 'To Delete');

    DB::table('agent_conversation_messages')->insert([
        'id' => fake()->uuid(),
        'conversation_id' => $conversationId,
        'user_id' => null,
        'agent' => 'App\Ai\Agents\ChatBot',
        'role' => 'user',
        'content' => 'Hello!',
        'attachments' => '[]',
        'tool_calls' => '[]',
        'tool_results' => '[]',
        'usage' => '[]',
        'meta' => '[]',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->deleteJson("/api/conversations/{$conversationId}")
        ->assertNoContent();

    expect(DB::table('agent_conversations')->where('id', $conversationId)->exists())->toBeFalse();
    expect(DB::table('agent_conversation_messages')->where('conversation_id', $conversationId)->exists())->toBeFalse();
});

test('returns 404 when deleting nonexistent conversation', function () {
    $this->deleteJson('/api/conversations/nonexistent-id')
        ->assertNotFound();
});
