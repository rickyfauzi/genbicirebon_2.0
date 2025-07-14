<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        try {
            Log::info('🔥 RAW JSON', ['raw' => $request->getContent()]);

            $data = json_decode($request->getContent(), true);

            // Validasi request
            if (!$data || !isset($data['queryResult'])) {
                throw new \Exception('Invalid request format');
            }

            $queryText = $data['queryResult']['queryText'] ?? '';
            $intentName = $data['queryResult']['intent']['displayName'] ?? 'Default Fallback Intent';
            $sessionId = $data['session'] ?? 'session-' . uniqid();

            Log::info("Request Dialogflow", [
                'query' => $queryText,
                'intent' => $intentName,
                'session' => $sessionId
            ]);

            // Dapatkan balasan
            $answer = $this->replyFromIntent($intentName, $queryText);

            // Simpan ke Firestore (opsional)
            $this->saveToFirestore($queryText, $answer, $sessionId, $intentName);

            return response()->json([
                'fulfillmentText' => $answer,
                'source' => 'genbicirebon.org'
            ]);
        } catch (\Exception $e) {
            Log::error('Webhook Error: ' . $e->getMessage());
            return response()->json([
                'fulfillmentText' => 'Maaf, sedang ada gangguan teknis. Silakan coba lagi nanti.',
                'source' => 'genbicirebon.org'
            ], 500);
        }
    }

    /**
     * Memberikan balasan berdasarkan intent
     */
    private function replyFromIntent($intentName, $queryText, $dialogflowResponse)
    {
        $responseJson = json_decode($dialogflowResponse, true);

        // Ambil langsung dari Dialogflow jika tersedia
        if (!empty($responseJson['queryResult']['fulfillmentText'])) {
            return $responseJson['queryResult']['fulfillmentText'];
        }

        // Jika tidak ada, fallback ke daftar manual
        $responses = [
            'Default Welcome Intent' => 'Halo! Saya chatbot GenBI. Ada yang bisa saya bantu?',
            'kontakgenbiintent' => '📧 Email: genbicirebon@gmail.com | 📱 IG: @genbi.cirebon',
            'definisi.genbi' => 'GenBI adalah komunitas penerima beasiswa Bank Indonesia.',
            'Default Fallback Intent' => 'Maaf, saya belum paham. Bisa diulangi?',
        ];

        return $responses[$intentName] ?? "Saya tidak mengerti pertanyaan Anda.";
    }


    /**
     * Simpan riwayat chat ke Firestore
     */
    private function saveToFirestore($question, $answer, $sessionId, $intentName)
    {
        try {
            // Skip jika gRPC tidak ada
            if (!extension_loaded('grpc')) {
                Log::warning("gRPC extension tidak tersedia");
                return false;
            }

            // Skip jika file credentials tidak ada
            if (!file_exists(storage_path('app/firebase/firebase_credentials.json'))) {
                Log::warning("File Firebase credentials tidak ditemukan");
                return false;
            }

            $firestore = new FirestoreClient([
                'keyFilePath' => storage_path('app/firebase/firebase_credentials.json'),
                'projectId' => 'your-firebase-project-id' // Tambahkan ini
            ]);

            $collection = $firestore->collection('chat_history');
            $collection->add([
                'session' => $sessionId,
                'intent' => $intentName,
                'question' => $question,
                'answer' => $answer,
                'timestamp' => now()->toDateTimeString(),
                'source' => 'web'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Firestore Error: " . $e->getMessage());
            return false;
        }
    }
}
y