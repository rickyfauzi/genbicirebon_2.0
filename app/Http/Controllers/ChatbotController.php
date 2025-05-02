<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google_Client;
use Google_Service_Sheets;
use Google_Service_Sheets_ValueRange;
use Carbon\Carbon;

class ChatbotController extends Controller
{
    public function handle(Request $request)
    {
        $userMessage = strtolower($request->input('queryResult.queryText'));
        $timestamp = Carbon::now()->toDateTimeString();
        
        \Log::info("🔍 Menerima pertanyaan: " . $userMessage);
        
        try {
            $jawaban = $this->cariJawabanDariSheet($userMessage);
        } catch (\Exception $e) {
            \Log::error("❌ Gagal membaca dari Sheet jawaban_bot: " . $e->getMessage());
            return response()->json([
                'fulfillmentText' => "Terjadi kesalahan saat mengambil jawaban dari sistem."
            ]);
        }
        
        try {
            $this->catatLogInteraksi($timestamp, $userMessage, $jawaban);
        } catch (\Exception $e) {
            \Log::error("❌ Gagal mencatat log interaksi ke log_interaksi: " . $e->getMessage());
        }
        
        return response()->json([
            'fulfillmentText' => $jawaban
        ]);
    }
    
    private function cariJawabanDariSheet($userMessage)
    {
        \Log::info("📥 Mulai akses Sheet 'jawaban_bot'...");
        
        $client = new Google_Client();
        $client->setAuthConfig(base_path(env('GOOGLE_KEY_PATH')));
        $client->addScope(Google_Service_Sheets::SPREADSHEETS_READONLY);
        
        $service = new Google_Service_Sheets($client);
        $spreadsheetId = env('SPREADSHEET_ID');
        
        // Fix: Ensure the sheet name exists and format is correct
        $range = "'jawaban_bot'!A2:B"; // Added quotes around sheet name
        
        try {
            $response = $service->spreadsheets_values->get($spreadsheetId, $range);
            $values = $response->getValues();
            
            if (empty($values)) {
                \Log::warning("📛 Tidak ada data yang dibaca dari Sheet jawaban_bot.");
                return "Sheet jawaban kosong.";
            }
            
            foreach ($values as $row) {
                \Log::info("➡️ Cek row: " . json_encode($row));
                if (isset($row[0]) && stripos($userMessage, strtolower($row[0])) !== false) {
                    \Log::info("✅ Jawaban ditemukan: " . $row[1]);
                    return $row[1];
                }
            }
            
            \Log::info("❌ Tidak ada jawaban cocok untuk: " . $userMessage);
            return "Maaf, saya belum memiliki jawaban untuk pertanyaan itu.";
        } catch (\Exception $e) {
            \Log::error("Error accessing spreadsheet: " . $e->getMessage());
            // Try an alternative approach with different sheet name format
            try {
                $range = "jawaban_bot!A:B"; // Alternative format
                $response = $service->spreadsheets_values->get($spreadsheetId, $range);
                $values = $response->getValues();
                
                if (empty($values)) {
                    return "Sheet jawaban kosong.";
                }
                
                foreach ($values as $row) {
                    if (isset($row[0]) && stripos($userMessage, strtolower($row[0])) !== false) {
                        return $row[1];
                    }
                }
                
                return "Maaf, saya belum memiliki jawaban untuk pertanyaan itu.";
            } catch (\Exception $e2) {
                \Log::error("Second attempt failed: " . $e2->getMessage());
                throw $e2;
            }
        }
    }
    
    private function catatLogInteraksi($timestamp, $pertanyaan, $jawaban)
    {
        \Log::info("📝 Menyimpan log interaksi: [$timestamp | $pertanyaan | $jawaban]");
        
        $client = new Google_Client();
        $client->setAuthConfig(base_path(env('GOOGLE_KEY_PATH')));
        $client->addScope(Google_Service_Sheets::SPREADSHEETS);
        
        $service = new Google_Service_Sheets($client);
        $spreadsheetId = env('SPREADSHEET_ID');
        
        // Fix: ensure sheet name format is correct
        $range = "'log_interaksi'!A:C"; // Added quotes and simplified range
        
        $body = new Google_Service_Sheets_ValueRange([
            'values' => [
                [$timestamp, $pertanyaan, $jawaban]
            ]
        ]);
        
        $params = ['valueInputOption' => 'RAW'];
        
        try {
            $result = $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
            \Log::info("✅ Log interaksi berhasil dicatat.");
        } catch (\Exception $e) {
            \Log::error("Error logging interaction: " . $e->getMessage());
            throw $e;
        }
    }
}