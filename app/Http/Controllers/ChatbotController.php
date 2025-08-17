<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Services\FirestoreService;
use Illuminate\Support\Facades\Auth;

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

        $firestore = new FirestoreService();

        try {
            $message = $request->input('message');
            $startTime = microtime(true);

            $response = $this->detectIntent($message);

            if (empty(trim($response))) {
                $response = $this->fallbackAI($message);
                $source = "openai";
            } else {
                $source = "dialogflow";
            }

            $endTime = microtime(true);
            $responseTime = round(($endTime - $startTime) * 1000);

            // simpan log percakapan ke Firestore
            $firestore->addChatLog([
                'session_id' => session()->getId(),
                'user_id'    => Auth::id() ?? 'guest',
                'question'   => $message,
                'answer'     => $response,
                'source'     => $source,
                'timestamp'  => now()->toDateTimeString(),
            ]);

            // update metrics harian
            $firestore->updateSystemMetrics(now()->format('Y-m-d'), [
                'date' => now()->format('Y-m-d'),
                'total_queries' => 1,
                $source . '_success' => 1,
            ]);

            return response()->json([
                'message' => $response
            ]);
        } catch (\Exception $e) {
            $firestore->addErrorLog([
                'error_message' => $e->getMessage(),
                'user_message'  => $request->input('message'),
                'timestamp'     => now()->toDateTimeString(),
            ]);

            return response()->json(['message' => 'Maaf, terjadi kesalahan di server.'], 500);
        }
    }

    private function fallbackAI(string $text)
    {
        $apiKey = env('OPENROUTER_API_KEY');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => 'https://genbicirebon.org/', // domain kamu
            'X-Title'       => 'Genbi Cirebon Chatbot', // nama app kamu
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            "model" => "mistralai/mistral-7b-instruct", // model gratis di OpenRouter
            "messages" => [
                ["role" => "system", "content" => "Kamu adalah asisten AI yang membantu pengguna."],
                ["role" => "user", "content" => $text]
            ]
        ]);

        if ($response->successful()) {
            return $response->json()['choices'][0]['message']['content'] ?? 'Maaf, saya tidak bisa menjawab saat ini.';
        }

        // Log error untuk debug
        Log::error('Fallback AI error: ' . json_encode([
            'status' => $response->status(),
            'body' => $response->json()
        ]));

        return 'Maaf, saya tidak bisa menjawab saat ini.';
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

        $textInput = new TextInput();
        $textInput->setText($text);
        $textInput->setLanguageCode('id');

        $queryInput = new QueryInput();
        $queryInput->setText($textInput);

        $detectIntentRequest = new DetectIntentRequest();
        $detectIntentRequest->setSession($session);
        $detectIntentRequest->setQueryInput($queryInput);

        $response = $sessionsClient->detectIntent($detectIntentRequest);

        $queryResult = $response->getQueryResult();
        $fulfillmentText = $queryResult->getFulfillmentText();

        return $fulfillmentText;
    }

    // private function fallbackAI(string $text)
    // {
    //     $apiKey = "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";

    //     $response = Http::withHeaders([
    //         'Authorization' => 'Bearer ' . $apiKey,
    //         'Content-Type'  => 'application/json',
    //         'HTTP-Referer'  => 'https://genbicirebon.org/', // domain kamu
    //         'X-Title'       => 'Genbi Cirebon Chatbot', // nama app kamu
    //     ])->post('https://openrouter.ai/api/v1/chat/completions', [
    //         "model" => "mistralai/mistral-7b-instruct", // model gratis di OpenRouter
    //         "messages" => [
    //             ["role" => "system", "content" => "Kamu adalah asisten AI yang membantu pengguna."],
    //             ["role" => "user", "content" => $text]
    //         ]
    //     ]);

    //     if ($response->successful()) {
    //         return $response->json()['choices'][0]['message']['content'] ?? 'Maaf, saya tidak bisa menjawab saat ini.';
    //     }

    //     Log::error('Fallback AI error: ' . json_encode($response->json()));
    //     return 'Maaf, saya tidak bisa menjawab saat ini.';
    // }

    public function fallbackTest(Request $request)
    {
        $apiKey = env('OPENROUTER_API_KEY');
        // Contoh pesan dari user
        $userMessage = "Halo, apa kabar?";

        // Kirim request ke OpenRouter API
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$apiKey}",
            'Content-Type'  => 'application/json',
            'HTTP-Referer'  => 'https://genbicirebon.org/', // ganti sesuai domain kamu
            'X-Title'       => 'Laravel Chatbot Test',
        ])->post('https://openrouter.ai/api/v1/chat/completions', [
            'model' => 'openai/gpt-3.5-turbo',
            'messages' => [
                ['role' => 'system', 'content' => 'You are a helpful assistant.'],
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        // Cek apakah request sukses
        if ($response->successful()) {
            return $response->json();
        } else {
            return [
                'error' => [
                    'status' => $response->status(),
                    'body'   => $response->json()
                ]
            ];
        }
    }
}
