<?php

namespace App\Http\Controllers;

use App\Services\DialogflowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    protected $dialogflow;

    public function __construct(DialogflowService $dialogflow)
    {
        $this->dialogflow = $dialogflow;
    }

    public function handleChat(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'message' => 'required|string|max:1000',
                'session_id' => 'nullable|string|max:100'
            ]);

            $message = $request->input('message');
            $sessionId = $request->input('session_id') ?? 'genbi-' . uniqid();

            Log::info('Chatbot Request:', [
                'message' => $message,
                'session_id' => $sessionId
            ]);

            // Proses pesan dengan Dialogflow
            $response = $this->dialogflow->detectIntentText($message, $sessionId);

            Log::info('Chatbot Response:', $response);

            return response()->json($response);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'response' => 'Pesan tidak valid. Silakan coba lagi.',
                'error' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Chatbot Controller Error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'response' => 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.',
                'error' => 'Internal server error'
            ], 500);
        }
    }
}
