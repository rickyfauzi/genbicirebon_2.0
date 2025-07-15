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
        // Chatbot JavaScript - Versi yang lebih robust
        const ChatBot = {
            init() {
                this.setupEventListeners();
                this.setupSounds();
                this.welcomeMessage();
            },

            setupEventListeners() {
                // Chat float button
                document.getElementById("chat-float").addEventListener("click", () => this.toggleChat());

                // Enter key support
                document.getElementById("chat-input").addEventListener("keydown", (e) => {
                    if (e.key === "Enter") {
                        e.preventDefault();
                        this.sendChat();
                    }
                });

                // Close button di chat window
                document.querySelector('#chat-window button').addEventListener('click', () => this.toggleChat());
            },

            setupSounds() {
                this.soundSend = new Audio("/sounds/send.mp3");
                this.soundReceive = new Audio("/sounds/receive.mp3");

                // Disable sound errors
                this.soundSend.onerror = () => console.warn("Send sound not available");
                this.soundReceive.onerror = () => console.warn("Receive sound not available");
            },

            welcomeMessage() {
                // Tampilkan welcome message saat pertama kali dibuka
                const welcomeShown = localStorage.getItem('chatbot_welcome_shown');
                if (!welcomeShown) {
                    setTimeout(() => {
                        this.appendMsg('Halo! Saya chatbot GenBI Cirebon. Ada yang bisa saya bantu? 😊', 'bot',
                            true);
                        localStorage.setItem('chatbot_welcome_shown', 'true');
                    }, 1000);
                }
            },

            toggleChat() {
                const win = document.getElementById("chat-window");
                const isVisible = win.style.display === "block";
                win.style.display = isVisible ? "none" : "block";

                if (!isVisible) {
                    document.getElementById("chat-input").focus();
                }
            },

            appendMsg(text, sender, isTyping = false) {
                const row = document.createElement("div");
                row.className = `msg-row ${sender === "user" ? "msg-user" : "msg-bot"}`;

                const avatar = document.createElement("img");
                avatar.className = "avatar";
                avatar.src = sender === "user" ?
                    "http://static.vecteezy.com/system/resources/thumbnails/011/490/381/small_2x/happy-smiling-young-man-avatar-3d-portrait-of-a-man-cartoon-character-people-illustration-isolated-on-white-background-vector.jpg" :
                    "assets2/images/logo.png";

                const bubble = document.createElement("div");
                bubble.className = `msg-bubble bg-${sender === "user" ? "primary text-white" : "light"}`;
                bubble.textContent = "";

                if (sender === "bot") {
                    row.appendChild(avatar);
                    row.appendChild(bubble);
                } else {
                    row.appendChild(bubble);
                    row.appendChild(avatar);
                }

                document.getElementById("chat-messages").appendChild(row);
                this.scrollToBottom();

                if (isTyping && sender === "bot") {
                    this.typeMessage(bubble, text);
                } else {
                    bubble.textContent = text;
                    if (sender === "user") {
                        this.soundSend.play().catch(() => {});
                    }
                }
            },

            typeMessage(element, text) {
                let i = 0;
                const typing = setInterval(() => {
                    element.textContent += text.charAt(i);
                    i++;
                    if (i >= text.length) {
                        clearInterval(typing);
                        this.soundReceive.play().catch(() => {});
                    }
                }, 30);
            },

            scrollToBottom() {
                const chatMessages = document.getElementById("chat-messages");
                chatMessages.scrollTop = chatMessages.scrollHeight;
            },

            showLoading() {
                const loadingMsg = document.createElement("div");
                loadingMsg.className = "msg-row msg-bot";
                loadingMsg.id = "loading-message";
                loadingMsg.innerHTML = `
            <img class="avatar" src="assets2/images/logo.png" alt="bot">
            <div class="msg-bubble bg-light">
                <div class="typing-indicator">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
        `;

                document.getElementById("chat-messages").appendChild(loadingMsg);
                this.scrollToBottom();
            },

            hideLoading() {
                const loadingElement = document.getElementById("loading-message");
                if (loadingElement) {
                    loadingElement.remove();
                }
            },

            async sendChat() {
                const input = document.getElementById("chat-input");
                const text = input.value.trim();

                if (!text) return;

                // Tampilkan pesan user
                this.appendMsg(text, "user");
                input.value = "";
                input.disabled = true;

                // Tampilkan loading
                this.showLoading();

                const payload = {
                    queryText: text,
                    session: this.getSessionId()
                };

                try {
                    const response = await fetch("/chat", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            "Accept": "application/json",
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(payload)
                    });

                    console.log("Response Status:", response.status);

                    this.hideLoading();
                    input.disabled = false;
                    input.focus();

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();
                    console.log("Server Response:", data);

                    const reply = data.fulfillmentText || "Maaf, saya tidak mendapat balasan.";
                    this.appendMsg(reply, "bot", true);

                    // Log untuk debugging
                    if (data.source) {
                        console.log(`Response source: ${data.source}`);
                    }

                } catch (error) {
                    console.error("Chat Error:", error);

                    this.hideLoading();
                    input.disabled = false;
                    input.focus();

                    this.appendMsg("⚠️ Gangguan sementara. Silakan refresh halaman atau coba lagi nanti.", "bot");
                }
            },

            getSessionId() {
                let sessionId = localStorage.getItem('chat_session');
                if (!sessionId) {
                    sessionId = 'web-session-' + Date.now() + '-' + Math.random().toString(36).substr(2, 9);
                    localStorage.setItem('chat_session', sessionId);
                }
                return sessionId;
            },

            // Fungsi untuk testing
            async testConnection() {
                try {
                    const response = await fetch("/test-dialogflow");
                    const data = await response.json();

                    console.log("Test Results:", data);

                    if (data.all_passed) {
                        alert("✅ Semua test berhasil! Chatbot siap digunakan.");
                    } else {
                        alert("⚠️ Beberapa test gagal. Cek console untuk detail.");
                    }

                } catch (error) {
                    console.error("Test Error:", error);
                    alert("❌ Test gagal: " + error.message);
                }
            }
        };

        // Inisialisasi chatbot saat DOM ready
        document.addEventListener("DOMContentLoaded", function() {
            ChatBot.init();
        });

        // Expose functions to global scope for backward compatibility
        window.toggleChat = () => ChatBot.toggleChat();
        window.sendChat = () => ChatBot.sendChat();
        window.testConnection = () => ChatBot.testConnection();

        // Add CSS for loading indicator
        const style = document.createElement('style');
        style.textContent = `
    .typing-indicator {
        display: flex;
        align-items: center;
        gap: 3px;
        padding: 5px 0;
    }
    
    .typing-indicator span {
        width: 6px;
        height: 6px;
        background-color: #6c757d;
        border-radius: 50%;
        animation: typing 1.4s infinite ease-in-out;
    }
    
    .typing-indicator span:nth-child(1) { animation-delay: -0.32s; }
    .typing-indicator span:nth-child(2) { animation-delay: -0.16s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0s; }
    
    @keyframes typing {
        0%, 80%, 100% {
            transform: scale(0.8);
            opacity: 0.5;
        }
        40% {
            transform: scale(1.2);
            opacity: 1;
        }
    }
    
    #chat-input:disabled {
        background-color: #f8f9fa;
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    #chat-window {
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        border-radius: 10px;
        overflow: hidden;
    }
    
    #chat-messages {
        max-height: 350px;
        overflow-y: auto;
        padding: 10px;
    }
    
    .msg-bubble {
        word-wrap: break-word;
        line-height: 1.4;
    }
`;
        document.head.appendChild(style);

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
            const faqTitles = document.querySelectorAll('.faq   -title');
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
