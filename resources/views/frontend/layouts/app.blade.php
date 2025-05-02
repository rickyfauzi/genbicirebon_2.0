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
</head>

<body>

    <div class="loading-spinner" id="loading-spinner">
        <div class="spinner"></div>
    </div>

    <!-- Konten Halaman Web -->

    @include('frontend.template.header')

    <main>
        @yield('content')
        <div id="chat-launcher" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; cursor: pointer;">
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

   <script type="text/javascript">
(function(d, m){
    var kommunicateSettings = {
        "appId":"312005e39b4f0ee7c771510619e9c10e8",
        "popupWidget": true,
        "automaticChatOpenOnNavigation": false
    };
    var s = document.createElement("script"); s.type = "text/javascript"; s.async = true;
    s.src = "https://widget.kommunicate.io/v2/kommunicate.app";
    var h = document.getElementsByTagName("head")[0]; h.appendChild(s);
    window.kommunicate = m; m._globals = kommunicateSettings;
})(document, window.kommunicate || {});
</script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById('chat-launcher').onclick = function () {
        Kommunicate.launchConversation();
    };

    // Override displayMessage to intercept user message
    setTimeout(() => {
        const originalFn = Kommunicate.displayMessage;
        Kommunicate.displayMessage = function(msgObj) {
            if (msgObj && msgObj.source === 'user') {
                fetch("/api/webhook-dialogflow", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        queryResult: { queryText: msgObj.message }
                    })
                })
                .then(res => res.json())
                .then(data => {
                    Kommunicate.displayMessage({
                        message: data.fulfillmentText,
                        type: 'text',
                        contentType: 'text',
                        source: 'bot'
                    });
                });
            }
            originalFn.call(Kommunicate, msgObj);
        };
    }, 3000);
});
</script>

    <!-- Trigger Manual Popup -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const chatBtn = document.getElementById('chat-launcher');
            chatBtn.addEventListener('click', function() {
                if (window.Kommunicate) {
                    Kommunicate.launchConversation();
                } else {
                    alert("Widget belum siap, tunggu sebentar...");
                }
            });
        });
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
    </script>


</body>

</html>
