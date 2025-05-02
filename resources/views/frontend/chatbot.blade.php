<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Chatbot GenBi Cirebon</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Tempelkan CSS custom dari #chat1 di sini (yang sudah kamu punya) */
        #chat1 .form-outline .form-control~.form-notch div {
            pointer-events: none;
            border: 1px solid;
            border-color: #eee;
            box-sizing: border-box;
            background: transparent;
        }

        #chat1 .form-outline .form-control~.form-notch .form-notch-leading {
            left: 0;
            top: 0;
            height: 100%;
            border-right: none;
            border-radius: .65rem 0 0 .65rem;
        }

        #chat1 .form-outline .form-control~.form-notch .form-notch-middle {
            flex: 0 0 auto;
            max-width: calc(100% - 1rem);
            height: 100%;
            border-right: none;
            border-left: none;
        }

        #chat1 .form-outline .form-control~.form-notch .form-notch-trailing {
            flex-grow: 1;
            height: 100%;
            border-left: none;
            border-radius: 0 .65rem .65rem 0;
        }

        #chat1 .form-outline .form-control:focus~.form-notch .form-notch-leading {
            border-top: 0.125rem solid #39c0ed;
            border-bottom: 0.125rem solid #39c0ed;
            border-left: 0.125rem solid #39c0ed;
        }

        #chat1 .form-outline .form-control:focus~.form-notch .form-notch-leading,
        #chat1 .form-outline .form-control.active~.form-notch .form-notch-leading {
            border-right: none;
            transition: all 0.2s linear;
        }

        #chat1 .form-outline .form-control:focus~.form-notch .form-notch-middle {
            border-bottom: 0.125rem solid;
            border-color: #39c0ed;
        }

        #chat1 .form-outline .form-control:focus~.form-notch .form-notch-middle,
        #chat1 .form-outline .form-control.active~.form-notch .form-notch-middle {
            border-top: none;
            border-right: none;
            border-left: none;
            transition: all 0.2s linear;
        }

        #chat1 .form-outline .form-control:focus~.form-notch .form-notch-trailing {
            border-top: 0.125rem solid #39c0ed;
            border-bottom: 0.125rem solid #39c0ed;
            border-right: 0.125rem solid #39c0ed;
        }

        #chat1 .form-outline .form-control:focus~.form-notch .form-notch-trailing,
        #chat1 .form-outline .form-control.active~.form-notch .form-notch-trailing {
            border-left: none;
            transition: all 0.2s linear;
        }

        #chat1 .form-outline .form-control:focus~.form-label {
            color: #39c0ed;
        }

        #chat1 .form-outline .form-control~.form-label {
            color: #bfbfbf;
        }
    </style>
</head>

<body>

    <section>
        <div class="container py-5">
            <div class="row d-flex justify-content-center">
                <div class="col-md-8 col-lg-6 col-xl-4">
                    <div class="card" id="chat1" style="border-radius: 15px;">
                        <div class="card-header d-flex justify-content-between align-items-center p-3 bg-info text-white border-bottom-0"
                            style="border-top-left-radius: 15px; border-top-right-radius: 15px;">
                            <p class="mb-0 fw-bold">Live Chat GenBi Cirebon</p>
                        </div>
                        <div class="card-body" id="chatbox" style="overflow-y: auto; height: 400px;">

                            <!-- Chat bubble akan ditambahkan dinamis via JS -->

                        </div>

                        <div class="card-footer">
                            <div class="form-outline d-flex">
                                <textarea class="form-control bg-body-tertiary" id="textAreaExample" rows="1" placeholder="Ketik pesan..."></textarea>
                                <button class="btn btn-primary ms-2" onclick="sendMessage()">Kirim</button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function sendMessage() {
            const textarea = document.getElementById("textAreaExample");
            const message = textarea.value.trim();
            if (!message) return;

            appendMessage("user", message);
            textarea.value = "";

            fetch("/api/webhook-dialogflow", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        queryResult: {
                            queryText: message
                        }
                    })
                })
                .then(res => res.json())
                .then(data => {
                    appendMessage("bot", data.fulfillmentText);
                });
        }

        function appendMessage(role, text) {
            const box = document.getElementById("chatbox");
            const wrapper = document.createElement("div");
            wrapper.className = "d-flex flex-row mb-4 " + (role === "user" ? "justify-content-end" :
                "justify-content-start");

            const bubble = document.createElement("div");
            bubble.className = "p-3 " + (role === "user" ? "me-3 border bg-body-tertiary" : "ms-3");
            bubble.style.borderRadius = "15px";
            bubble.innerHTML = `<p class="small mb-0">${text}</p>`;

            const avatar = document.createElement("img");
            avatar.src = role === "user" ?
                "https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava2-bg.webp" :
                "https://mdbcdn.b-cdn.net/img/Photos/new-templates/bootstrap-chat/ava1-bg.webp";
            avatar.style.width = "45px";
            avatar.alt = "avatar";

            if (role === "user") {
                wrapper.appendChild(bubble);
                wrapper.appendChild(avatar);
            } else {
                wrapper.appendChild(avatar);
                wrapper.appendChild(bubble);
            }

            box.appendChild(wrapper);
            box.scrollTop = box.scrollHeight;
        }
    </script>

</body>

</html>
