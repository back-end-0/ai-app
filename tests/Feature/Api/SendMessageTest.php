<?php

use App\Ai\Agents\ChatBot;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\ConversationStore;

beforeEach(function () {
    $this->store = app(ConversationStore::class);
});

test('can send a message and receive an AI response', function () {
    ChatBot::fake(['Hello! How can I help you today?']);

    $conversationId = $this->store->storeConversation(null, 'Test Chat');

    $this->postJson("/api/conversations/{$conversationId}/messages", [
        'message' => 'Hello!',
    ])
        ->assertSuccessful()
        ->assertJsonPath('data.conversation_id', $conversationId)
        ->assertJsonPath('data.response', 'Hello! How can I help you today?');
});

test('persists both user and assistant messages', function () {
    ChatBot::fake(['I am doing well, thanks!']);

    $conversationId = $this->store->storeConversation(null, 'Test Chat');

    $this->postJson("/api/conversations/{$conversationId}/messages", [
        'message' => 'How are you?',
    ])->assertSuccessful();

    $messages = DB::table('agent_conversation_messages')
        ->where('conversation_id', $conversationId)
        ->orderBy('created_at')
        ->get();

    expect($messages)->toHaveCount(2);
    expect($messages[0]->role)->toBe('user');
    expect($messages[0]->content)->toBe('How are you?');
    expect($messages[1]->role)->toBe('assistant');
    expect($messages[1]->content)->toBe('I am doing well, thanks!');
});

test('validates message is required', function () {
    $conversationId = $this->store->storeConversation(null, 'Test Chat');

    $this->postJson("/api/conversations/{$conversationId}/messages", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

test('validates message is not too long', function () {
    $conversationId = $this->store->storeConversation(null, 'Test Chat');

    $this->postJson("/api/conversations/{$conversationId}/messages", [
        'message' => str_repeat('a', 10001),
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['message']);
});

test('returns 404 for nonexistent conversation', function () {
    ChatBot::fake(['response']);

    $this->postJson('/api/conversations/nonexistent-id/messages', [
        'message' => 'Hello!',
    ])->assertNotFound();
});

test('agent is prompted with the correct message', function () {
    ChatBot::fake(['Mocked response']);

    $conversationId = $this->store->storeConversation(null, 'Test Chat');

    $this->postJson("/api/conversations/{$conversationId}/messages", [
        'message' => 'What is Laravel?',
    ])->assertSuccessful();

    ChatBot::assertPrompted('What is Laravel?');
});
