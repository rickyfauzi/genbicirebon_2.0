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

        function toggleChat() {
            const win = document.getElementById("chat-window");
            win.style.display = win.style.display === "none" ? "block" : "none";
        }

        document.getElementById("chat-float").addEventListener("click", toggleChat);

        function appendMsg(text, sender, isTyping = false) {
            const row = document.createElement("div");
            row.className = "msg-row " + (sender === "user" ? "msg-user" : "msg-bot");

            const avatar = document.createElement("img");
            avatar.className = "avatar";
            avatar.src = sender === "user" ?
                "http://static.vecteezy.com/system/resources/thumbnails/011/490/381/small_2x/happy-smiling-young-man-avatar-3d-portrait-of-a-man-cartoon-character-people-illustration-isolated-on-white-background-vector.jpg" :
                "assets2/images/logo.png";

            const bubble = document.createElement("div");
            bubble.className = "msg-bubble bg-" + (sender === "user" ? "primary text-white" : "light");
            bubble.textContent = "";

            row.appendChild(avatar);
            row.appendChild(bubble);
            document.getElementById("chat-messages").appendChild(row);
            document.getElementById("chat-messages").scrollTop = document.getElementById("chat-messages").scrollHeight;

            if (isTyping) {
                let i = 0;
                const typing = setInterval(() => {
                    bubble.textContent += text.charAt(i);
                    i++;
                    if (i >= text.length) {
                        clearInterval(typing);
                        soundReceive.play().catch(() => {});
                    }
                }, 25);
            } else {
                bubble.textContent = text;
                if (sender === "bot") soundReceive.play().catch(() => {});
            }
        }

        // Replace the existing sendChat function with this simplified version
        function sendChat() {
            const input = document.getElementById("chat-input");
            const text = input.value.trim();
            if (!text) return;

            appendMsg(text, "user");
            input.value = "";
            soundSend.play().catch(() => {}); // Mainkan suara saat mengirim

            const payload = {
                queryText: text,
                // Kirim session ID yang unik untuk setiap percakapan
                // Anda bisa menyimpannya di localStorage jika ingin percakapan berlanjut
                session: "web-session-" + (localStorage.getItem('chat_session_id') || Date.now())
            };

            // Jika belum ada session id, buat dan simpan
            if (!localStorage.getItem('chat_session_id')) {
                localStorage.setItem('chat_session_id', Date.now());
            }

            fetch("/chat", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => {
                    // Cek jika response dari server tidak OK (misal: error 500)
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json(); // Ubah response menjadi format JSON
                })
                .then(data => {
                    // Ambil teks balasan dari data JSON
                    console.log("Response from server:", data); // Untuk debugging
                    const reply = data.fulfillmentText || "Maaf, saya tidak mendapat balasan.";

                    // Tampilkan balasan dari bot dengan efek mengetik
                    appendMsg(reply, "bot", true);
                })
                .catch(error => {
                    // Tangani jika terjadi error koneksi atau error dari server
                    console.error("Fetch Error:", error);
                    appendMsg("⚠️ Maaf, terjadi gangguan. Silakan coba lagi nanti.", "bot");
                });
        }

        // Add this for Enter key support
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("chat-input").addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                    sendChat();
                }
            });
        });

        // Test function for debugging
        function testWebhook() {
            fetch("https://genbicirebon.org/dialogflow-webhook", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        queryText: "test",
                        session: "test-session"
                    })
                })
                .then(response => response.json())
                .then(data => console.log("Test result:", data))
                .catch(error => console.error("Test error:", error));
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
