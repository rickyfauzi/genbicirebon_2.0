<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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
            $response = null;

            try {
                // Coba ke Dialogflow
                $response = $this->detectIntent($message);

                // Kalau Dialogflow tidak punya jawaban, fallback ke OpenAI
                if (empty(trim($response))) {
                    Log::info("Dialogflow tidak punya jawaban, fallback ke OpenAI.");
                    $response = $this->fallbackAI($message);
                }
            } catch (\Exception $e) {
                Log::warning("Dialogflow gagal, fallback ke AI: " . $e->getMessage());
                $response = $this->fallbackAI($message);
            }

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

    private function fallbackAI(string $text)
    {
        $apiKey = "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";

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
        $projectId = 'websitebot-etqii'; // Ganti dengan ID Project kamu
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
        $apiKey = "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";

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
