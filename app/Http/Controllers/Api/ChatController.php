<?php

namespace App\Http\Controllers\Api;

use App\Ai\Agents\ChatBot;
use App\Http\Controllers\Controller;
use App\Http\Requests\SendMessageRequest;
use App\Http\Requests\StoreConversationRequest;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\MessageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Contracts\ConversationStore;

class ChatController extends Controller
{
    public function __construct(
        private ConversationStore $conversationStore,
    ) {}

    /**
     * List all conversations, newest first.
     */
    public function index(): AnonymousResourceCollection
    {
        $conversations = DB::table('agent_conversations')
            ->select('id', 'title', 'user_id', 'created_at', 'updated_at')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return ConversationResource::collection($conversations);
    }

    /**
     * Create a new conversation.
     */
    public function store(StoreConversationRequest $request): ConversationResource
    {
        $conversationId = $this->conversationStore->storeConversation(
            userId: null,
            title: $request->validated('title') ?? 'New Conversation',
        );

        $conversation = DB::table('agent_conversations')
            ->select('id', 'title', 'user_id', 'created_at', 'updated_at')
            ->where('id', $conversationId)
            ->first();

        return new ConversationResource($conversation);
    }

    /**
     * Show a conversation with its messages.
     */
    public function show(string $conversationId): JsonResponse
    {
        $conversation = DB::table('agent_conversations')
            ->select('id', 'title', 'user_id', 'created_at', 'updated_at')
            ->where('id', $conversationId)
            ->first();

        if (! $conversation) {
            abort(404, 'Conversation not found.');
        }

        $messages = DB::table('agent_conversation_messages')
            ->select('id', 'conversation_id', 'role', 'content', 'usage', 'created_at')
            ->where('conversation_id', $conversationId)
            ->orderBy('created_at')
            ->get();

        return response()->json([
            'data' => [
                ...(new ConversationResource($conversation))->toArray(request()),
                'messages' => MessageResource::collection($messages),
            ],
        ]);
    }

    /**
     * Delete a conversation and its messages.
     */
    public function destroy(string $conversationId): JsonResponse
    {
        $exists = DB::table('agent_conversations')
            ->where('id', $conversationId)
            ->exists();

        if (! $exists) {
            abort(404, 'Conversation not found.');
        }

        DB::table('agent_conversation_messages')
            ->where('conversation_id', $conversationId)
            ->delete();

        DB::table('agent_conversations')
            ->where('id', $conversationId)
            ->delete();

        return response()->json(null, 204);
    }

    /**
     * Send a message to a conversation and get the AI response.
     */
    public function sendMessage(SendMessageRequest $request, string $conversationId): JsonResponse
    {
        $conversation = DB::table('agent_conversations')
            ->select('id', 'user_id')
            ->where('id', $conversationId)
            ->first();

        if (! $conversation) {
            abort(404, 'Conversation not found.');
        }

        try {
            $agent = new ChatBot;

            $response = $agent
                ->continue($conversationId, as: (object) ['id' => $conversation->user_id])
                ->prompt($request->validated('message'));
        } catch (\Throwable $e) {
            $message = $e->getMessage();

            if (str_contains($message, 'rate limit')) {
                return response()->json(['message' => 'Rate limited. Please wait a moment and try again.'], 429);
            }

            return response()->json(['message' => 'AI provider error: '.$message], 503);
        }

        $assistantMessage = DB::table('agent_conversation_messages')
            ->select('id', 'conversation_id', 'role', 'content', 'usage', 'created_at')
            ->where('conversation_id', $conversationId)
            ->where('role', 'assistant')
            ->orderByDesc('created_at')
            ->limit(1)
            ->first();

        return response()->json([
            'data' => [
                'conversation_id' => $conversationId,
                'response' => (string) $response,
                'message' => $assistantMessage
                    ? (new MessageResource($assistantMessage))->toArray(request())
                    : null,
                'usage' => $response->usage->toArray(),
            ],
        ]);
    }
}
