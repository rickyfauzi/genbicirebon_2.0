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
            if (!$data) {
                throw new \Exception('Invalid JSON format');
            }

            // Handle different request formats
            $queryText = '';
            $intentName = 'Default Fallback Intent';
            $sessionId = $data['session'] ?? 'session-' . uniqid();

            // Format 1: queryResult (dari Dialogflow webhook)
            if (isset($data['queryResult'])) {
                $queryText = $data['queryResult']['queryText'] ?? '';
                $intentName = $data['queryResult']['intent']['displayName'] ?? 'Default Fallback Intent';
            }
            // Format 2: queryInput (dari frontend custom)
            elseif (isset($data['queryInput'])) {
                $queryText = $data['queryInput']['text']['text'] ?? '';
                $intentName = 'Default Fallback Intent'; // Custom frontend selalu fallback
            }
            // Format 3: Direct queryText (fallback sederhana)
            elseif (isset($data['queryText'])) {
                $queryText = $data['queryText'];
                $intentName = 'Default Fallback Intent';
            } else {
                throw new \Exception('No valid query format found');
            }

            Log::info("Request Dialogflow", [
                'query' => $queryText,
                'intent' => $intentName,
                'session' => $sessionId,
                'format' => isset($data['queryResult']) ? 'queryResult' : (isset($data['queryInput']) ? 'queryInput' : 'direct')
            ]);

            // Dapatkan balasan berdasarkan intent atau keyword
            $answer = $this->replyFromIntent($intentName, $queryText);

            // Simpan ke Firestore (opsional)
            $this->saveToFirestore($queryText, $answer, $sessionId, $intentName);

            // Format response untuk Dialogflow
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
     * Memberikan balasan berdasarkan intent dan keyword
     */
    private function replyFromIntent($intentName, $queryText)
    {
        // Responses berdasarkan intent name
        $intentResponses = [
            'Default Welcome Intent' => 'Halo! Saya chatbot GenBI Cirebon. Ada yang bisa saya bantu? 😊',
            'kontakgenbiintent' => '📧 Email: genbicirebon@gmail.com | 📱 Instagram: @genbi.cirebon | 🌐 Website: genbicirebon.org',
            'definisi.genbi' => 'GenBI (Generasi Baru Indonesia) adalah komunitas penerima beasiswa Bank Indonesia yang aktif dalam kegiatan sosial, edukasi, dan pengembangan diri untuk membangun Indonesia yang lebih baik.',
            'TentangGenBIIntent' => 'GenBI (Generasi Baru Indonesia) adalah komunitas penerima beasiswa Bank Indonesia yang aktif dalam kegiatan sosial, edukasi, dan pengembangan diri untuk membangun Indonesia yang lebih baik.',
            'Default Fallback Intent' => $this->handleFallback($queryText)
        ];

        return $intentResponses[$intentName] ?? $this->handleFallback($queryText);
    }

    /**
     * Handle fallback dengan keyword detection
     */
    private function handleFallback($queryText)
    {
        $query = strtolower($queryText);

        // Keyword-based responses
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

        if (strpos($query, 'halo') !== false || strpos($query, 'hai') !== false || strpos($query, 'hi') !== false) {
            return 'Halo! Saya chatbot GenBI Cirebon. Ada yang bisa saya bantu? 😊';
        }

        if (strpos($query, 'terima kasih') !== false || strpos($query, 'thanks') !== false) {
            return 'Sama-sama! Senang bisa membantu. Jangan ragu untuk bertanya lagi ya! 😊';
        }

        // Default fallback
        return 'Maaf, saya tidak mengerti maksud Anda. Bisa dijelaskan lagi? Atau coba tanyakan tentang GenBI, beasiswa, kegiatan, atau kontak kami. 😊';
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
                'projectId' => 'your-firebase-project-id' // Ganti dengan project ID Anda
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
