<?php

namespace App\Http\Controllers;

use Google\Cloud\Dialogflow\V2\Client\SessionsClient;
use Illuminate\Http\Request;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\DetectIntentRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class ChatbotController extends Controller
{
    private $websiteData;

    public function __construct()
    {
        // Initialize website data
        $this->websiteData = $this->initializeWebsiteData();
    }

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
            $message = $request->input('message');

            // Check if it's a local query first
            $localResponse = $this->handleLocalQuery($message);
            if ($localResponse) {
                return response()->json([
                    'message' => $this->formatResponse($localResponse)
                ]);
            }

            // If not handled locally, use Dialogflow
            $response = $this->detectIntent($message);

            return response()->json([
                'message' => $this->formatResponse($response)
            ]);
        } catch (\Exception $e) {
            Log::error("Chatbot Exception: " . $e->getMessage());
            return response()->json([
                'message' => 'Maaf, terjadi kesalahan. Silakan coba lagi nanti.'
            ], 500);
        }
    }

    private function handleLocalQuery($message)
    {
        $message = strtolower($message);

        // Handle kegiatan queries
        if (strpos($message, 'kegiatan') !== false || strpos($message, 'acara') !== false || strpos($message, 'event') !== false) {
            return $this->getKegiatanInfo($message);
        }

        // Handle program queries
        if (strpos($message, 'program') !== false) {
            return $this->getProgramInfo($message);
        }

        // Handle beasiswa queries
        if (strpos($message, 'beasiswa') !== false || strpos($message, 'syarat') !== false) {
            return $this->getBeasiswaInfo($message);
        }

        // Handle about queries
        if (strpos($message, 'tentang genbi') !== false || strpos($message, 'apa itu genbi') !== false) {
            return $this->getAboutInfo();
        }

        // Handle contact queries
        if (strpos($message, 'kontak') !== false || strpos($message, 'hubungi') !== false) {
            return $this->getContactInfo();
        }

        return null;
    }

    private function getKegiatanInfo($message)
    {
        $kegiatan = [
            [
                'nama' => 'GenBI Fair 2024',
                'tanggal' => '15 Maret 2024',
                'deskripsi' => 'Pameran dan expo karya mahasiswa GenBI',
                'lokasi' => 'Universitas Swadaya Gunung Jati'
            ],
            [
                'nama' => 'Workshop Digital Marketing',
                'tanggal' => '22 April 2024',
                'deskripsi' => 'Pelatihan digital marketing untuk UMKM',
                'lokasi' => 'Hotel Aston Cirebon'
            ],
            [
                'nama' => 'Seminar Kewirausahaan',
                'tanggal' => '10 Mei 2024',
                'deskripsi' => 'Seminar nasional tentang kewirausahaan muda',
                'lokasi' => 'Gedung Balai Kota Cirebon'
            ],
            [
                'nama' => 'GenBI Goes to School',
                'tanggal' => '5 Juni 2024',
                'deskripsi' => 'Program edukasi ke sekolah-sekolah',
                'lokasi' => 'SMA Negeri 1 Cirebon'
            ],
            [
                'nama' => 'Bakti Sosial Ramadan',
                'tanggal' => '25 Maret 2024',
                'deskripsi' => 'Program berbagi dengan masyarakat kurang mampu',
                'lokasi' => 'Kelurahan Pekalangan'
            ]
        ];

        if (strpos($message, 'terbaru') !== false || strpos($message, 'mendatang') !== false) {
            return "📅 **Kegiatan Terbaru GenBI Cirebon:**\n\n" .
                "• **GenBI Fair 2024**\n" .
                "  📍 Universitas Swadaya Gunung Jati\n" .
                "  📆 15 Maret 2024\n" .
                "  📝 Pameran dan expo karya mahasiswa GenBI\n\n" .
                "• **Workshop Digital Marketing**\n" .
                "  📍 Hotel Aston Cirebon\n" .
                "  📆 22 April 2024\n" .
                "  📝 Pelatihan digital marketing untuk UMKM\n\n" .
                "Untuk info lebih lengkap, kunjungi: genbicirebon.org/kegiatan";
        }

        $response = "🎯 **Daftar Kegiatan GenBI Cirebon 2024:**\n\n";
        foreach ($kegiatan as $k) {
            $response .= "• **{$k['nama']}**\n";
            $response .= "  📅 {$k['tanggal']}\n";
            $response .= "  📍 {$k['lokasi']}\n";
            $response .= "  📄 {$k['deskripsi']}\n\n";
        }
        $response .= "🔗 Info lengkap: genbicirebon.org/kegiatan";

        return $response;
    }

    private function getProgramInfo($message)
    {
        return "🌟 **Program Unggulan GenBI Cirebon:**\n\n" .
            "• **Program Sosial**\n" .
            "  ✓ Bakti sosial rutin\n" .
            "  ✓ Bantuan pendidikan\n" .
            "  ✓ Program lingkungan\n\n" .
            "• **Program Edukasi**\n" .
            "  ✓ Workshop skill development\n" .
            "  ✓ Seminar kewirausahaan\n" .
            "  ✓ Pelatihan literasi keuangan\n\n" .
            "• **Program Pengembangan Diri**\n" .
            "  ✓ Leadership training\n" .
            "  ✓ Public speaking\n" .
            "  ✓ Project management\n\n" .
            "🔗 Detail program: genbicirebon.org/program";
    }

    private function getBeasiswaInfo($message)
    {
        if (strpos($message, 'syarat') !== false) {
            return "📋 **Syarat Beasiswa GenBI:**\n\n" .
                "• **Persyaratan Umum:**\n" .
                "  ✓ WNI dan tidak sedang menerima beasiswa lain\n" .
                "  ✓ Mahasiswa aktif semester 2-6 (D3/S1) atau semester 2-4 (D4)\n" .
                "  ✓ IPK minimal 3.00\n\n" .
                "• **Persyaratan Khusus:**\n" .
                "  ✓ Berasal dari keluarga kurang mampu\n" .
                "  ✓ Aktif dalam organisasi/kegiatan sosial\n" .
                "  ✓ Memiliki jiwa kepemimpinan\n\n" .
                "• **Dokumen yang Diperlukan:**\n" .
                "  ✓ Formulir pendaftaran\n" .
                "  ✓ Transkrip nilai\n" .
                "  ✓ Surat keterangan tidak mampu\n" .
                "  ✓ Essay motivasi\n\n" .
                "🔗 Info lengkap: genbicirebon.org/beasiswa";
        }

        return "🎓 **Beasiswa Bank Indonesia (GenBI):**\n\n" .
            "• **Nilai Beasiswa:**\n" .
            "  💰 Rp 1.000.000/bulan\n" .
            "  📚 Bantuan biaya pendidikan\n\n" .
            "• **Fasilitas Tambahan:**\n" .
            "  ✓ Pelatihan soft skill\n" .
            "  ✓ Networking dengan alumni\n" .
            "  ✓ Program magang\n" .
            "  ✓ Sertifikat kompetensi\n\n" .
            "• **Durasi:**\n" .
            "  ⏰ Maksimal 4 semester\n\n" .
            "📝 Pendaftaran dibuka setiap tahun (Februari-Maret)\n" .
            "🔗 Info: genbicirebon.org/beasiswa";
    }

    private function getAboutInfo()
    {
        return "🏛️ **Tentang GenBI Cirebon:**\n\n" .
            "**GenBI** (Generasi Baru Indonesia) adalah komunitas penerima beasiswa Bank Indonesia yang tersebar di seluruh Indonesia.\n\n" .
            "• **Visi:**\n" .
            "  Menjadi generasi pemimpin masa depan yang berintegritas\n\n" .
            "• **Misi:**\n" .
            "  ✓ Mengembangkan potensi generasi muda\n" .
            "  ✓ Berkontribusi pada masyarakat\n" .
            "  ✓ Membangun karakter kepemimpinan\n\n" .
            "• **GenBI Cirebon:**\n" .
            "  📍 Wilayah kerja: Cirebon dan sekitarnya\n" .
            "  👥 Anggota aktif: 150+ mahasiswa\n" .
            "  🏫 Partner: 15+ perguruan tinggi\n\n" .
            "🔗 Website: genbicirebon.org";
    }

    private function getContactInfo()
    {
        return "📞 **Hubungi GenBI Cirebon:**\n\n" .
            "• **Alamat Sekretariat:**\n" .
            "  📍 Bank Indonesia Perwakilan Cirebon\n" .
            "  Jl. Siliwangi No. 1, Cirebon\n\n" .
            "• **Kontak:**\n" .
            "  📧 Email: genbicirebon@gmail.com\n" .
            "  📱 Instagram: @genbi_cirebon\n" .
            "  🌐 Website: genbicirebon.org\n\n" .
            "• **Jam Operasional:**\n" .
            "  🕐 Senin - Jumat: 08.00 - 16.00 WIB\n\n" .
            "💬 Atau Anda bisa langsung chat dengan admin melalui website kami!";
    }

    private function formatResponse($response)
    {
        // Convert markdown-style formatting to HTML for better display
        $response = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $response);
        $response = preg_replace('/^• (.+)$/m', '<div class="bullet-point">• <span class="bullet-text">$1</span></div>', $response);
        $response = preg_replace('/^  ✓ (.+)$/m', '<div class="sub-bullet">✓ <span class="sub-bullet-text">$1</span></div>', $response);
        $response = preg_replace('/^  📅 (.+)$/m', '<div class="info-item">📅 <span class="info-text">$1</span></div>', $response);
        $response = preg_replace('/^  📍 (.+)$/m', '<div class="info-item">📍 <span class="info-text">$1</span></div>', $response);
        $response = preg_replace('/^  📄 (.+)$/m', '<div class="info-item">📄 <span class="info-text">$1</span></div>', $response);
        $response = preg_replace('/^  💰 (.+)$/m', '<div class="info-item">💰 <span class="info-text">$1</span></div>', $response);
        $response = preg_replace('/^  📚 (.+)$/m', '<div class="info-item">📚 <span class="info-text">$1</span></div>', $response);
        $response = preg_replace('/^  ⏰ (.+)$/m', '<div class="info-item">⏰ <span class="info-text">$1</span></div>', $response);

        // Format links
        $response = preg_replace('/🔗 (.+)/', '<div class="link-info">🔗 <a href="https://$1" target="_blank" class="chat-link">$1</a></div>', $response);

        return $response;
    }

    private function initializeWebsiteData()
    {
        // This would typically fetch from your database
        // For now, returning static data structure
        return [
            'base_url' => 'https://genbicirebon.org',
            'routes' => [
                'home' => '/',
                'about' => '/about',
                'beasiswa' => '/beasiswa',
                'kegiatan' => '/kegiatan',
                'contact' => '/contact',
                'organization' => '/organization',
                'galeri' => '/galeri',
                'blog' => '/blog'
            ]
        ];
    }

    public function detectIntent(string $text)
    {
        try {
            if (!class_exists(SessionsClient::class)) {
                Log::error('SessionsClient class not found');
                return 'Maaf, layanan chatbot sedang tidak tersedia.';
            }

            $projectId = 'websitebot-etqi';
            $sessionId = session()->getId();
            $credentialsPath = storage_path('app/google/dialogflow-credentials.json');

            if (!file_exists($credentialsPath)) {
                Log::error('Dialogflow credentials file not found');
                return 'Maaf, konfigurasi chatbot belum lengkap.';
            }

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

            return $fulfillmentText ?: 'Maaf, saya tidak memahami pertanyaan Anda. Bisa diulang dengan kata-kata yang berbeda?';
        } catch (\Exception $e) {
            Log::error('Dialogflow Error: ' . $e->getMessage());
            return 'Maaf, terjadi kesalahan saat memproses permintaan Anda.';
        }
    }
}
