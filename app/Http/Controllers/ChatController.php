<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;

class ChatController extends Controller
{
    /**
     * Menerima pesan dari frontend, mengirimkannya ke Dialogflow,
     * dan mengembalikan respons dari Dialogflow.
     */
    public function detectIntent(Request $request)
    {
        // Validasi input dari frontend
        $request->validate([
            'queryText' => 'required|string',
            'session' => 'nullable|string', // Session ID bisa dikirim dari frontend atau dibuat di sini
        ]);

        try {
            $queryText = $request->input('queryText');
            // Gunakan session dari request atau buat yang baru jika tidak ada
            $sessionId = $request->input('session', uniqid('session-', true));

            // Path ke file credentials Anda
            // Path ke file credentials Anda
            $credentialsPath = storage_path('app/genbi-key.json'); // Hapus 'storage/' di dalam fungsi
            if (!file_exists($credentialsPath)) {
                throw new \Exception('File Firebase credentials tidak ditemukan.');
            }

            // Konfigurasi untuk Dialogflow Client
            $config = [
                'credentials' => $credentialsPath
            ];

            $sessionsClient = new SessionsClient($config);
            $projectId = 'chatbotgenbi'; // GANTI DENGAN PROJECT ID ANDA

            // Format nama session yang dibutuhkan oleh Dialogflow
            $sessionName = $sessionsClient->sessionName($projectId, $sessionId);

            // Buat query input dari teks
            $textInput = new TextInput();
            $textInput->setText($queryText);
            $textInput->setLanguageCode('id'); // Gunakan 'id' untuk Bahasa Indonesia

            $queryInput = new QueryInput();
            $queryInput->setText($textInput);

            // Kirim request ke Dialogflow
            $response = $sessionsClient->detectIntent($sessionName, $queryInput);
            $queryResult = $response->getQueryResult();
            $fulfillmentText = $queryResult->getFulfillmentText();

            // Tutup koneksi client
            $sessionsClient->close();

            // Kirim balasan dari Dialogflow ke frontend
            return response()->json([
                'fulfillmentText' => $fulfillmentText,
            ]);
        } catch (\Exception $e) {
            Log::error('Dialogflow Proxy Error: ' . $e->getMessage());
            return response()->json([
                'fulfillmentText' => 'Maaf, terjadi kesalahan saat menghubungi asisten virtual kami.'
            ], 500);
        }
    }
}
