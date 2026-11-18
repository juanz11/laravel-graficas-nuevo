<?php

namespace App\Http\Controllers;

use App\Services\SalesChatService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Chat page: ask questions about sales data in natural language.
     */
    public function index()
    {
        return view('chat');
    }

    /**
     * Answer a chat message (JSON endpoint).
     */
    public function ask(Request $request, SalesChatService $chat)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array|max:16',
            'history.*.role' => 'required_with:history|string|in:user,assistant',
            'history.*.content' => 'required_with:history|string|max:4000',
        ]);

        return response()->json(
            $chat->answer($validated['message'], $validated['history'] ?? [])
        );
    }
}
