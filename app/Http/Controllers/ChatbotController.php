<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot');
    }

    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        try {
            if (!class_exists(SessionsClient::class)) {
                Log::error('SessionsClient class not found. Pastikan library google/cloud-dialogflow sudah di-install.');
                return response()->json(['message' => 'Library Dialogflow tidak ditemukan. Silakan install dengan composer.'], 500);
            }

            $message = $request->input('message');
            $response = $this->detectIntent($message);

            return response()->json([
                'message' => $response
            ]);
        } catch (\Exception $e) {
            Log::error("Exception: " . $e->getMessage());
            Log::error("File: " . $e->getFile());
            Log::error("Line: " . $e->getLine());
            return response()->json(['message' => 'Maaf, terjadi kesalahan di server.'], 500);
        }
    }

    public function detectIntent(string $text)
    {
        $projectId = 'websitebot-etqi'; // Ganti dengan ID Project kamu
        $sessionId = session()->getId();

        $credentialsPath = storage_path('app/google/dialogflow-credentials.json');

        $sessionsClient = new SessionsClient([
            'credentials' => $credentialsPath
        ]);

        $session = $sessionsClient->sessionName($projectId, $sessionId);

        // Buat TextInput
        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode('id');

        // Bungkus dalam QueryInput
        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        // Buat request DetectIntentRequest
        $detectIntentRequest = new DetectIntentRequest();
        $detectIntentRequest->setSession($session);
        $detectIntentRequest->setQueryInput($queryInput);

        // Kirim ke Dialogflow
        $response = $sessionsClient->detectIntent($detectIntentRequest);

        // Ambil response
        $queryResult = $response->getQueryResult();
        $fulfillmentText = $queryResult->getFulfillmentText();

        return $fulfillmentText;
    }
}
