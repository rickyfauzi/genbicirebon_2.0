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
        $(document).ready(function() {
            // Setup CSRF Token untuk semua request AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Hide loading spinner when page is ready
            $('#loading-spinner').fadeOut();

            // Initialize chat
            initializeChat();
        });

        // Chat variables
        let isTyping = false;
        let chatInitialized = false;
        const sessionId = 'genbi-' + Math.random().toString(36).substring(2, 15);

        // Initialize chat functionality
        function initializeChat() {
            // Event listener for Enter key in input
            $('#chat-input').on('keypress', function(e) {
                if (e.which === 13 && !e.shiftKey) {
                    e.preventDefault();
                    sendChat();
                }
            });

            // Auto-focus input when typing
            $(document).on('keydown', function(e) {
                if (!$('#chat-window').is(':visible')) return;
                if (e.target.tagName.toLowerCase() !== 'input' && e.target.tagName
                    .toLowerCase() !== 'textarea') {
                    $('#chat-input').focus();
                }
            });
        }

        // Toggle chat window
        function toggleChat() {
            const chatWindow = $('#chat-window');
            const isVisible = chatWindow.is(':visible');

            if (isVisible) {
                chatWindow.removeClass('show').fadeOut(300);
            } else {
                chatWindow.addClass('show').fadeIn(300);
                $('#chat-input').focus();

                // Send welcome message if first time
                if (!chatInitialized) {
                    setTimeout(() => {
                        showWelcomeMessage();
                        chatInitialized = true;
                    }, 2000);
                }
            }
        }

        // Show welcome message with quick replies
        function showWelcomeMessage() {
            $('.welcome-message').fadeOut(300, function() {
                appendMessage(
                    "Halo! Saya GenBI Assistant. Ada yang bisa saya bantu tentang program GenBI Cirebon?",
                    'bot',
                    true
                );

                setTimeout(() => {
                    showQuickReplies([
                        "Apa itu GenBI?",
                        "Syarat beasiswa GenBI",
                        "Program unggulan",
                        "Cara mendaftar"
                    ]);
                }, 1000);
            });
        }

        // Send message function
        function sendChat() {
            const input = $('#chat-input');
            const message = input.val().trim();
            const sendBtn = $('#send-btn');

            if (!message || isTyping) return;

            // Clear input and disable send button
            input.val('');
            sendBtn.prop('disabled', true);

            // Show user message
            appendMessage(message, 'user');

            // Show typing indicator
            showTypingIndicator();

            // Send to server
            $.ajax({
                url: "{{ route('chatbot.sendMessage') }}",
                type: 'POST',
                data: {
                    message: message,
                    session_id: sessionId
                },
                timeout: 10000,
                success: function(response) {
                    hideTypingIndicator();

                    if (response && response.message) {
                        appendMessage(response.message, 'bot');

                        // Show contextual quick replies
                        setTimeout(() => {
                            showContextualQuickReplies(message, response.message);
                        }, 500);
                    } else {
                        appendMessage("Maaf, saya tidak dapat memproses pesan Anda saat ini.", 'bot');
                    }
                },
                error: function(xhr, status, error) {
                    hideTypingIndicator();
                    console.error('Chat Error:', error);

                    let errorMessage = "Maaf, terjadi kesalahan saat menghubungi server.";

                    if (status === 'timeout') {
                        errorMessage = "Koneksi timeout. Silakan coba lagi.";
                    } else if (xhr.status === 429) {
                        errorMessage = "Terlalu banyak pesan. Silakan tunggu sebentar.";
                    } else if (xhr.status === 500) {
                        errorMessage = "Terjadi kesalahan server. Tim kami sedang memperbaikinya.";
                    }

                    appendMessage(errorMessage, 'bot');
                },
                complete: function() {
                    sendBtn.prop('disabled', false);
                    input.focus();
                }
            });
        }

        // Append message to chat
        function appendMessage(text, sender, isWelcome = false) {
            const messagesContainer = $('#chat-messages');
            const timestamp = new Date().toLocaleTimeString('id-ID', {
                hour: '2-digit',
                minute: '2-digit'
            });

            let messageHTML = '';

            if (sender === 'user') {
                messageHTML = `
                    <div class="msg-row msg-user">
                        <div class="msg-bubble user">
                            ${escapeHtml(text)}
                            <div style="font-size: 11px; opacity: 0.7; margin-top: 4px;">${timestamp}</div>
                        </div>
                        <div class="chat-avatar user">U</div>
                    </div>
                `;
            } else {
                messageHTML = `
                    <div class="msg-row msg-bot">
                        <img src="{{ asset('assets2/images/logo.png') }}" alt="GenBI" class="chat-avatar bot">
                        <div class="msg-bubble bot">
                            ${text}
                            <div style="font-size: 11px; opacity: 0.5; margin-top: 4px;">${timestamp}</div>
                        </div>
                    </div>
                `;
            }

            messagesContainer.append(messageHTML);
            scrollToBottom();
        }

        // Show typing indicator
        function showTypingIndicator() {
            if (isTyping) return;

            isTyping = true;
            const typingHTML = `
                <div class="msg-row msg-bot" id="typing-indicator">
                    <img src="{{ asset('assets2/images/logo.png') }}" alt="GenBI" class="chat-avatar bot">
                    <div class="msg-bubble bot typing-indicator">
                        <div class="typing-dots">
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                            <div class="typing-dot"></div>
                        </div>
                    </div>
                </div>
            `;

            $('#chat-messages').append(typingHTML);
            scrollToBottom();
        }

        // Hide typing indicator
        function hideTypingIndicator() {
            isTyping = false;
            $('#typing-indicator').remove();
        }

        // Show quick replies
        function showQuickReplies(replies) {
            const quickRepliesHTML = `
                <div class="quick-replies">
                    ${replies.map(reply =>
                        `<button class="quick-reply" onclick="handleQuickReply('${escapeHtml(reply)}')">${reply}</button>`
                    ).join('')}
                </div>
            `;

            $('#chat-messages .msg-row:last-child .msg-bubble').append(quickRepliesHTML);
            scrollToBottom();
        }

        // Show contextual quick replies based on conversation
        function showContextualQuickReplies(userMessage, botResponse) {
            const message = userMessage.toLowerCase();
            let replies = [];

            if (message.includes('genbi') || message.includes('apa itu')) {
                replies = ["Syarat beasiswa", "Program unggulan", "Cara mendaftar"];
            } else if (message.includes('syarat') || message.includes('persyaratan')) {
                replies = ["Dokumen yang dibutuhkan", "Timeline pendaftaran", "Tips lolos seleksi"];
            } else if (message.includes('program') || message.includes('kegiatan')) {
                replies = ["Program sosial", "Program edukasi", "Event mendatang"];
            } else if (message.includes('daftar') || message.includes('mendaftar')) {
                replies = ["Syarat pendaftaran", "Link pendaftaran", "Kontak admin"];
            } else {
                // Default replies
                replies = ["Info lebih lanjut", "Hubungi admin", "FAQ lainnya"];
            }

            if (replies.length > 0) {
                showQuickReplies(replies);
            }
        }

        // Handle quick reply click
        function handleQuickReply(text) {
            $('#chat-input').val(text);
            sendChat();

            // Remove quick replies after selection
            $('.quick-replies').fadeOut(200, function() {
                $(this).remove();
            });
        }

        // Scroll to bottom of messages
        function scrollToBottom() {
            const messagesContainer = $('#chat-messages');
            messagesContainer.animate({
                scrollTop: messagesContainer[0].scrollHeight
            }, 300);
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }

        // Auto-open chat after 30 seconds if not interacted
        setTimeout(() => {
            if (!chatInitialized && !$('#chat-window').is(':visible')) {
                toggleChat();
            }
        }, 30000);

        // Initialize other scripts
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize AOS
            if (typeof AOS !== 'undefined') {
                AOS.init();
            }

            // Initialize WOW
            // Initialize WOW
            if (typeof WOW !== 'undefined') {
                new WOW().init();
            }

            // Initialize Typed.js if elements exist
            if ($('.typed-text-output').length) {
                const typedStrings = $('.typed-text').text();
                const typed = new Typed('.typed-text-output', {
                    strings: typedStrings.split(','),
                    typeSpeed: 100,
                    backSpeed: 20,
                    smartBackspace: false,
                    loop: true
                });
            }

            // Magnific Popup for image galleries
            if (typeof $.fn.magnificPopup !== 'undefined') {
                $('.popup-image').magnificPopup({
                    type: 'image',
                    closeOnContentClick: true,
                    mainClass: 'mfp-img-mobile',
                    image: {
                        verticalFit: true
                    }
                });
            }

            // Smooth scrolling for anchor links
            $('a[href*="#"]').not('[href="#"]').not('[href="#0"]').click(function(event) {
                if (
                    location.pathname.replace(/^\//, '') == this.pathname.replace(/^\//, '') &&
                    location.hostname == this.hostname
                ) {
                    let target = $(this.hash);
                    target = target.length ? target : $('[name=' + this.hash.slice(1) + ']');
                    if (target.length) {
                        event.preventDefault();
                        $('html, body').animate({
                            scrollTop: target.offset().top - 100
                        }, 1000, 'easeInOutExpo');
                    }
                }
            });

            // Back to top button
            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    $('.back-to-top').fadeIn('slow');
                } else {
                    $('.back-to-top').fadeOut('slow');
                }
            });

            $('.back-to-top').click(function() {
                $('html, body').animate({
                    scrollTop: 0
                }, 1500, 'easeInOutExpo');
                return false;
            });

            // Mobile navigation toggle
            $('.navbar-toggler').click(function() {
                $('.navbar-collapse').slideToggle(300);
            });

            // Close mobile menu when clicking on a link
            $('.navbar-nav a').click(function() {
                if ($('.navbar-toggler').is(':visible')) {
                    $('.navbar-collapse').slideUp(300);
                }
            });

            // Prevent dropdown from closing when clicking inside
            $('.dropdown-menu').click(function(e) {
                e.stopPropagation();
            });

            // Initialize tooltips
            if (typeof $.fn.tooltip !== 'undefined') {
                $('[data-bs-toggle="tooltip"]').tooltip();
            }

            // Initialize popovers
            if (typeof $.fn.popover !== 'undefined') {
                $('[data-bs-toggle="popover"]').popover();
            }
        });

        // Cookie consent banner
        function checkCookieConsent() {
            if (!localStorage.getItem('cookieConsent')) {
                $('#cookie-consent').removeClass('d-none');
            }
        }

        function acceptCookies() {
            localStorage.setItem('cookieConsent', 'accepted');
            $('#cookie-consent').fadeOut();
        }

        // Check cookie consent on page load
        $(window).on('load', function() {
            checkCookieConsent();
        });

        // Form validation example
        function validateForm(formId) {
            const form = document.getElementById(formId);
            if (!form) return false;

            let isValid = true;
            const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');

            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('is-invalid');
                    isValid = false;
                } else {
                    input.classList.remove('is-invalid');
                }
            });

            return isValid;
        }

        // Example of form submission handler
        $('form.needs-validation').on('submit', function(e) {
            e.preventDefault();
            if (validateForm(this.id)) {
                // Form is valid, proceed with submission
                this.submit();
            }
        });

        // Remove validation classes when user starts typing
        $('input, textarea, select').on('input change', function() {
            if (this.value.trim()) {
                $(this).removeClass('is-invalid');
            }
        });

        // Lazy loading for images
        if ('loading' in HTMLImageElement.prototype) {
            const images = document.querySelectorAll('img[loading="lazy"]');
            images.forEach(img => {
                img.src = img.dataset.src;
            });
        } else {
            // Fallback for browsers that don't support lazy loading
            const script = document.createElement('script');
            script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
            script.async = true;
            document.body.appendChild(script);
        }

        // Handle video modal
        $('.video-btn').click(function() {
            const videoSrc = $(this).data('src');
            $('#videoModal iframe').attr('src', videoSrc + '?autoplay=1');
        });

        $('#videoModal').on('hidden.bs.modal', function() {
            $('#videoModal iframe').attr('src', '');
        });

        // CountUp animation for stats
        function animateStats() {
            $('.counter').each(function() {
                $(this).prop('Counter', 0).animate({
                    Counter: $(this).text()
                }, {
                    duration: 2000,
                    easing: 'swing',
                    step: function(now) {
                        $(this).text(Math.ceil(now));
                    }
                });
            });
        }

        // Initialize stats animation when scrolled to
        $(window).scroll(function() {
            if ($('.counter').length && $(window).scrollTop() > $('.counter').offset().top - 500) {
                animateStats();
                $(window).off('scroll'); // Remove the handler after first trigger
            }
        });

        // Initialize light/dark mode toggle
        function initTheme() {
            const themeToggle = $('#theme-toggle');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const storedTheme = localStorage.getItem('theme');

            // Set initial theme
            let theme = storedTheme || (prefersDark ? 'dark' : 'light');
            setTheme(theme);

            // Toggle theme on button click
            themeToggle.click(function() {
                theme = theme === 'dark' ? 'light' : 'dark';
                setTheme(theme);
                localStorage.setItem('theme', theme);
            });

            function setTheme(theme) {
                document.documentElement.setAttribute('data-bs-theme', theme);
                themeToggle.find('i').toggleClass('fa-sun fa-moon');
            }
        }

        // Initialize theme toggle if element exists
        if ($('#theme-toggle').length) {
            initTheme();
        }
    </script>
</body>

</html>
