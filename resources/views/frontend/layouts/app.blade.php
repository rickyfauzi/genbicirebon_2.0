<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'GenBI Cirebon')</title>
    <meta name="description"
        content="Website resmi GenBI Cirebon - Komunitas penerima beasiswa Bank Indonesia wilayah Cirebon. Informasi beasiswa, kegiatan mahasiswa, dan program pengembangan.">
    <meta name="keywords"
        content="GenBI Cirebon, Beasiswa Bank Indonesia, Mahasiswa Cirebon, Komunitas Mahasiswa, Pengembangan Soft Skill">

    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="GenBI Cirebon - Generasi Baru Indonesia">
    <meta property="og:description" content="Komunitas penerima beasiswa Bank Indonesia wilayah Cirebon">
    <meta property="og:image" content="{{ asset('assets2/images/GenBI white (1).png') }}">
    <meta property="og:url" content="https://genbicirebon.com">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="GenBI Cirebon">
    <meta name="twitter:description" content="Komunitas penerima beasiswa Bank Indonesia wilayah Cirebon">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="https://genbicirebon.com">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets2/images/logo.png') }}">

    <!-- CSS Files -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('assets2/css/animate.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets2/css/lineicons.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets2/css/style.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets2/css/aos.css') }}" />
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.min.css" />

    <style>
        #chat-window {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 300px;
            height: 450px;
            /* Gave it a fixed height */
            z-index: 9999;
            display: none;
            transition: all 0.3s ease-in-out;
            flex-direction: column;
            /* Added for layout */
            background-color: #fff;
            /* Added for consistency */
        }

        #chat-messages {
            flex-grow: 1;
            /* Make message area fill available space */
            overflow-y: auto;
            padding: 10px;
        }

        .msg-row {
            display: flex;
            align-items: flex-end;
            /* Align to bottom for better look */
            margin-bottom: 15px;
        }

        .msg-user {
            justify-content: flex-end;
        }

        .msg-bot {
            justify-content: flex-start;
        }

        .avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            margin: 0 8px;
        }

        .msg-user .avatar {
            order: 2;
            /* Move avatar to the right for user messages */
        }

        .msg-bubble {
            padding: 10px 15px;
            border-radius: 18px;
            max-width: 80%;
            /* Increased max-width */
            word-wrap: break-word;
        }

        .msg-user .msg-bubble {
            border-bottom-right-radius: 5px;
        }

        .msg-bot .msg-bubble {
            border-bottom-left-radius: 5px;
        }
    </style>
</head>

