<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $userMessage = strtolower(trim($request->input('queryResult.queryText')));
        $timestamp = Carbon::now()->toDateTimeString();

        Log::info("🔍 Menerima pertanyaan: " . $userMessage);

        $affirmatives = ['iya', 'ya', 'betul', 'benar', 'ok', 'oke', 'iya benar', 'ya betul', 'iya maksud saya itu'];

        if (in_array($userMessage, $affirmatives)) {
            $suggested = Cache::get('suggested_question');
            $original = Cache::get('original_question');

            if ($suggested && $original) {
                Log::info("✅ Menggunakan suggestion sebelumnya: $suggested");
                $jawaban = $this->cariJawabanDariSheet($suggested);
                Cache::forget('suggested_question');
                Cache::forget('original_question');
                $this->catatLogInteraksi($timestamp, $userMessage . " [konfirmasi suggestion]", $jawaban);
                return response()->json(['fulfillmentText' => $jawaban]);
            }
        }

        try {
            $jawaban = $this->cariJawabanDariSheet($userMessage);
        } catch (\Exception $e) {
            Log::error("❌ Gagal membaca dari Sheet jawaban_bot: " . $e->getMessage());
            return response()->json([
                'fulfillmentText' => "Terjadi kesalahan saat mengambil jawaban dari sistem."
            ]);
        }

        if (str_contains($jawaban, "Mungkin maksud Anda:")) {
            if (preg_match('/\*(.*?)\*/', $jawaban, $match)) {
                Cache::put('suggested_question', $match[1], now()->addMinutes(5));
                Cache::put('original_question', $userMessage, now()->addMinutes(5));
            }
        } else {
            Cache::forget('suggested_question');
            Cache::forget('original_question');
        }

        try {
            $this->catatLogInteraksi($timestamp, $userMessage, $jawaban);
        } catch (\Exception $e) {
            Log::error("❌ Gagal mencatat log interaksi ke log_interaksi: " . $e->getMessage());
        }

        return response()->json([
            'fulfillmentText' => $jawaban
        ]);
    }

    private function normalize($text)
    {
        $text = strtolower($text);
        $text = preg_replace('/[^\p{L}\p{N}\s]/u', '', $text);
        $text = preg_replace('/\b(apa|itu|siapa|yang|adalah|maksudnya)\b/', '', $text);
        return trim($text);
    }

    private function cariJawabanDariSheet($userMessage)
    {
        Log::info("📥 Mulai akses Sheet 'jawaban_bot'...");

        $client = new Google_Client();
        $client->setAuthConfig(base_path(env('GOOGLE_KEY_PATH')));
        $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);

        $service = new Google_Service_Sheets($client);
        $spreadsheetId = env('SPREADSHEET_ID');
        $range = "'jawaban_bot'!A2:B";

        try {
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();

            if (empty($values)) {
                Log::warning("📛 Tidak ada data yang dibaca dari Sheet jawaban_bot.");
                return "Sheet jawaban kosong.";
            }

            $bestMatch = null;
            $bestScore = 0;
            $bestQuestion = '';

            foreach ($values as $row) {
                if (!isset($row[0]) || !isset($row[1])) continue;

                $sheetQuestion = strtolower(trim($row[0]));
                similar_text($sheetQuestion, $userMessage, $percent);

                Log::info("🔍 Bandingkan: '$sheetQuestion' ↔ '$userMessage' = {$percent}%");

                if ($percent > $bestScore) {
                    $bestScore = $percent;
                    $bestMatch = $row[1];
                    $bestQuestion = $row[0];
                }

                if ($percent >= 90) {
                    Log::info("✅ Match langsung ditemukan (>=90%): $row[1]");
                    return $row[1];
                }
            }

            if ($bestScore >= 60) {
                Log::info("✅ Match terbaik ditemukan (>=60%): $bestMatch");
                return $bestMatch;
            }

            Log::info("🤔 Tidak ditemukan jawaban cocok. Skor tertinggi: $bestScore% dari '$bestQuestion'");
            return "Maaf, saya belum memiliki jawaban yang tepat.\nMungkin maksud Anda: *{$bestQuestion}* ?";
        } catch (\Exception $e) {
            Log::error("Error accessing spreadsheet: " . $e->getMessage());
            return "Terjadi kesalahan saat mengakses data jawaban.";
        }
    }

    private function catatLogInteraksi($timestamp, $pertanyaan, $jawaban)
    {
        Log::info("📜 Menyimpan log interaksi: [$timestamp | $pertanyaan | $jawaban]");

        $client = new Google_Client();
        $client->setAuthConfig(base_path(env('GOOGLE_KEY_PATH')));
        $client->addScope(Google_Service_Sheets::SPREADSHEETS);

        $service = new Google_Service_Sheets($client);
        $spreadsheetId = env('SPREADSHEET_ID');
        $range = "'log_interaksi'!A:C";

        $body = new Google_Service_Sheets_ValueRange([
            'values' => [
                [$timestamp, $pertanyaan, $jawaban]
            ]
        ]);

        $params = ['valueInputOption' => 'RAW'];

        try {
            $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
            Log::info("✅ Log interaksi berhasil dicatat.");
        } catch (\Exception $e) {
            Log::error("Gagal menyimpan log: " . $e->getMessage());
            throw $e;
        }
    }
}
