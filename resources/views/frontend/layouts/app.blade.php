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
        /**
         * GenBI Chatbot - Main JavaScript Module
         * Refactored and organized for better maintainability
         */

        // Main Chatbot Class
        class GenBIChatbot {
            constructor() {
                // Configuration
                this.config = {
                    maxRetries: 3,
                    retryDelay: 1000,
                    maxMessageLength: 500,
                    typingDelay: 800,
                    suggestionLimit: 4,
                    sessionTimeout: 30 * 60 * 1000, // 30 minutes
                    autoOpenDelay: 30000, // 30 seconds
                    refreshSuggestionsDelay: 30000 // 30 seconds
                };

                // State management
                this.state = {
                    isTyping: false,
                    chatInitialized: false,
                    isVisible: false,
                    conversationHistory: [],
                    lastDetectedIntent: null,
                    retryCount: 0,
                    sessionId: this.generateSessionId(),
                    currentSuggestions: []
                };

                // DOM elements cache
                this.elements = {};

                // Event handlers
                this.handlers = {};

                // Timers
                this.timers = {
                    autoOpen: null,
                    suggestionRefresh: null,
                    sessionTimeout: null
                };

                // Initialize
                this.init();
            }

            /**
             * Initialize the chatbot
             */
            init() {
                this.setupCSRF();
                this.cacheElements();
                this.bindEvents();
                this.setupAutoFeatures();
                this.initializeSessionTimeout();
            }

            /**
             * Generate unique session ID
             */
            generateSessionId() {
                return 'genbi-' + Math.random().toString(36).substring(2, 15) + Date.now().toString(36);
            }

            /**
             * Setup CSRF token for AJAX requests
             */
            setupCSRF() {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
            }

            /**
             * Cache DOM elements for better performance
             */
            cacheElements() {
                this.elements = {
                    chatFloat: $('#chat-float'),
                    chatWindow: $('#chat-window'),
                    chatMessages: $('#chat-messages'),
                    chatInput: $('#chat-input'),
                    sendBtn: $('#send-btn'),
                    inputContainer: $('#chat-input-container'),
                    welcomeMessage: $('.welcome-message'),
                    loadingSpinner: $('#loading-spinner')
                };
            }

            /**
             * Bind all event handlers
             */
            bindEvents() {
                // Chat toggle
                this.elements.chatFloat.on('click', () => this.toggleChat());
                $('.chat-close').on('click', () => this.toggleChat());

                // Message sending
                this.elements.sendBtn.on('click', () => this.sendMessage());
                this.elements.chatInput.on('keypress', (e) => {
                    if (e.which === 13 && !e.shiftKey) {
                        e.preventDefault();
                        this.sendMessage();
                    }
                });

                // Input focus management
                $(document).on('keydown', (e) => {
                    if (this.state.isVisible &&
                        !['input', 'textarea'].includes(e.target.tagName.toLowerCase())) {
                        this.elements.chatInput.focus();
                    }
                });

                // Quick reply handling
                $(document).on('click', '.quick-reply', (e) => {
                    this.handleQuickReply(e.target.textContent.trim());
                });

                // Window focus/blur for session management
                $(window).on('focus', () => this.resetSessionTimeout());
                $(window).on('blur', () => this.startSessionTimeout());
            }

            /**
             * Setup automatic features
             */
            setupAutoFeatures() {
                // Auto-open chat after delay
                this.timers.autoOpen = setTimeout(() => {
                    if (!this.state.chatInitialized && !this.state.isVisible) {
                        this.toggleChat();
                    }
                }, this.config.autoOpenDelay);

                // Hide loading spinner
                this.elements.loadingSpinner.fadeOut();
            }

            /**
             * Toggle chat window visibility
             */
            toggleChat() {
                const isCurrentlyVisible = this.elements.chatWindow.is(':visible');

                if (isCurrentlyVisible) {
                    this.hideChat();
                } else {
                    this.showChat();
                }
            }

            /**
             * Show chat window
             */
            showChat() {
                this.state.isVisible = true;
                this.elements.chatWindow.addClass('show').fadeIn(300);
                this.elements.chatInput.focus();

                // Clear auto-open timer
                if (this.timers.autoOpen) {
                    clearTimeout(this.timers.autoOpen);
                    this.timers.autoOpen = null;
                }

                // Show welcome message if first time
                if (!this.state.chatInitialized) {
                    setTimeout(() => {
                        this.showWelcomeMessage();
                        this.state.chatInitialized = true;
                    }, 1500);
                }

                this.resetSessionTimeout();
            }

            /**
             * Hide chat window
             */
            hideChat() {
                this.state.isVisible = false;
                this.elements.chatWindow.removeClass('show').fadeOut(300);
            }

            /**
             * Send message to server
             */
            async sendMessage() {
                const message = this.elements.chatInput.val().trim();

                if (!this.validateMessage(message)) {
                    return;
                }

                // Prepare UI for sending
                this.prepareMessageSend(message);

                try {
                    const response = await this.sendToServer(message);
                    this.handleSuccessResponse(response);
                } catch (error) {
                    this.handleErrorResponse(error);
                } finally {
                    this.finalizeSend();
                }
            }

            /**
             * Validate message before sending
             */
            validateMessage(message) {
                if (!message || this.state.isTyping) {
                    return false;
                }

                if (message.length > this.config.maxMessageLength) {
                    this.showToast('Pesan terlalu panjang. Maksimal ' + this.config.maxMessageLength + ' karakter.',
                        'warning');
                    return false;
                }

                return true;
            }

            /**
             * Prepare UI for message sending
             */
            prepareMessageSend(message) {
                this.elements.chatInput.val('');
                this.elements.sendBtn.prop('disabled', true);
                this.appendMessage(message, 'user');
                this.showTypingIndicator();
                this.state.retryCount = 0;
            }

            /**
             * Send message to server with retry logic
             */
            async sendToServer(message, retryCount = 0) {
                const requestData = {
                    message: message,
                    session_id: this.state.sessionId,
                    conversation_history: this.state.conversationHistory.slice(-5) // Last 5 intents
                };

                try {
                    const response = await $.ajax({
                        url: window.routes?.chatbotSendMessage || '/chatbot/send-message',
                        type: 'POST',
                        data: requestData,
                        timeout: 15000
                    });

                    return response;
                } catch (error) {
                    if (retryCount < this.config.maxRetries && this.shouldRetry(error)) {
                        await this.delay(this.config.retryDelay * (retryCount + 1));
                        return this.sendToServer(message, retryCount + 1);
                    }
                    throw error;
                }
            }

            /**
             * Handle successful server response
             */
            handleSuccessResponse(response) {
                this.hideTypingIndicator();

                if (response?.message) {
                    this.appendMessage(response.message, 'bot');
                    this.updateConversationState(response);

                    // Show suggestions after delay
                    setTimeout(() => {
                        if (response.suggestions?.length) {
                            this.showSuggestions(response.suggestions, response.detected_intent);
                        } else {
                            this.showFallbackSuggestions(response.detected_intent);
                        }
                    }, this.config.typingDelay);

                    // Analytics tracking
                    this.trackInteraction(response);
                } else {
                    this.appendMessage("Maaf, saya tidak dapat memproses pesan Anda saat ini.", 'bot');
                    this.showErrorSuggestions();
                }
            }

            /**
             * Handle error response
             */
            handleErrorResponse(error) {
                this.hideTypingIndicator();
                console.error('Chat Error:', error);

                const errorMessage = this.getErrorMessage(error);
                this.appendMessage(errorMessage, 'bot');

                setTimeout(() => {
                    this.showRecoverySuggestions();
                }, 500);
            }

            /**
             * Finalize message sending
             */
            finalizeSend() {
                this.elements.sendBtn.prop('disabled', false);
                this.elements.chatInput.focus();
                this.resetSessionTimeout();
            }

            /**
             * Update conversation state after response
             */
            updateConversationState(response) {
                if (response.detected_intent) {
                    this.state.conversationHistory.push(response.detected_intent);
                    this.state.lastDetectedIntent = response.detected_intent;

                    // Keep only recent history
                    if (this.state.conversationHistory.length > 10) {
                        this.state.conversationHistory = this.state.conversationHistory.slice(-5);
                    }
                }
            }

            /**
             * Show welcome message
             */
            showWelcomeMessage() {
                this.elements.welcomeMessage.fadeOut(300, () => {
                    this.appendMessage(
                        "Halo! Saya GenBI Assistant. Saya dapat membantu Anda dengan informasi tentang GenBI dan beasiswa Bank Indonesia. Apa yang ingin Anda ketahui?",
                        'bot',
                        true
                    );

                    setTimeout(() => {
                        this.showSuggestions([
                            "Apa itu GenBI?",
                            "Info beasiswa BI",
                            "Cara mendaftar GenBI",
                            "Program unggulan"
                        ], 'welcome');
                    }, 1200);
                });
            }

            /**
             * Append message to chat
             */
            appendMessage(text, sender, isWelcome = false) {
                const timestamp = new Date().toLocaleTimeString('id-ID', {
                    hour: '2-digit',
                    minute: '2-digit'
                });

                let messageHTML = '';

                if (sender === 'user') {
                    messageHTML = this.createUserMessageHTML(text, timestamp);
                } else {
                    messageHTML = this.createBotMessageHTML(text, timestamp);
                }

                this.elements.chatMessages.append(messageHTML);
                this.scrollToBottom();
            }

            /**
             * Create user message HTML
             */
            createUserMessageHTML(text, timestamp) {
                return `
            <div class="msg-row msg-user">
                <div class="msg-bubble user">
                    ${this.escapeHtml(text)}
                    <div class="msg-timestamp">${timestamp}</div>
                </div>
                <div class="chat-avatar user">U</div>
            </div>
        `;
            }

            /**
             * Create bot message HTML
             */
            createBotMessageHTML(text, timestamp) {
                const logoPath = window.assetPaths?.logo || '/assets2/images/logo.png';
                return `
            <div class="msg-row msg-bot">
                <img src="${logoPath}" alt="GenBI" class="chat-avatar bot">
                <div class="msg-bubble bot">
                    ${text}
                    <div class="msg-timestamp">${timestamp}</div>
                </div>
            </div>
        `;
            }

            /**
             * Show typing indicator
             */
            showTypingIndicator() {
                if (this.state.isTyping) return;

                this.state.isTyping = true;
                const logoPath = window.assetPaths?.logo || '/assets2/images/logo.png';

                const typingHTML = `
            <div class="msg-row msg-bot" id="typing-indicator">
                <img src="${logoPath}" alt="GenBI" class="chat-avatar bot">
                <div class="msg-bubble bot typing-indicator">
                    <div class="typing-dots">
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                        <div class="typing-dot"></div>
                    </div>
                </div>
            </div>
        `;

                this.elements.chatMessages.append(typingHTML);
                this.scrollToBottom();
            }

            /**
             * Hide typing indicator
             */
            hideTypingIndicator() {
                this.state.isTyping = false;
                $('#typing-indicator').remove();
            }

            /**
             * Show suggestions
             */
            showSuggestions(suggestions, intent = '') {
                if (!suggestions?.length) return;

                this.state.currentSuggestions = suggestions.slice(0, this.config.suggestionLimit);

                const quickRepliesHTML = `
            <div class="quick-replies dynamic-suggestions" data-intent="${intent}">
                ${this.state.currentSuggestions.map((suggestion, index) => 
                    `<button class="quick-reply" 
                                        data-suggestion="${this.escapeHtml(suggestion)}"
                                        title="Tanya tentang: ${this.escapeHtml(suggestion)}"
                                        style="animation-delay: ${index * 0.1}s">
                                        ${suggestion}
                                    </button>`
                ).join('')}
            </div>
        `;

                this.elements.chatMessages.find('.msg-row:last-child .msg-bubble').append(quickRepliesHTML);
                this.scrollToBottom();
                this.startSuggestionRefreshTimer();
            }

            /**
             * Show fallback suggestions
             */
            showFallbackSuggestions(intent) {
                const fallbackSuggestions = this.getFallbackSuggestions(intent);
                this.showSuggestions(fallbackSuggestions, 'fallback');
            }

            /**
             * Show error recovery suggestions
             */
            showRecoverySuggestions() {
                const suggestions = ['Coba lagi', 'Hubungi admin', 'FAQ umum', 'Bantuan teknis'];
                this.showSuggestions(suggestions, 'error_recovery');
            }

            /**
             * Show error-specific suggestions
             */
            showErrorSuggestions() {
                const suggestions = ['Ulangi pertanyaan', 'Bantuan umum', 'Kontak support', 'Menu utama'];
                this.showSuggestions(suggestions, 'error');
            }

            /**
             * Get fallback suggestions based on intent
             */
            getFallbackSuggestions(intent) {
                const fallbackMap = {
                    'beasiswa': ['Syarat beasiswa BI', 'Cara mendaftar', 'Timeline pendaftaran',
                        'Tips lolos seleksi'
                    ],
                    'genbi': ['Apa itu GenBI', 'Program GenBI', 'Cara bergabung', 'Kegiatan GenBI'],
                    'daftar': ['Syarat pendaftaran', 'Link aplikasi', 'Dokumen dibutuhkan', 'Bantuan pendaftaran'],
                    'program': ['Program sosial', 'Program edukasi', 'Event terbaru', 'Cara ikut program']
                };

                // Find matching intent
                for (const [key, suggestions] of Object.entries(fallbackMap)) {
                    if (intent?.toLowerCase().includes(key)) {
                        return suggestions;
                    }
                }

                return ['Info GenBI', 'Info Beasiswa BI', 'FAQ umum', 'Hubungi admin'];
            }

            /**
             * Handle quick reply click
             */
            handleQuickReply(text) {
                // Visual feedback
                const clickedButton = event.target;
                $(clickedButton).addClass('clicked');

                // Track click
                this.trackSuggestionClick(text);

                // Set input and send
                this.elements.chatInput.val(text);
                this.sendMessage();

                // Remove suggestions with animation
                this.removeSuggestions();
            }

            /**
             * Remove suggestions with animation
             */
            removeSuggestions() {
                const $suggestions = $('.quick-replies');
                $suggestions.addClass('fade-out');

                $('.quick-reply').each((index, element) => {
                    $(element).delay(index * 50).fadeOut(200);
                });

                setTimeout(() => {
                    $suggestions.remove();
                }, 500);
            }

            /**
             * Session timeout management
             */
            initializeSessionTimeout() {
                this.resetSessionTimeout();
            }

            resetSessionTimeout() {
                if (this.timers.sessionTimeout) {
                    clearTimeout(this.timers.sessionTimeout);
                }

                this.timers.sessionTimeout = setTimeout(() => {
                    this.handleSessionTimeout();
                }, this.config.sessionTimeout);
            }

            startSessionTimeout() {
                // Implement if needed for different timeout behavior when window is not focused
            }

            handleSessionTimeout() {
                // Clear conversation history on timeout
                this.state.conversationHistory = [];
                this.state.lastDetectedIntent = null;

                if (this.state.isVisible) {
                    this.appendMessage("Sesi chat telah berakhir karena tidak aktif. Silakan mulai percakapan baru.",
                        'bot');
                }
            }

            /**
             * Suggestion refresh timer
             */
            startSuggestionRefreshTimer() {
                if (this.timers.suggestionRefresh) {
                    clearTimeout(this.timers.suggestionRefresh);
                }

                this.timers.suggestionRefresh = setTimeout(() => {
                    if ($('.quick-replies').length > 0 && !this.state.isTyping) {
                        this.refreshSuggestions();
                    }
                }, this.config.refreshSuggestionsDelay);
            }

            /**
             * Refresh suggestions
             */
            async refreshSuggestions() {
                if (!this.state.lastDetectedIntent) return;

                try {
                    const response = await $.ajax({
                        url: '/api/chatbot/get-fresh-suggestions',
                        type: 'POST',
                        data: {
                            intent: this.state.lastDetectedIntent,
                            conversation_history: this.state.conversationHistory,
                            session_id: this.state.sessionId
                        }
                    });

                    if (response.suggestions) {
                        $('.quick-replies').fadeOut(200, () => {
                            $('.quick-replies').remove();
                            this.showSuggestions(response.suggestions, this.state.lastDetectedIntent);
                        });
                    }
                } catch (error) {
                    console.log('Failed to refresh suggestions:', error);
                }
            }

            /**
             * Analytics and tracking
             */
            trackInteraction(response) {
                const logData = {
                    timestamp: new Date().toISOString(),
                    user_message: response.user_message || '',
                    detected_intent: response.detected_intent,
                    suggestions: response.suggestions,
                    session_id: this.state.sessionId,
                    conversation_length: this.state.conversationHistory.length
                };

                this.saveToLocalStorage('chatbot_conversation_log', logData);
            }

            trackSuggestionClick(suggestionText) {
                $.ajax({
                    url: '/api/chatbot/track-interaction',
                    type: 'POST',
                    data: {
                        suggestion: suggestionText,
                        current_intent: this.state.lastDetectedIntent,
                        session_id: this.state.sessionId,
                        timestamp: new Date().toISOString()
                    },
                    success: (response) => {
                        console.log('Interaction tracked:', response);
                    },
                    error: (error) => {
                        console.log('Analytics tracking failed:', error);
                    }
                });
            }

            /**
             * Utility functions
             */
            escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, (m) => map[m]);
            }

            scrollToBottom() {
                this.elements.chatMessages.animate({
                    scrollTop: this.elements.chatMessages[0].scrollHeight
                }, 300);
            }

            delay(ms) {
                return new Promise(resolve => setTimeout(resolve, ms));
            }

            shouldRetry(error) {
                const retryableStatuses = [0, 408, 429, 500, 502, 503, 504];
                return retryableStatuses.includes(error.status) || error.statusText === 'timeout';
            }

            getErrorMessage(error) {
                const errorMessages = {
                    'timeout': "Koneksi timeout. Jaringan Anda mungkin lambat, silakan coba lagi.",
                    429: "Terlalu banyak pesan dalam waktu singkat. Mohon tunggu sebentar sebelum mengirim pesan lagi.",
                    500: "Terjadi kesalahan pada server. Tim teknis kami sedang memperbaikinya.",
                    503: "Layanan sedang dalam pemeliharaan. Silakan coba beberapa saat lagi."
                };

                return errorMessages[error.statusText] ||
                    errorMessages[error.status] ||
                    "Terjadi kesalahan koneksi. Periksa koneksi internet Anda dan coba lagi.";
            }

            saveToLocalStorage(key, data) {
                try {
                    const existingData = JSON.parse(localStorage.getItem(key) || '[]');
                    existingData.push(data);

                    // Keep only last 50 items
                    if (existingData.length > 50) {
                        existingData.shift();
                    }

                    localStorage.setItem(key, JSON.stringify(existingData));
                } catch (error) {
                    console.warn('Failed to save to localStorage:', error);
                }
            }

            showToast(message, type = 'info') {
                // Simple toast implementation
                const toast = $(`
            <div class="chatbot-toast chatbot-toast-${type}">
                ${message}
            </div>
        `).appendTo('body');

                setTimeout(() => {
                    toast.fadeOut(() => toast.remove());
                }, 3000);
            }

            /**
             * Cleanup method
             */
            destroy() {
                // Clear all timers
                Object.values(this.timers).forEach(timer => {
                    if (timer) clearTimeout(timer);
                });

                // Remove event listeners
                this.elements.chatFloat.off();
                this.elements.sendBtn.off();
                this.elements.chatInput.off();
                $(document).off('click', '.quick-reply');
                $(document).off('keydown');
                $(window).off('focus blur');

                // Clear references
                this.elements = {};
                this.state = {};
                this.timers = {};
            }
        }

        // Additional Utility Classes and Functions

        /**
         * Page initialization and other features
         */
        class PageManager {
            constructor() {
                this.init();
            }

            init() {
                this.initializeLibraries();
                this.setupUIFeatures();
                this.setupFormValidation();
                this.setupImageHandling();
                this.setupScrollFeatures();
                this.setupThemeToggle();
            }

            initializeLibraries() {
                // Initialize AOS
                if (typeof AOS !== 'undefined') {
                    AOS.init({
                        duration: 1000,
                        once: true,
                        offset: 100
                    });
                }

                // Initialize WOW
                if (typeof WOW !== 'undefined') {
                    new WOW().init();
                }

                // Initialize Typed.js
                this.initializeTypedJS();

                // Initialize Magnific Popup
                this.initializeMagnificPopup();
            }

            initializeTypedJS() {
                const typedElements = $('.typed-text-output');
                if (typedElements.length && typeof Typed !== 'undefined') {
                    typedElements.each(function() {
                        const typedStrings = $(this).siblings('.typed-text').text();
                        if (typedStrings) {
                            new Typed(this, {
                                strings: typedStrings.split(','),
                                typeSpeed: 100,
                                backSpeed: 20,
                                smartBackspace: false,
                                loop: true
                            });
                        }
                    });
                }
            }

            initializeMagnificPopup() {
                if (typeof $.fn.magnificPopup !== 'undefined') {
                    $('.popup-image').magnificPopup({
                        type: 'image',
                        closeOnContentClick: true,
                        mainClass: 'mfp-img-mobile',
                        image: {
                            verticalFit: true
                        }
                    });

                    $('.popup-video').magnificPopup({
                        type: 'iframe',
                        mainClass: 'mfp-fade',
                        removalDelay: 160,
                        preloader: false,
                        fixedContentPos: false
                    });
                }
            }

            setupUIFeatures() {
                // Smooth scrolling
                this.setupSmoothScrolling();

                // Back to top button
                this.setupBackToTop();

                // Mobile navigation
                this.setupMobileNavigation();

                // Bootstrap components
                this.initializeBootstrapComponents();
            }

            setupSmoothScrolling() {
                $('a[href*="#"]').not('[href="#"]').not('[href="#0"]').on('click', function(event) {
                    if (location.pathname.replace(/^\//, '') === this.pathname.replace(/^\//, '') &&
                        location.hostname === this.hostname) {

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
            }

            setupBackToTop() {
                const backToTopButton = $('<div class="back-to-top"><i class="lni lni-chevron-up"></i></div>');
                $('body').append(backToTopButton);

                $(window).on('scroll', function() {
                    if ($(this).scrollTop() > 300) {
                        backToTopButton.fadeIn('slow');
                    } else {
                        backToTopButton.fadeOut('slow');
                    }
                });

                backToTopButton.on('click', function() {
                    $('html, body').animate({
                        scrollTop: 0
                    }, 1500, 'easeInOutExpo');
                    return false;
                });
            }

            setupMobileNavigation() {
                $('.navbar-toggler').on('click', function() {
                    $('.navbar-collapse').slideToggle(300);
                });

                $('.navbar-nav a').on('click', function() {
                    if ($('.navbar-toggler').is(':visible')) {
                        $('.navbar-collapse').slideUp(300);
                    }
                });

                $('.dropdown-menu').on('click', function(e) {
                    e.stopPropagation();
                });
            }

            initializeBootstrapComponents() {
                // Initialize tooltips
                if (typeof $.fn.tooltip !== 'undefined') {
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }

                // Initialize popovers
                if (typeof $.fn.popover !== 'undefined') {
                    $('[data-bs-toggle="popover"]').popover();
                }

                // Video modal handling
                $('.video-btn').on('click', function() {
                    const videoSrc = $(this).data('src');
                    $('#videoModal iframe').attr('src', videoSrc + '?autoplay=1');
                });

                $('#videoModal').on('hidden.bs.modal', function() {
                    $('#videoModal iframe').attr('src', '');
                });
            }

            setupFormValidation() {
                // Form validation
                $('form.needs-validation').on('submit', function(e) {
                    e.preventDefault();
                    if (this.validateForm(this.id)) {
                        this.submit();
                    }
                });

                // Remove validation classes on input
                $('input, textarea, select').on('input change', function() {
                    if (this.value.trim()) {
                        $(this).removeClass('is-invalid');
                    }
                });
            }

            validateForm(formId) {
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

            setupImageHandling() {
                // Lazy loading fallback
                if (!('loading' in HTMLImageElement.prototype)) {
                    const script = document.createElement('script');
                    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/lazysizes/5.3.2/lazysizes.min.js';
                    script.async = true;
                    document.body.appendChild(script);
                } else {
                    const images = document.querySelectorAll('img[loading="lazy"]');
                    images.forEach(img => {
                        if (img.dataset.src) {
                            img.src = img.dataset.src;
                        }
                    });
                }
            }

            setupScrollFeatures() {
                // Counter animation
                let counterAnimated = false;
                $(window).on('scroll', function() {
                    const counterElements = $('.counter');
                    if (counterElements.length && !counterAnimated) {
                        const counterOffset = counterElements.first().offset();
                        if (counterOffset && $(window).scrollTop() > counterOffset.top - 500) {
                            counterAnimated = true;
                            counterElements.each(function() {
                                const $this = $(this);
                                const targetValue = parseInt($this.text());

                                $this.prop('Counter', 0).animate({
                                    Counter: targetValue
                                }, {
                                    duration: 2000,
                                    easing: 'swing',
                                    step: function(now) {
                                        $this.text(Math.ceil(now));
                                    }
                                });
                            });
                        }
                    }
                });
            }

            setupThemeToggle() {
                const themeToggle = $('#theme-toggle');

                if (themeToggle.length) {
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const storedTheme = localStorage.getItem('theme');
                    let currentTheme = storedTheme || (prefersDark ? 'dark' : 'light');

                    this.setTheme(currentTheme);

                    themeToggle.on('click', () => {
                        currentTheme = currentTheme === 'dark' ? 'light' : 'dark';
                        this.setTheme(currentTheme);
                        localStorage.setItem('theme', currentTheme);
                    });
                }
            }

            setTheme(theme) {
                document.documentElement.setAttribute('data-bs-theme', theme);
                const themeToggle = $('#theme-toggle');
                if (themeToggle.length) {
                    themeToggle.find('i').toggleClass('fa-sun fa-moon', theme === 'dark');
                }
            }
        }

        /**
         * Cookie Consent Manager
         */
        class CookieManager {
            constructor() {
                this.init();
            }

            init() {
                this.checkCookieConsent();
                this.setupCookieHandlers();
            }

            checkCookieConsent() {
                if (!localStorage.getItem('cookieConsent')) {
                    this.showCookieBanner();
                }
            }

            showCookieBanner() {
                const banner = $('#cookie-consent');
                if (banner.length) {
                    banner.removeClass('d-none').fadeIn();
                }
            }

            setupCookieHandlers() {
                $(document).on('click', '#accept-cookies', () => {
                    this.acceptCookies();
                });

                $(document).on('click', '#decline-cookies', () => {
                    this.declineCookies();
                });
            }

            acceptCookies() {
                localStorage.setItem('cookieConsent', 'accepted');
                $('#cookie-consent').fadeOut();
                this.enableAnalytics();
            }

            declineCookies() {
                localStorage.setItem('cookieConsent', 'declined');
                $('#cookie-consent').fadeOut();
            }

            enableAnalytics() {
                // Enable Google Analytics or other tracking scripts
                if (typeof gtag !== 'undefined') {
                    gtag('consent', 'update', {
                        'analytics_storage': 'granted'
                    });
                }
            }
        }

        /**
         * Performance Monitor
         */
        class PerformanceMonitor {
            constructor() {
                this.metrics = {};
                this.init();
            }

            init() {
                this.measurePageLoad();
                this.setupErrorTracking();
                this.monitorChatPerformance();
            }

            measurePageLoad() {
                window.addEventListener('load', () => {
                    if ('performance' in window) {
                        const navigation = performance.getEntriesByType('navigation')[0];
                        this.metrics.pageLoad = {
                            domContentLoaded: navigation.domContentLoadedEventEnd - navigation
                                .domContentLoadedEventStart,
                            loadComplete: navigation.loadEventEnd - navigation.loadEventStart,
                            totalTime: navigation.loadEventEnd - navigation.fetchStart
                        };

                        this.logMetrics('pageLoad', this.metrics.pageLoad);
                    }
                });
            }

            setupErrorTracking() {
                window.addEventListener('error', (e) => {
                    this.logError('JavaScript Error', {
                        message: e.message,
                        filename: e.filename,
                        lineno: e.lineno,
                        colno: e.colno,
                        stack: e.error?.stack
                    });
                });

                window.addEventListener('unhandledrejection', (e) => {
                    this.logError('Unhandled Promise Rejection', {
                        reason: e.reason,
                        promise: e.promise
                    });
                });
            }

            monitorChatPerformance() {
                // Monitor chat response times
                const originalSendMessage = GenBIChatbot.prototype.sendToServer;
                GenBIChatbot.prototype.sendToServer = async function(...args) {
                    const startTime = performance.now();
                    try {
                        const result = await originalSendMessage.apply(this, args);
                        const endTime = performance.now();

                        window.performanceMonitor?.logMetrics('chatResponse', {
                            responseTime: endTime - startTime,
                            success: true
                        });

                        return result;
                    } catch (error) {
                        const endTime = performance.now();

                        window.performanceMonitor?.logMetrics('chatResponse', {
                            responseTime: endTime - startTime,
                            success: false,
                            error: error.message
                        });

                        throw error;
                    }
                };
            }

            logMetrics(type, data) {
                if (window.location.search.includes('debug=1')) {
                    console.log(`Performance [${type}]:`, data);
                }

                // Send to analytics service if available
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'performance_metric', {
                        'metric_type': type,
                        'metric_data': JSON.stringify(data)
                    });
                }
            }

            logError(type, error) {
                console.error(`${type}:`, error);

                // Send to error tracking service
                if (typeof gtag !== 'undefined') {
                    gtag('event', 'exception', {
                        'description': `${type}: ${error.message || error.reason}`,
                        'fatal': false
                    });
                }
            }
        }

        /**
         * Accessibility Enhancer
         */
        class AccessibilityEnhancer {
            constructor() {
                this.init();
            }

            init() {
                this.setupKeyboardNavigation();
                this.setupScreenReaderSupport();
                this.setupFocusManagement();
                this.setupHighContrast();
            }

            setupKeyboardNavigation() {
                // Escape key to close chat
                $(document).on('keydown', (e) => {
                    if (e.key === 'Escape' && window.genbiChatbot?.state.isVisible) {
                        window.genbiChatbot.hideChat();
                    }
                });

                // Tab navigation for chat elements
                $('.chat-window').on('keydown', (e) => {
                    if (e.key === 'Tab') {
                        this.handleTabNavigation(e);
                    }
                });
            }

            handleTabNavigation(e) {
                const focusableElements = $(e.currentTarget).find(
                    'button, input, [tabindex]:not([tabindex="-1"])'
                );

                const firstElement = focusableElements.first();
                const lastElement = focusableElements.last();

                if (e.shiftKey && $(document.activeElement).is(firstElement)) {
                    e.preventDefault();
                    lastElement.focus();
                } else if (!e.shiftKey && $(document.activeElement).is(lastElement)) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }

            setupScreenReaderSupport() {
                // Add ARIA labels and roles
                $('#chat-float').attr({
                    'role': 'button',
                    'aria-label': 'Buka chat dengan GenBI Assistant',
                    'aria-expanded': 'false'
                });

                $('#chat-messages').attr({
                    'role': 'log',
                    'aria-live': 'polite',
                    'aria-label': 'Riwayat percakapan chat'
                });

                $('#chat-input').attr({
                    'role': 'textbox',
                    'aria-label': 'Ketik pesan Anda di sini'
                });

                // Announce new messages to screen readers
                this.setupMessageAnnouncements();
            }

            setupMessageAnnouncements() {
                const observer = new MutationObserver((mutations) => {
                    mutations.forEach((mutation) => {
                        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                            mutation.addedNodes.forEach((node) => {
                                if ($(node).hasClass('msg-row') && $(node).hasClass(
                                        'msg-bot')) {
                                    const messageText = $(node).find('.msg-bubble').text();
                                    this.announceToScreenReader(`Bot berkata: ${messageText}`);
                                }
                            });
                        }
                    });
                });

                const chatMessages = document.getElementById('chat-messages');
                if (chatMessages) {
                    observer.observe(chatMessages, {
                        childList: true
                    });
                }
            }

            announceToScreenReader(message) {
                const announcement = $('<div>')
                    .addClass('sr-only')
                    .attr('aria-live', 'assertive')
                    .text(message);

                $('body').append(announcement);
                setTimeout(() => announcement.remove(), 1000);
            }

            setupFocusManagement() {
                // Focus management for modal-like chat window
                $(document).on('chatOpened', () => {
                    $('#chat-input').focus();
                    $('#chat-float').attr('aria-expanded', 'true');
                });

                $(document).on('chatClosed', () => {
                    $('#chat-float').focus().attr('aria-expanded', 'false');
                });
            }

            setupHighContrast() {
                // Detect high contrast preference
                if (window.matchMedia('(prefers-contrast: high)').matches) {
                    $('body').addClass('high-contrast');
                }

                // Listen for changes
                window.matchMedia('(prefers-contrast: high)').addEventListener('change', (e) => {
                    $('body').toggleClass('high-contrast', e.matches);
                });
            }
        }

        /**
         * SEO and Analytics Helper
         */
        class SEOAnalyticsHelper {
            constructor() {
                this.init();
            }

            init() {
                this.trackPageViews();
                this.trackUserInteractions();
                this.trackChatUsage();
                this.setupStructuredData();
            }

            trackPageViews() {
                if (typeof gtag !== 'undefined') {
                    gtag('config', 'GA_MEASUREMENT_ID', {
                        page_title: document.title,
                        page_location: window.location.href
                    });
                }
            }

            trackUserInteractions() {
                // Track important user interactions
                $('a[href^="mailto:"], a[href^="tel:"]').on('click', function() {
                    const linkType = this.href.startsWith('mailto:') ? 'email' : 'phone';
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'contact_interaction', {
                            'method': linkType,
                            'contact_info': this.href
                        });
                    }
                });

                // Track form submissions
                $('form').on('submit', function() {
                    const formId = this.id || 'unknown';
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'form_submit', {
                            'form_id': formId
                        });
                    }
                });
            }

            trackChatUsage() {
                // Track chat interactions
                $(document).on('chatOpened', () => {
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'chat_opened');
                    }
                });

                $(document).on('messagesSent', (e, data) => {
                    if (typeof gtag !== 'undefined') {
                        gtag('event', 'chat_message_sent', {
                            'session_id': data.sessionId,
                            'message_count': data.messageCount
                        });
                    }
                });
            }

            setupStructuredData() {
                // Add FAQ structured data if FAQ content exists
                const faqElements = $('.faq-item');
                if (faqElements.length > 0) {
                    const faqData = {
                        "@context": "https://schema.org",
                        "@type": "FAQPage",
                        "mainEntity": []
                    };

                    faqElements.each(function() {
                        const question = $(this).find('.faq-question').text().trim();
                        const answer = $(this).find('.faq-answer').text().trim();

                        if (question && answer) {
                            faqData.mainEntity.push({
                                "@type": "Question",
                                "name": question,
                                "acceptedAnswer": {
                                    "@type": "Answer",
                                    "text": answer
                                }
                            });
                        }
                    });

                    if (faqData.mainEntity.length > 0) {
                        const script = document.createElement('script');
                        script.type = 'application/ld+json';
                        script.textContent = JSON.stringify(faqData);
                        document.head.appendChild(script);
                    }
                }
            }
        }

        /**
         * Global initialization and cleanup
         */
        $(document).ready(function() {
            try {
                // Initialize all components
                window.genbiChatbot = new GenBIChatbot();
                window.pageManager = new PageManager();
                window.cookieManager = new CookieManager();
                window.performanceMonitor = new PerformanceMonitor();
                window.accessibilityEnhancer = new AccessibilityEnhancer();
                window.seoAnalyticsHelper = new SEOAnalyticsHelper();

                // Global event triggers for chatbot
                window.toggleChat = () => window.genbiChatbot?.toggleChat();
                window.sendChat = () => window.genbiChatbot?.sendMessage();
                window.handleQuickReply = (text) => window.genbiChatbot?.handleQuickReply(text);

                console.log('GenBI Chatbot initialized successfully');

            } catch (error) {
                console.error('Error initializing GenBI components:', error);

                // Fallback basic functionality
                $('#chat-float').on('click', function() {
                    alert('Chat sedang dalam perbaikan. Silakan hubungi admin melalui email atau telepon.');
                });
            }
        });

        // Cleanup on page unload
        $(window).on('beforeunload', function() {
            try {
                // Cleanup all components
                window.genbiChatbot?.destroy();
                window.performanceMonitor = null;
                window.pageManager = null;
                window.cookieManager = null;
                window.accessibilityEnhancer = null;
                window.seoAnalyticsHelper = null;

                // Clear any remaining timers
                const highestTimeoutId = setTimeout(() => {}, 0);
                for (let i = 0; i < highestTimeoutId; i++) {
                    clearTimeout(i);
                }

            } catch (error) {
                console.warn('Error during cleanup:', error);
            }
        });

        // Export for use in other scripts if needed
        if (typeof module !== 'undefined' && module.exports) {
            module.exports = {
                GenBIChatbot,
                PageManager,
                CookieManager,
                PerformanceMonitor,
                AccessibilityEnhancer,
                SEOAnalyticsHelper
            };
        }

        // Enhanced CSS styles to support the refactored JavaScript
        const enhancedStyles = `
<style>
/* Message timestamp styles */
.msg-timestamp {
    font-size: 11px;
    opacity: 0.7;
    margin-top: 4px;
}

/* Toast notification styles */
.chatbot-toast {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 20px;
    border-radius: 8px;
    color: white;
    font-weight: 500;
    z-index: 10000;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    animation: slideInDown 0.3s ease-out;
}

.chatbot-toast-info { background: var(--chatbot-info); }
.chatbot-toast-success { background: var(--chatbot-success); }
.chatbot-toast-warning { background: var(--chatbot-warning); color: #000; }
.chatbot-toast-error { background: var(--chatbot-danger); }

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Back to top button */
.back-to-top {
    position: fixed;
    bottom: 120px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: var(--chatbot-gradient);
    color: white;
    border-radius: 50%;
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.3s ease;
    z-index: 9997;
}

.back-to-top:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
}

/* High contrast mode */
.high-contrast {
    filter: contrast(150%);
}

.high-contrast .chat-header {
    background: #000 !important;
    color: #fff !important;
}

.high-contrast .msg-bubble.bot {
    background: #fff !important;
    color: #000 !important;
    border: 2px solid #000 !important;
}

.high-contrast .msg-bubble.user {
    background: #000 !important;
    color: #fff !important;
    border: 2px solid #fff !important;
}

/* Screen reader only content */
.sr-only {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    border: 0;
}

/* Cookie consent banner */
#cookie-consent {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 20px;
    z-index: 10000;
    text-align: center;
}

#cookie-consent button {
    margin: 0 5px;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

#accept-cookies {
    background: var(--chatbot-success);
    color: white;
}

#decline-cookies {
    background: var(--chatbot-secondary);
    color: white;
}

/* Enhanced quick reply animations */
.quick-reply {
    position: relative;
    overflow: hidden;
}

.quick-reply:focus {
    outline: 2px solid var(--chatbot-primary);
    outline-offset: 2px;
}

.quick-reply:active {
    transform: scale(0.98);
}

/* Loading states */
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.8);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
}

.loading-dots {
    display: flex;
    gap: 4px;
}

.loading-dot {
    width: 8px;
    height: 8px;
    background: var(--chatbot-primary);
    border-radius: 50%;
    animation: loading-bounce 1.4s ease-in-out infinite both;
}

.loading-dot:nth-child(1) { animation-delay: -0.32s; }
.loading-dot:nth-child(2) { animation-delay: -0.16s; }
.loading-dot:nth-child(3) { animation-delay: 0s; }

@keyframes loading-bounce {
    0%, 80%, 100% {
        transform: scale(0);
    }
    40% {
        transform: scale(1);
    }
}

/* Responsive improvements */
@media (max-width: 768px) {
    .back-to-top {
        bottom: 100px;
        right: 20px;
        width: 45px;
        height: 45px;
    }
    
    .chatbot-toast {
        right: 10px;
        left: 10px;
        text-align: center;
    }
}

/* Print styles */
@media print {
    #chat-float,
    #chat-window,
    .back-to-top,
    #cookie-consent {
        display: none !important;
    }
}
</style>
`;

        // Inject enhanced styles
        if (typeof document !== 'undefined') {
            document.head.insertAdjacentHTML('beforeend', enhancedStyles);
        }
    </script>
</body>

</html>
