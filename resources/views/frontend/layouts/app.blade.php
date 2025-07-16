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
        /* Update your existing styles with these additions */
        #chat-window {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 350px;
            /* Slightly wider for better chat experience */
            height: 450px;
            max-height: 80vh;
            overflow-y: auto;
            z-index: 9999;
            display: none;
            transition: all 0.3s ease-in-out;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
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
            object-fit: cover;
        }

        .msg-bubble {
            padding: 10px 15px;
            border-radius: 15px;
            max-width: 80%;
            word-wrap: break-word;
            font-size: 14px;
            line-height: 1.4;
        }

        .msg-bubble.user {
            background-color: #0d6efd;
            color: white;
            border-bottom-right-radius: 5px;
        }

        .msg-bubble.bot {
            background-color: #f1f1f1;
            color: #333;
            border-bottom-left-radius: 5px;
        }

        #chat-messages {
            padding: 10px;
            height: calc(100% - 110px);
            overflow-y: auto;
            background-color: #fff;
        }

        #chat-input-container {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 10px;
            background: white;
            border-top: 1px solid #eee;
        }

        #chat-float {
            position: fixed;
            bottom: 20px;
            right: 20px;
            cursor: pointer;
            z-index: 9998;
            transition: all 0.3s ease;
        }

        #chat-float:hover {
            transform: scale(1.1);
        }

        .typing-indicator {
            display: flex;
            padding: 10px 15px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            margin: 0 2px;
            background-color: #ccc;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }

        .typing-dot:nth-child(1) {
            animation-delay: 0s;
        }

        .typing-dot:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-dot:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {

            0%,
            60%,
            100% {
                transform: translateY(0);
            }

            30% {
                transform: translateY(-5px);
            }
        }

        .quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-top: 10px;
        }

        .quick-reply {
            background-color: #e9ecef;
            border: none;
            border-radius: 15px;
            padding: 5px 10px;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .quick-reply:hover {
            background-color: #dee2e6;
        }
    </style>
</head>

<body>
    <!-- Your existing body content remains the same until the chat elements -->
    <!-- ... -->
    @include('frontend.template.header')
    <!-- Floating Chat Icon -->
    <div id="chat-float">
        <img src="{{ asset('assets2/images/chatbot.png') }}" alt="chat" width="60" height="60">
    </div>

    <!-- Chat Window -->
    <div id="chat-window" class="shadow-lg border rounded bg-white">
        <div class="p-2 bg-primary text-white d-flex justify-content-between align-items-center rounded-top">
            <strong>GenBI Assistant</strong>
            <button class="btn btn-sm btn-light" onclick="toggleChat()">&times;</button>
        </div>
        <div id="chat-messages">
            <!-- Messages will appear here -->
        </div>
        <div id="chat-input-container">
            <div class="input-group">
                <input type="text" id="chat-input" class="form-control" placeholder="Tulis pesan..."
                    aria-label="Message">
                <button class="btn btn-primary" type="button" onclick="sendChat()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                        viewBox="0 0 16 16">
                        <path
                            d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Your existing scripts remain the same -->
    <!-- ... -->

    @include('frontend.template.header')
    <!-- Updated Chat Script -->
    <script>
        // Session ID for tracking conversation
        const sessionId = 'genbi-' + Math.random().toString(36).substring(2, 15);
        let isTyping = false;

        // Toggle chat window visibility
        function toggleChat() {
            const win = document.getElementById("chat-window");
            win.style.display = win.style.display === "none" ? "block" : "none";

            // Auto focus input when chat opens
            if (win.style.display === "block") {
                setTimeout(() => {
                    document.getElementById("chat-input").focus();
                }, 100);

                // Send welcome message if first time opening
                if (!localStorage.getItem('chatOpened')) {
                    setTimeout(() => {
                        appendMsg("Halo! Saya GenBI Assistant. Ada yang bisa saya bantu tentang program GenBI?",
                            "bot");
                        showQuickReplies([
                            "Apa itu GenBI?",
                            "Bagaimana cara daftar?",
                            "Apa saja program GenBI?"
                        ]);
                    }, 500);
                    localStorage.setItem('chatOpened', 'true');
                }
            }
        }

        // Show typing indicator
        function showTyping() {
            if (isTyping) return;

            isTyping = true;
            const row = document.createElement("div");
            row.className = "msg-row msg-bot";
            row.id = "typing-indicator";

            const avatar = document.createElement("img");
            avatar.className = "avatar";
            avatar.src = "{{ asset('assets2/images/logo.png') }}";

            const bubble = document.createElement("div");
            bubble.className = "msg-bubble bot typing-indicator";

            // Add typing dots
            for (let i = 0; i < 3; i++) {
                const dot = document.createElement("div");
                dot.className = "typing-dot";
                bubble.appendChild(dot);
            }

            row.appendChild(avatar);
            row.appendChild(bubble);
            document.getElementById("chat-messages").appendChild(row);
            document.getElementById("chat-messages").scrollTop = document.getElementById("chat-messages").scrollHeight;
        }

        // Hide typing indicator
        function hideTyping() {
            isTyping = false;
            const indicator = document.getElementById("typing-indicator");
            if (indicator) {
                indicator.remove();
            }
        }

        // Show quick reply buttons
        function showQuickReplies(replies) {
            const container = document.createElement("div");
            container.className = "quick-replies";

            replies.forEach(reply => {
                const button = document.createElement("button");
                button.className = "quick-reply";
                button.textContent = reply;
                button.onclick = () => {
                    document.getElementById("chat-input").value = reply;
                    sendChat();
                    container.remove();
                };
                container.appendChild(button);
            });

            // Append to last bot message
            const messages = document.getElementById("chat-messages").children;
            if (messages.length > 0) {
                messages[messages.length - 1].appendChild(container);
                document.getElementById("chat-messages").scrollTop = document.getElementById("chat-messages").scrollHeight;
            }
        }

        // Append message to chat
        function appendMsg(text, sender, isQuickReply = false) {
            hideTyping(); // Hide typing indicator when message arrives

            const row = document.createElement("div");
            row.className = "msg-row " + (sender === "user" ? "msg-user" : "msg-bot");

            // Only show avatar for bot messages
            if (sender === "bot") {
                const avatar = document.createElement("img");
                avatar.className = "avatar";
                avatar.src = "{{ asset('assets2/images/logo.png') }}";
                avatar.alt = "GenBI Bot";
                row.appendChild(avatar);
            }

            const bubble = document.createElement("div");
            bubble.className = "msg-bubble " + (sender === "user" ? "user" : "bot");

            if (isQuickReply) {
                bubble.innerHTML = text; // Allow HTML for formatted responses
            } else {
                bubble.textContent = text;
            }

            row.appendChild(bubble);

            // If user message, add avatar after bubble for right alignment
            if (sender === "user") {
                const avatar = document.createElement("img");
                avatar.className = "avatar";
                avatar.src = "https://ui-avatars.com/api/?name=User&background=0D8ABC&color=fff";
                avatar.alt = "You";
                row.appendChild(avatar);
            }

            document.getElementById("chat-messages").appendChild(row);
            document.getElementById("chat-messages").scrollTop = document.getElementById("chat-messages").scrollHeight;
        }

        // Send message to Dialogflow
        async function sendChat() {
            const input = document.getElementById("chat-input");
            const message = input.value.trim();

            if (!message) return;

            // Add user message to chat
            appendMsg(message, "user");
            input.value = "";

            try {
                showTyping();

                // Call Laravel backend endpoint
                const response = await fetch("/chatbot", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        "Accept": "application/json"
                    },
                    body: JSON.stringify({
                        message: message,
                        session_id: sessionId
                    })
                });

                const data = await response.json();

                // Process response from Dialogflow
                if (data.response) {
                    appendMsg(data.response, "bot");

                    // Show quick replies for common follow-ups
                    if (data.intent === "Tentang.Genbi") {
                        showQuickReplies([
                            "Apa saja divisi GenBI?",
                            "Bagaimana cara daftar GenBI?",
                            "Apa manfaat jadi anggota GenBI?"
                        ]);
                    }
                }
            } catch (error) {
                console.error("Error:", error);
                appendMsg("Maaf, terjadi kesalahan. Silakan coba lagi nanti.", "bot");
            } finally {
                hideTyping();
            }
        }

        // Event listeners
        document.getElementById("chat-float").addEventListener("click", toggleChat);

        document.getElementById("chat-input").addEventListener("keydown", function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                sendChat();
            }
        });

        // Auto-open chat after 30 seconds if not interacted with
        setTimeout(() => {
            if (!localStorage.getItem('chatOpened')) {
                toggleChat();
            }
        }, 30000);
    </script>

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
