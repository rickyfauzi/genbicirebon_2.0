<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Google\Cloud\Firestore\FirestoreClient;

class WebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Log semua request untuk debugging
        Log::debug('Dialogflow Webhook Payload:', $request->all());

        // Parse request dari Dialogflow
        $intentName = $request->input('queryResult.intent.displayName');
        $queryText = $request->input('queryResult.queryText');
        $parameters = $request->input('queryResult.parameters');
        $session = $request->input('session');

        // Handle berdasarkan intent
        $response = $this->handleIntent($intentName, $queryText, $parameters);

        // Format response untuk Dialogflow
        return response()->json([
            'fulfillmentText' => $response,
            'source' => 'Laravel Webhook',
            'payload' => [
                'laravel_session' => session()->getId()
            ]
        ]);
    }

    private function handleIntent($intentName, $queryText, $parameters)
    {
        // Tambahkan logika intent Anda di sini
        switch ($intentName) {
            case 'Default Welcome Intent':
                return "Halo! Selamat datang di chatbot GenBI Cirebon. Ada yang bisa saya bantu?";

            case 'tanya-kegiatan':
                return "GenBI Cirebon sering mengadakan kegiatan seperti pelatihan, webinar, dan bakti sosial.";

            case 'tanya-syarat-beasiswa':
                return "Syarat utama beasiswa GenBI adalah mahasiswa aktif dengan IPK minimal 3.0.";

            default:
                return "Maaf, saya belum paham pertanyaan Anda. Coba tanyakan hal lain ya!";
        }
    }
}
