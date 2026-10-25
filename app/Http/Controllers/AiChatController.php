<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAiChatMessageRequest;
use App\Http\Requests\StoreAiChatSessionRequest;
use App\Models\AiChatSession;
use App\Services\AiChatService;
use Illuminate\Http\JsonResponse;

class AiChatController extends Controller
{
    public function __construct(
        protected AiChatService $aiChatService
    ) {}

    public function storeSession(StoreAiChatSessionRequest $request): JsonResponse
    {
        $student = $request->user()->student;

        $result = $this->aiChatService->getOrCreateSession($student, (int) $request->validated('unit_id'));

        return response()->json($result);
    }

    public function storeMessage(StoreAiChatMessageRequest $request, AiChatSession $session): JsonResponse
    {
        $student = $request->user()->student;

        $result = $this->aiChatService->sendMessage($student, $session, (string) $request->validated('content'));

        return response()->json($result);
    }
}
