<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function detectIntent(Request $request)
    {
        $request->validate([
            'queryText' => 'required|string',
            'session' => 'nullable|string',
        ]);

        try {
            // 1. Setup Credentials
            $credentialsPath = storage_path('app/genbi-key.json');
            if (!file_exists($credentialsPath)) {
                throw new \Exception('File credentials tidak ditemukan');
            }

            putenv("GOOGLE_APPLICATION_CREDENTIALS=$credentialsPath");

            $projectId = 'genbichatbot-gjxn';
            $sessionId = $request->input('session', 'web-session-' . uniqid());
            $queryText = $request->input('queryText');

            Log::info('Dialogflow Request', [
                'query' => $queryText,
                'session' => $sessionId,
                'project' => $projectId
            ]);

            $sessionsClient = new SessionsClient([
                'credentials' => $credentialsPath
            ]);

            $sessionName = $sessionsClient->sessionName($projectId, $sessionId);

            // 3. Create Query Input
            $textInput = (new TextInput())
                ->setText($queryText)
                ->setLanguageCode('id'); // Indonesian

            $queryInput = (new QueryInput())
                ->setText($textInput);

            // 4. Create DetectIntentRequest
            $detectIntentRequest = (new DetectIntentRequest())
                ->setSession($sessionName)
                ->setQueryInput($queryInput);

            // 5. Send Request
            $response = $sessionsClient->detectIntent($detectIntentRequest);
            $queryResult = $response->getQueryResult();

            // 6. Close Connection
            $sessionsClient->close();

            $fulfillmentText = $queryResult->getFulfillmentText();
            $intentName = $queryResult->getIntent() ? $queryResult->getIntent()->getDisplayName() : 'Unknown';

            Log::info('Dialogflow Response', [
                'fulfillmentText' => $fulfillmentText,
                'intent' => $intentName,
                'confidence' => $queryResult->getIntentDetectionConfidence()
            ]);

            // Fallback jika tidak ada response dari Dialogflow
            if (empty($fulfillmentText)) {
                $fulfillmentText = $this->getFallbackResponse($queryText);
            }

            return response()->json([
                'fulfillmentText' => $fulfillmentText,
                'intent' => $intentName,
                'session' => $sessionId,
                'success' => true,
                'source' => 'dialogflow'
            ]);
        } catch (\Exception $e) {
            Log::error('Dialogflow Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            // Fallback ke response lokal jika Dialogflow error
            $fallbackResponse = $this->getFallbackResponse($request->input('queryText'));

            return response()->json([
                'fulfillmentText' => $fallbackResponse,
                'intent' => 'Fallback',
                'session' => $request->input('session'),
                'success' => false,
                'source' => 'local_fallback',
                'error' => env('APP_DEBUG') ? $e->getMessage() : 'Gangguan sementara'
            ]);
        }
    }

    /**
     * Fallback response jika Dialogflow tidak tersedia
     */
    private function getFallbackResponse($queryText)
    {
        $query = strtolower($queryText);

        // Keyword-based responses
        if (strpos($query, 'halo') !== false || strpos($query, 'hai') !== false || strpos($query, 'hi') !== false) {
            return 'Halo! Saya chatbot GenBI Cirebon. Ada yang bisa saya bantu? 😊';
        }

        if (strpos($query, 'genbi') !== false || strpos($query, 'generasi baru') !== false) {
            return 'GenBI (Generasi Baru Indonesia) adalah komunitas penerima beasiswa Bank Indonesia yang aktif dalam kegiatan sosial, edukasi, dan pengembangan diri untuk membangun Indonesia yang lebih baik.';
        }

        if (strpos($query, 'kontak') !== false || strpos($query, 'hubungi') !== false) {
            return '📧 Email: genbicirebon@gmail.com | 📱 Instagram: @genbi.cirebon | 🌐 Website: genbicirebon.org';
        }

        if (strpos($query, 'beasiswa') !== false) {
            return 'Beasiswa Bank Indonesia merupakan program pemberian bantuan dana pendidikan untuk mahasiswa berprestasi. Untuk info lebih lanjut, hubungi kontak GenBI Cirebon ya!';
        }

        if (strpos($query, 'kegiatan') !== false || strpos($query, 'program') !== false) {
            return 'GenBI Cirebon aktif dalam berbagai kegiatan seperti workshop, seminar, kegiatan sosial, dan pengembangan soft skill. Follow Instagram @genbi.cirebon untuk update terbaru!';
        }

        if (strpos($query, 'terima kasih') !== false || strpos($query, 'thanks') !== false) {
            return 'Sama-sama! Senang bisa membantu. Jangan ragu untuk bertanya lagi ya! 😊';
        }

        // Default fallback
        return 'Maaf, saya tidak mengerti maksud Anda. Bisa dijelaskan lagi? Atau coba tanyakan tentang GenBI, beasiswa, kegiatan, atau kontak kami. 😊';
    }

    /**
     * Method untuk test konektivitas Dialogflow
     */
    public function testConnection()
    {
        try {
            $credentialsPath = storage_path('app/genbi-key.json');

            $tests = [
                'credentials_file' => file_exists($credentialsPath),
                'grpc_extension' => extension_loaded('grpc'),
                'google_cloud_installed' => class_exists('Google\Cloud\Dialogflow\V2\SessionsClient'),
                'credentials_readable' => file_exists($credentialsPath) && is_readable($credentialsPath)
            ];

            return response()->json([
                'status' => 'test_complete',
                'tests' => $tests,
                'all_passed' => !in_array(false, $tests)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'test_failed',
                'error' => $e->getMessage()
            ]);
        }
    }
}
