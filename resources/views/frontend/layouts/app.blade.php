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

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Canonical URL -->
    <link rel="canonical" href="https://genbicirebon.com">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ asset('assets2/images/logo.png') }}">

    {{-- <img src="{{ asset('assets2/images/logo.png') }}" alt="Image" loading="lazy"> --}}

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
            max-height: 500px;
            overflow-y: auto;
            z-index: 9999;
            display: none;
            transition: all 0.3s ease-in-out;
        }

        .msg-row {
            display: flex;
            align-items: flex-start;
            margin: 10px;
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
            margin-right: 8px;
        }

        .msg-bubble {
            padding: 10px;
            border-radius: 15px;
            max-width: 70%;
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
        <div id="chat-float">
            <img src="{{ asset('assets2/images/chatbot.png') }}" alt="chat" width="60" height="60">
        </div>

        <div id="chat-window" class="shadow-lg border rounded bg-white">
            <div class="p-2 bg-primary text-white d-flex justify-content-between align-items-center">
                <strong>Chatbot GenBI</strong>
                <button class="btn btn-sm btn-light" onclick="toggleChat()">&times;</button>
            </div>
            <div id="chat-messages"></div>
            <div class="p-2 border-top d-flex">
                <input type="text" id="chat-input" class="form-control" placeholder="Tulis pesan...">
                <button class="btn btn-primary ms-2" onclick="sendChat()">Kirim</button>
            </div>
        </div>


    </main>

    @include('frontend.template.footer')
    {{-- @include('frontend.chatbot') --}}

    {{-- <div class="container">
        @yield('content')



    {{-- @include('frontend.template.footer') --}}


    {{-- @include('frontend.template.footer') --}}

    <!-- JavaScript Files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="{{ asset('assets2/js/wow.min.js') }}"></script>
    <script src="{{ asset('assets2/js/typed.js') }}"></script>
    <script src="{{ asset('assets2/js/aos.js') }}"></script>
    <script src="{{ asset('assets2/js/change.js') }}"></script>
    <script src="{{ asset('assets2/js/main.js') }}"></script>
    <script src="{{ asset('assets2/js/chatbot .js') }}"></script>



    <script>
        const soundSend = new Audio("/sounds/send.mp3");
        const soundReceive = new Audio("/sounds/receive.mp3");

        // Simpan session ID di variabel JavaScript agar percakapan tetap nyambung
        let chatSessionId = null;

        function toggleChat() {
            const win = document.getElementById("chat-window");
            win.style.display = win.style.display === "none" ? "block" : "none";
        }

        document.getElementById("chat-float").addEventListener("click", toggleChat);

        function appendMsg(text, sender, isTyping = false) {
            // ... fungsi appendMsg Anda sudah benar, tidak perlu diubah ...
        }

        function sendChat() {
            const input = document.getElementById("chat-input");
            const text = input.value.trim();
            if (!text) return;

            appendMsg(text, "user");
            soundSend.play().catch(() => {});
            input.value = "";

            // === PERUBAHAN UTAMA DI SINI ===
            // Ganti URL ke endpoint baru /api/chat
            fetch("/api/chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                    },
                    // Kirim payload yang lebih sederhana
                    body: JSON.stringify({
                        message: text,
                        session_id: chatSessionId // Kirim session_id jika sudah ada
                    })
                })
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(data => {
                    const reply = data.fulfillmentText || "Bot tidak bisa menjawab saat ini.";
                    // Simpan session_id yang dikembalikan dari server
                    if (data.session_id) {
                        chatSessionId = data.session_id;
                    }
                    appendMsg(reply, "bot", true);
                })
                .catch((error) => {
                    console.error("Fetch Error:", error);
                    appendMsg("⚠️ Gagal menghubungi server chatbot.", "bot");
                });
        }
    </script>


    <!-- Trigger Manual Popup -->

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Inisialisasi AOS
            AOS.init();

            // Inisialisasi Wow.js
            new WOW().init();

            // Script untuk FAQ
            const faqTitles = document.querySelectorAll('.faq-title');
            faqTitles.forEach(title => {
                title.addEventListener('click', () => {
                    const collapseID = title.getAttribute('data-target');
                    title.classList.toggle('collapsed');
                    const collapseElement = document.querySelector(collapseID);
                    collapseElement.classList.toggle('show');
                });
            });

            // Script untuk smooth scroll
            const pageLink = document.querySelectorAll(".ud-menu-scroll");
            pageLink.forEach((elem) => {
                elem.addEventListener("click", (e) => {
                    e.preventDefault();
                    document.querySelector(elem.getAttribute("href")).scrollIntoView({
                        behavior: "smooth",
                        offsetTop: 1 - 60,
                    });
                });
            });

            // Script untuk accordion
            const items = document.querySelectorAll(".accordion button");

            function toggleAccordion() {
                const itemToggle = this.getAttribute('aria-expanded');
                for (i = 0; i < items.length; i++) {
                    items[i].setAttribute('aria-expanded', 'false');
                }
                if (itemToggle == 'false') {
                    this.setAttribute('aria-expanded', 'true');
                }
            }
            items.forEach(item => item.addEventListener('click', toggleAccordion));
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const loadingSpinner = document.getElementById('loading-spinner');
            loadingSpinner.style.display = 'none';
        });

        document.getElementById("chat-input").addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                sendChat();
            }
        });
    </script>





</body>

</html>
