<?php

namespace App\Http\Controllers;

use App\Services\DialogflowService;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    protected $dialogflow;

    public function __construct(DialogflowService $dialogflow)
    {
        $this->dialogflow = $dialogflow;
    }

    public function handleChat(Request $request)
    {
        $message = $request->input('message');
        $sessionId = $request->input('session_id') ?? uniqid();

        try {
            $response = $this->dialogflow->detectIntentText($message, $sessionId);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'response' => 'Maaf, terjadi kesalahan. Silakan coba lagi.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
