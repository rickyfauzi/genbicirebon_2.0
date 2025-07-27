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
        /* Modern Chatbot Styles */
        :root {
            --chatbot-primary: #3056d3;
            --chatbot-secondary: #6c757d;
            --chatbot-success: #198754;
            --chatbot-danger: #dc3545;
            --chatbot-warning: #ffc107;
            --chatbot-info: #0dcaf0;
            --chatbot-light: #f8f9fa;
            --chatbot-dark: #212529;
            --chatbot-gradient: linear-gradient(135deg, #3056d3 0%, #764ba2 100%);
        }

        /* Floating Chat Button */
        #chat-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 70px;
            height: 70px;
            background: var(--chatbot-gradient);
            border-radius: 50%;
            cursor: pointer;
            z-index: 9998;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            animation: pulse-float 2s infinite;
        }

        #chat-float:hover {
            transform: scale(1.1) translateY(-2px);
            box-shadow: 0 12px 35px rgba(0, 0, 0, 0.25);
        }

        #chat-float img {
            width: 40px;
            height: 40px;
            filter: brightness(0) invert(1);
        }

        @keyframes pulse-float {
            0% {
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15), 0 0 0 0 rgba(102, 126, 234, 0.7);
            }

            70% {
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15), 0 0 0 10px rgba(102, 126, 234, 0);
            }

            100% {
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15), 0 0 0 0 rgba(102, 126, 234, 0);
            }
        }

        /* Chat Window */
        #chat-window {
            position: fixed;
            bottom: 120px;
            right: 30px;
            width: 380px;
            height: 520px;
            max-height: 85vh;
            z-index: 9999;
            display: none;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        #chat-window.show {
            display: block;
            animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Chat Header */
        .chat-header {
            background: var(--chatbot-gradient);
            padding: 20px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-radius: 20px 20px 0 0;
        }

        .chat-header h5 {
            color: white;

        }

        .chat-header-info {
            display: flex;
            align-items: center;
        }

        .chat-header img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            margin-right: 12px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .chat-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 16px;
        }

        .chat-status {
            font-size: 12px;
            opacity: 0.9;
            display: flex;
            align-items: center;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background: #4ade80;
            border-radius: 50%;
            margin-right: 6px;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }

            100% {
                opacity: 1;
            }
        }

        .chat-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            font-size: 18px;
            cursor: pointer;
        }

        .chat-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        /* Chat Messages Area */
        #chat-messages {
            height: 350px;
            overflow-y: auto;
            padding: 20px;
            background: #fafbfc;
            scroll-behavior: smooth;
        }

        #chat-messages::-webkit-scrollbar {
            width: 4px;
        }

        #chat-messages::-webkit-scrollbar-track {
            background: transparent;
        }

        #chat-messages::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        /* Message Rows */
        .msg-row {
            display: flex;
            margin-bottom: 16px;
            align-items: flex-end;
            animation: fadeInUp 0.3s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .msg-row.msg-user {
            justify-content: flex-end;
        }

        .msg-row.msg-bot {
            justify-content: flex-start;
        }

        /* Message Bubbles */
        .msg-bubble {
            max-width: 75%;
            padding: 12px 16px;
            border-radius: 18px;
            font-size: 14px;
            line-height: 1.5;
            word-wrap: break-word;
            position: relative;
        }

        .msg-bubble.user {
            background: var(--chatbot-gradient);
            color: white;
            border-bottom-right-radius: 6px;
            margin-left: 12px;
        }

        .msg-bubble.bot {
            background: white;
            color: #374151;
            border-bottom-left-radius: 6px;
            margin-right: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Avatar Styles */
        .chat-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .chat-avatar.user {
            background: var(--chatbot-gradient);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
        }

        .chat-avatar.bot {
            border: 2px solid #e5e7eb;
        }

        /* Typing Indicator */
        .typing-indicator {
            background: white;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .typing-dots {
            display: flex;
            gap: 4px;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background: #9ca3af;
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
                opacity: 0.4;
            }

            30% {
                transform: translateY(-10px);
                opacity: 1;
            }
        }

        /* Quick Replies */
        .quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
        }

        .quick-reply {
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            color: #475569;
            border-radius: 20px;
            padding: 8px 16px;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            white-space: nowrap;
        }

        .quick-reply:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }

        /* Chat Input */
        #chat-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e5e7eb;
        }

        .chat-input-group {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 25px;
            padding: 4px;
            transition: all 0.2s;
        }

        .chat-input-group:focus-within {
            border-color: var(--chatbot-primary);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        #chat-input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 12px 16px;
            border-radius: 20px;
            outline: none;
            font-size: 14px;
        }

        #chat-input::placeholder {
            color: #9ca3af;
        }

        .chat-send-btn {
            background: var(--chatbot-gradient);
            border: none;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .chat-send-btn:hover {
            transform: scale(1.05);
        }

        .chat-send-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        /* Welcome Message */
        .welcome-message {
            text-align: center;
            padding: 30px 20px;
            color: #6b7280;
        }

        .welcome-message h6 {
            color: #000;
        }

        .welcome-icon {
            width: 60px;
            height: 60px;
            margin: 0 auto 16px;
            background: var(--chatbot-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .welcome-icon img {
            width: 30px;
            height: 30px;
            filter: brightness(0) invert(1);
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            #chat-window {
                width: calc(100vw - 20px);
                height: calc(100vh - 100px);
                right: 10px;
                bottom: 90px;
                border-radius: 15px;
            }

            #chat-float {
                right: 20px;
                bottom: 20px;
                width: 60px;
                height: 60px;
            }

            #chat-float img {
                width: 30px;
                height: 30px;
            }

            #chat-messages {
                height: calc(100vh - 280px);
            }
        }

        /* Loading spinner styles */
        .loading-spinner {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--chatbot-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .quick-replies {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-top: 12px;
            padding: 8px 0;
        }

        .quick-reply {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 18px;
            padding: 8px 16px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            white-space: nowrap;
        }

        .quick-reply:hover {
            background-color: #e0e0e0;
            transform: translateY(-2px);
        }

        .msg-bot .quick-reply {
            background-color: #e3f2fd;
            border-color: #bbdefb;
        }

        .msg-bot .quick-reply:hover {
            background-color: #bbdefb;
        }
    </style>
</head>

<body>
    <div class="loading-spinner" id="loading-spinner">
        <div class="spinner"></div>
    </div>

    @include('frontend.template.header')

    <main>
        @yield('content')

        <!-- Floating Chat Button -->
        <div id="chat-float" onclick="toggleChat()" title="Chat dengan GenBI Assistant">
            <img src="{{ asset('assets2/images/chatbot.png') }}" alt="Chat">
        </div>

        <!-- Chat Window -->
        <div id="chat-window">
            <!-- Chat Header -->
            <div class="chat-header">
                <div class="chat-header-info">
                    <img src="{{ asset('assets2/images/logo.png') }}" alt="GenBI">
                    <div>
                        <h5>GenBI Assistant</h5>
                        <div class="chat-status">
                            <span class="status-dot"></span>
                            Online
                        </div>
                    </div>
                </div>
                <button class="chat-close" onclick="toggleChat()" title="Tutup chat">
                    ×
                </button>
            </div>

            <!-- Chat Messages -->
            <div id="chat-messages">
                <div class="welcome-message">
                    <div class="welcome-icon">
                        <img src="{{ asset('assets2/images/logo.png') }}" alt="GenBI">
                    </div>
                    <h6>Selamat datang di GenBI Assistant!</h6>
                    <p>Saya siap membantu Anda dengan informasi tentang program GenBI Cirebon.</p>
                </div>
            </div>

            <!-- Chat Input -->
            <div id="chat-input-container">
                <div class="chat-input-group">
                    <input type="text" id="chat-input" placeholder="Ketik pesan Anda..." maxlength="500">
                    <button class="chat-send-btn" id="send-btn" onclick="sendChat()" title="Kirim pesan">
                        <svg width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                            <path
                                d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576 6.636 10.07Zm6.787-8.201L1.591 6.602l4.339 2.76 7.494-7.493Z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </main>

    @include('frontend.template.footer')

    <!-- JavaScript Files -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
    <script src="{{ asset('assets2/js/wow.min.js') }}"></script>
    <script src="{{ asset('assets2/js/typed.js') }}"></script>
    <script src="{{ asset('assets2/js/aos.js') }}"></script>
    <script src="{{ asset('assets2/js/change.js') }}"></script>
    <script src="{{ asset('assets2/js/main.js') }}"></script>

    <script>
        // Chatbot UI Controller - GenBI Assistant
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize chat when page loads
            initChatSystem();
        });

        // Chat System Core
        const ChatSystem = (function() {
            // Private variables
            let isTyping = false;
            let chatInitialized = false;
            const sessionId = 'genbi-' + Math.random().toString(36).substring(2, 15);
            const typingDelay = 300; // ms delay between actions

            // DOM Elements
            const elements = {
                chatInput: document.getElementById('chat-input'),
                sendBtn: document.getElementById('send-btn'),
                chatWindow: document.getElementById('chat-window'),
                chatMessages: document.getElementById('chat-messages'),
                toggleBtn: document.getElementById('chat-toggle')
            };

            // Initialize all chat functionality
            function init() {
                setupEventListeners();
                setupCSRF();
                autoOpenChat();
            }

            // Set up all event listeners
            function setupEventListeners() {
                // Message input handling
                elements.chatInput.addEventListener('keypress', function(e) {
                    if (e.which === 13 && !e.shiftKey) {
                        e.preventDefault();
                        sendMessage();
                    }
                });

                // Send button click
                elements.sendBtn.addEventListener('click', sendMessage);

                // Chat toggle
                if (elements.toggleBtn) {
                    elements.toggleBtn.addEventListener('click', toggleChat);
                }

                // Auto-focus when chat is opened
                elements.chatWindow.addEventListener('shown.bs.collapse', function() {
                    elements.chatInput.focus();
                    if (!chatInitialized) {
                        showWelcomeMessage();
                        chatInitialized = true;
                    }
                });
            }

            // Setup CSRF token for AJAX requests
            function setupCSRF() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            }

            // Auto-open chat after delay
            function autoOpenChat() {
                setTimeout(() => {
                    if (!chatInitialized && !elements.chatWindow.classList.contains('show')) {
                        new bootstrap.Collapse(elements.chatWindow).show();
                    }
                }, 30000);
            }

            // Toggle chat visibility
            function toggleChat() {
                const chatCollapse = new bootstrap.Collapse(elements.chatWindow);
                chatCollapse.toggle();
            }

            // Send message to server
            function sendMessage() {
                const message = elements.chatInput.value.trim();
                if (!message || isTyping) return;

                // Clear input and disable button
                elements.chatInput.value = '';
                elements.sendBtn.disabled = true;

                // Display user message
                appendMessage(message, 'user');

                // Show typing indicator
                showTypingIndicator();

                // AJAX request to server
                $.ajax({
                    url: "{{ route('chatbot.sendMessage') }}",
                    type: 'POST',
                    data: {
                        message: message,
                        session_id: sessionId
                    },
                    success: handleSuccessResponse,
                    error: handleErrorResponse,
                    complete: function() {
                        elements.sendBtn.disabled = false;
                        elements.chatInput.focus();
                    }
                });
            }

            // Handle successful response from server
            function handleSuccessResponse(response) {
                hideTypingIndicator();

                if (response && response.message) {
                    appendMessage(response.message, 'bot');

                    // Process quick replies
                    setTimeout(() => {
                        if (response.quick_replies && response.quick_replies.length > 0) {
                            showQuickReplies(response.quick_replies);
                        } else {
                            showContextualQuickReplies(response.message);
                        }
                    }, typingDelay);
                } else {
                    appendMessage("Maaf, saya tidak dapat memproses pesan Anda saat ini.", 'bot');
                    showQuickReplies(["Coba lagi", "Menu utama", "Hubungi admin"]);
                }
            }

            // Handle error response
            function handleErrorResponse(xhr) {
                hideTypingIndicator();

                let errorMessage = "Maaf, terjadi gangguan koneksi";
                let quickReplies = ["Coba lagi", "Menu utama"];

                if (xhr.status === 500) {
                    errorMessage = "Sedang ada masalah teknis, tim kami sedang memperbaikinya";
                    quickReplies = ["Refresh halaman", "Hubungi admin"];
                } else if (xhr.status === 429) {
                    errorMessage = "Terlalu banyak permintaan, silakan tunggu sebentar";
                }

                appendMessage(errorMessage, 'bot');
                showQuickReplies(quickReplies);
            }

            // Show welcome message
            function showWelcomeMessage() {
                appendMessage(
                    "Halo! Saya GenBI Assistant. Ada yang bisa saya bantu tentang program GenBI Cirebon?",
                    'bot'
                );

                setTimeout(() => {
                    showQuickReplies([
                        "Apa itu GenBI?",
                        "Syarat beasiswa GenBI",
                        "Program unggulan",
                        "Cara mendaftar"
                    ]);
                }, typingDelay);
            }

            // Append message to chat
            function appendMessage(text, sender) {
                const timestamp = new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                const messageElement = document.createElement('div');
                messageElement.className = `msg-row msg-${sender}`;

                if (sender === 'bot') {
                    messageElement.innerHTML = `
                <img src="{{ asset('assets2/images/logo.png') }}" alt="GenBI" class="chat-avatar bot">
                <div class="msg-bubble bot">
                    ${text}
                    <div class="message-timestamp">${timestamp}</div>
                </div>
            `;
                } else {
                    messageElement.innerHTML = `
                <div class="msg-bubble user">
                    ${escapeHtml(text)}
                    <div class="message-timestamp">${timestamp}</div>
                </div>
                <div class="chat-avatar user">U</div>
            `;
                }

                elements.chatMessages.appendChild(messageElement);
                scrollToBottom();
            }

            // Show typing indicator
            function showTypingIndicator() {
                if (isTyping) return;

                isTyping = true;
                const typingElement = document.createElement('div');
                typingElement.id = 'typing-indicator';
                typingElement.className = 'msg-row msg-bot';
                typingElement.innerHTML = `
            <img src="{{ asset('assets2/images/logo.png') }}" alt="GenBI" class="chat-avatar bot">
            <div class="msg-bubble bot typing-indicator">
                <div class="typing-dots">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        `;

                elements.chatMessages.appendChild(typingElement);
                scrollToBottom();
            }

            // Hide typing indicator
            function hideTypingIndicator() {
                isTyping = false;
                const indicator = document.getElementById('typing-indicator');
                if (indicator) {
                    indicator.remove();
                }
            }

            // Show quick replies
            function showQuickReplies(replies) {
                // Remove existing quick replies
                const existingReplies = document.querySelectorAll('.quick-replies');
                existingReplies.forEach(el => el.remove());

                if (!replies || replies.length === 0) return;

                const repliesContainer = document.createElement('div');
                repliesContainer.className = 'quick-replies';

                replies.forEach(reply => {
                    const button = document.createElement('button');
                    button.className = 'quick-reply';
                    button.textContent = reply;
                    button.addEventListener('click', function() {
                        elements.chatInput.value = reply;
                        sendMessage();
                        repliesContainer.remove();
                    });
                    repliesContainer.appendChild(button);
                });

                elements.chatMessages.appendChild(repliesContainer);
                scrollToBottom();
            }

            // Show contextual quick replies based on conversation
            function showContextualQuickReplies(botResponse) {
                const responseText = botResponse.toLowerCase();
                let replies = [];

                if (responseText.includes('beasiswa')) {
                    replies = ["Syarat pendaftaran", "Dokumen yang dibutuhkan", "Timeline pendaftaran"];
                } else if (responseText.includes('program') || responseText.includes('kegiatan')) {
                    replies = ["Program sosial", "Program edukasi", "Event mendatang"];
                } else if (responseText.includes('daftar') || responseText.includes('pendaftaran')) {
                    replies = ["Formulir pendaftaran", "Persyaratan dokumen", "Jadwal seleksi"];
                } else {
                    replies = ["Info lebih lanjut", "Hubungi admin", "FAQ lainnya"];
                }

                showQuickReplies(replies);
            }

            // Scroll to bottom of chat
            function scrollToBottom() {
                setTimeout(() => {
                    elements.chatMessages.scrollTop = elements.chatMessages.scrollHeight;
                }, 100);
            }

            // Escape HTML to prevent XSS
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            // Public API
            return {
                init: init
            };
        })();

        // Initialize the chat system
        function initChatSystem() {
            ChatSystem.init();
        }

        // Global function for quick reply handling (if needed)
        function handleQuickReply(text) {
            const input = document.getElementById('chat-input');
            if (input) {
                input.value = text;
                ChatSystem.sendMessage();
            }
        }
    </script>
</body>

</html>
