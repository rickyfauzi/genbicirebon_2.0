<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    private $websiteBaseUrl = 'https://genbicirebon.org';

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

            // Check for website content queries first
            $websiteResponse = $this->checkWebsiteContent($message);
            if ($websiteResponse) {
                return response()->json([
                    'message' => $websiteResponse,
                    'source' => 'website'
                ]);
            }

            // If no website content matched, use Dialogflow
            $response = $this->detectIntent($message);

            return response()->json([
                'message' => $response,
                'source' => 'dialogflow'
            ]);
        } catch (\Exception $e) {
            Log::error("Exception: " . $e->getMessage());
            Log::error("File: " . $e->getFile());
            Log::error("Line: " . $e->getLine());
            return response()->json(['message' => 'Maaf, terjadi kesalahan di server.'], 500);
        }
    }

    private function checkWebsiteContent(string $message)
    {
        // List of keywords that indicate user is asking about activities
        $activityKeywords = ['kegiatan', 'event', 'acara', 'aktivitas', 'blog', 'artikel', 'berita', 'program'];

        // Check if message contains any activity-related keywords
        $isActivityQuery = preg_match('/\b(' . implode('|', $activityKeywords) . ')\b/i', $message);

        if ($isActivityQuery) {
            try {
                // Fetch recent activities from your website API or scrape the content
                $response = Http::get($this->websiteBaseUrl . '/api/activities?limit=3');

                if ($response->successful()) {
                    $activities = $response->json();
                    $reply = "📌 Berikut beberapa kegiatan terbaru GenBI Cirebon:\n\n";

                    foreach ($activities as $activity) {
                        $reply .= "🔹 {$activity['title']}\n";
                        $reply .= "📅 {$activity['date']}\n";
                        $reply .= "🔗 {$this->websiteBaseUrl}{$activity['url']}\n\n";
                    }

                    $reply .= "Kunjungi {$this->websiteBaseUrl}/kegiatan untuk melihat semua kegiatan kami.";

                    return $reply;
                }
            } catch (\Exception $e) {
                Log::error("Error fetching activities: " . $e->getMessage());
            }

            // Fallback if API fails
            return "Anda bisa melihat semua kegiatan GenBI Cirebon di: {$this->websiteBaseUrl}/kegiatan";
        }

        // Check for other specific content types
        if (preg_match('/\b(anggota|pengurus|struktur)\b/i', $message)) {
            return "Informasi tentang anggota dan pengurus GenBI Cirebon bisa dilihat di: {$this->websiteBaseUrl}/tentang-kami";
        }

        if (preg_match('/\b(galeri|foto|dokumentasi)\b/i', $message)) {
            return "Kami memiliki galeri foto kegiatan di: {$this->websiteBaseUrl}/galeri";
        }

        return null;
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