<body>

    <div class="loading-spinner" id="loading-spinner">
        <div class="spinner"></div>
    </div>

    <!-- Konten Halaman Web -->
    @include('frontend.template.header')

    <main>
        @yield('content')

        <!-- Floating Chat Icon -->
        <div id="chat-float" style="position: fixed; bottom: 20px; right: 20px; cursor: pointer; z-index: 10000;">
            <img src="{{ asset('assets2/images/chatbot.png') }}" alt="chat" width="60" height="60">
        </div>

        <!-- Custom Chat Window -->
        <div id="chat-window" class="shadow-lg border rounded">
            <div class="p-2 bg-primary text-white d-flex justify-content-between align-items-center">
                <strong>Chatbot GenBI</strong>
                <button id="close-chat-btn" class="btn btn-sm btn-light">×</button>
            </div>
            <div id="chat-messages"></div>
            <div class="p-2 border-top d-flex">
                <input type="text" id="chat-input" class="form-control" placeholder="Tulis pesan...">
                <button id="send-chat-btn" class="btn btn-primary ms-2">Kirim</button>
            </div>
        </div>
    </main>

    @include('frontend.template.footer')

    <!-- JavaScript Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="{{ asset('assets2/js/wow.min.js') }}"></script>
    <script src="{{ asset('assets2/js/typed.js') }}"></script>
    <script src="{{ asset('assets2/js/aos.js') }}"></script>
    <script src="{{ asset('assets2/js/change.js') }}"></script>
    <script src="{{ asset('assets2/js/main.js') }}"></script>

    <!-- =================================================================
    // KODE CHATBOT TERPUSAT DAN SUDAH DIPERBAIKI
    // ================================================================= -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // --- Inisialisasi Tema & Animasi ---
            AOS.init();
            new WOW().init();

            // Sembunyikan loading spinner saat halaman siap
            const loadingSpinner = document.getElementById('loading-spinner');
            if (loadingSpinner) {
                loadingSpinner.style.display = 'none';
            }

            // --- Logika Chatbot ---
            const chatWindow = document.getElementById("chat-window");
            const chatFloat = document.getElementById("chat-float");
            const closeChatBtn = document.getElementById("close-chat-btn");
            const chatInput = document.getElementById("chat-input");
            const chatMessages = document.getElementById("chat-messages");
            const sendChatBtn = document.getElementById("send-chat-btn");

            // Buat session ID unik untuk setiap percakapan
            const sessionId = 'web-session-' + Date.now() + '-' + Math.random().toString(36).substring(2, 9);

            // Fungsi untuk membuka/menutup jendela chat
            const toggleChat = () => {
                const isHidden = chatWindow.style.display === "none" || chatWindow.style.display === "";
                chatWindow.style.display = isHidden ? "flex" : "none";

                // Tampilkan pesan selamat datang jika chat baru dibuka dan kosong
                if (isHidden && chatMessages.children.length === 0) {
                    appendMsg("Halo! Saya chatbot GenBI Cirebon. Ada yang bisa saya bantu? 😊", "bot", true);
                }
            };

            // Event listener untuk ikon dan tombol tutup
            chatFloat.addEventListener("click", toggleChat);
            closeChatBtn.addEventListener("click", toggleChat);

            // Fungsi untuk menambahkan pesan ke tampilan chat
            const appendMsg = (text, sender, isTyping = false) => {
                const row = document.createElement("div");
                row.className = "msg-row " + (sender === "user" ? "msg-user" : "msg-bot");

                const avatar = document.createElement("img");
                avatar.className = "avatar";
                avatar.src = sender === "user" ?
                    "https://static.vecteezy.com/system/resources/thumbnails/011/490/381/small_2x/happy-smiling-young-man-avatar-3d-portrait-of-a-man-cartoon-character-people-illustration-isolated-on-white-background-vector.jpg" :
                    "{{ asset('assets2/images/logo.png') }}";

                const bubble = document.createElement("div");
                bubble.className = "msg-bubble bg-" + (sender === "user" ? "primary text-white" : "light");

                // Susun elemen berdasarkan pengirim
                if (sender === 'user') {
                    row.appendChild(bubble);
                    row.appendChild(avatar);
                } else {
                    row.appendChild(avatar);
                    row.appendChild(bubble);
                }

                chatMessages.appendChild(row);
                chatMessages.scrollTop = chatMessages.scrollHeight; // Auto-scroll

                // Efek mengetik untuk bot
                if (isTyping) {
                    let i = 0;
                    bubble.textContent = "";
                    const typingInterval = setInterval(() => {
                        if (i < text.length) {
                            bubble.textContent += text.charAt(i);
                            i++;
                        } else {
                            clearInterval(typingInterval);
                        }
                    }, 20); // Kecepatan mengetik
                } else {
                    bubble.textContent = text;
                }
            };

            // Fungsi untuk mengirim pesan ke backend (Laravel)
            const sendChat = async () => {
                const text = chatInput.value.trim();
                if (!text) return;

                appendMsg(text, "user");
                chatInput.value = "";
                sendChatBtn.disabled = true;

                try {
                    const response = await fetch("{{ url('/dialogflow-webhook') }}", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content')
                        },
                        body: JSON.stringify({
                            queryText: text,
                            sessionId: sessionId // Kirim session ID ke backend
                        })
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const data = await response.json();
                    const reply = data.fulfillmentText ||
                        "Maaf, saya tidak mengerti. Coba tanyakan hal lain.";
                    appendMsg(reply, "bot", true);

                } catch (error) {
                    console.error("Error sending chat:", error);
                    appendMsg("⚠️ Terjadi kesalahan. Silakan coba lagi nanti.", "bot", true);
                } finally {
                    sendChatBtn.disabled = false;
                    chatInput.focus();
                }
            };

            // Event listener untuk tombol kirim dan tombol Enter
            sendChatBtn.addEventListener('click', sendChat);
            chatInput.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    sendChat();
                }
            });
        });
    </script>
</body>

</html>
