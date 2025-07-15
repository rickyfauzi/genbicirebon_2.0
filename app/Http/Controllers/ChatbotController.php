<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use GPBMetadata\Google\Api\Http;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $message = $request->input('message');

        // Dialogflow Webhook or Detect Intent API
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('DIALOGFLOW_TOKEN'),
        ])->post('https://dialogflow.googleapis.com/v2/projects/genbichatbot-gjxn/agent/sessions/123456:detectIntent', [
            'query_input' => [
                'text' => [
                    'text' => $message,
                    'language_code' => 'id',
                ]
            ]
        ]);

        if ($response->successful()) {
            $reply = $response->json()['queryResult']['fulfillmentText'] ?? 'Tidak ada jawaban.';
            return response()->json(['reply' => $reply]);
        } else {
            return response()->json(['reply' => 'Maaf, terjadi kesalahan.']);
        }
    }
}
