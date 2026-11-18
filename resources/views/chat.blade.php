<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SNC Pharma - Asistente de Ventas</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #090714;
            color: #f3f4f6;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.02);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .typing-dot {
            animation: blink 1.2s infinite;
        }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
        @keyframes blink {
            0%, 80%, 100% { opacity: 0.25; }
            40% { opacity: 1; }
        }
        #chat-messages::-webkit-scrollbar { width: 6px; }
        #chat-messages::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Background glow -->
    <div class="absolute top-0 right-0 w-[500px] h-[500px] rounded-full bg-indigo-500/5 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] rounded-full bg-purple-500/5 blur-[120px] pointer-events-none"></div>

    @include('layouts.sidebar')

    <div id="main-wrapper" class="lg:pl-64 flex flex-col min-h-screen">
        @include('layouts.topbar', ['title' => 'Asistente de Ventas', 'subtitle' => 'Pregunta sobre tus datos en lenguaje natural'])

    <!-- Main Content Area -->
    <main class="flex-grow max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-8 relative z-10 flex flex-col">

        <!-- Messages -->
        <div id="chat-messages" class="glass-card rounded-2xl p-5 flex-1 overflow-y-auto space-y-4 min-h-[400px] max-h-[calc(100vh-270px)]">
            <!-- Welcome message -->
            <div class="flex items-start space-x-3">
                <div class="bg-indigo-500/15 border border-indigo-500/30 p-2 rounded-xl shrink-0">
                    <svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <div class="bg-white/5 border border-white/5 rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-gray-300 leading-relaxed max-w-xl">
                    Hola, soy el asistente de SNC Pharma. Pregúntame sobre tus ventas, por ejemplo:
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button onclick="askSuggestion(this)" class="text-[11px] px-2.5 py-1 rounded-full bg-indigo-500/15 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-500/25 transition-all">¿Cuáles fueron mis 5 mejores clientes?</button>
                        <button onclick="askSuggestion(this)" class="text-[11px] px-2.5 py-1 rounded-full bg-indigo-500/15 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-500/25 transition-all">¿Cuánto vendí en total en USD?</button>
                        <button onclick="askSuggestion(this)" class="text-[11px] px-2.5 py-1 rounded-full bg-indigo-500/15 border border-indigo-500/30 text-indigo-300 hover:bg-indigo-500/25 transition-all">¿Qué productos se vendieron más el último mes?</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Input -->
        <form id="chat-form" class="mt-4 flex items-center space-x-3">
            <input id="chat-input" type="text" autocomplete="off" maxlength="1000"
                placeholder="Escribe tu pregunta sobre las ventas..."
                class="flex-1 bg-white/5 border border-white/10 rounded-2xl px-4 py-3 text-sm text-gray-200 placeholder-gray-600 focus:outline-none focus:border-indigo-500/50 transition-all">
            <button id="chat-send" type="submit"
                class="px-5 py-3 bg-indigo-600 hover:bg-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed text-white text-sm font-semibold rounded-2xl transition-all shrink-0">
                Enviar
            </button>
        </form>
    </main>
    </div>

    <script>
        const messagesEl = document.getElementById('chat-messages');
        const form = document.getElementById('chat-form');
        const input = document.getElementById('chat-input');
        const sendBtn = document.getElementById('chat-send');
        const history = [];

        function esc(s) {
            const d = document.createElement('div');
            d.textContent = s;
            return d.innerHTML;
        }

        function renderMarkdown(text) {
            return esc(text)
                .replace(/\*\*(.+?)\*\*/g, '<strong class="text-white font-semibold">$1</strong>')
                .replace(/^- (.+)$/gm, '<span class="block pl-3">• $1</span>')
                .replace(/\n/g, '<br>');
        }

        function addUserBubble(text) {
            messagesEl.insertAdjacentHTML('beforeend', `
                <div class="flex justify-end">
                    <div class="bg-indigo-600/80 rounded-2xl rounded-tr-sm px-4 py-3 text-sm text-white max-w-xl">${esc(text)}</div>
                </div>`);
        }

        function addAssistantBubble(answer, sql, rows) {
            let extra = '';
            if (sql) {
                extra += `
                    <details class="mt-2 text-[11px] text-gray-500">
                        <summary class="cursor-pointer hover:text-gray-300">Consulta usada</summary>
                        <code class="block mt-1 p-2 rounded-lg bg-black/40 text-indigo-300/80 text-[10px] overflow-x-auto">${esc(sql)}</code>
                    </details>`;
            }
            if (rows && rows.length) {
                const cols = Object.keys(rows[0]);
                const shown = rows.slice(0, 10);
                extra += `<div class="mt-2 overflow-x-auto"><table class="w-full text-[11px] border-collapse">
                    <thead><tr>${cols.map(c => `<th class="text-left px-2 py-1 text-gray-500 font-semibold border-b border-white/10">${esc(c)}</th>`).join('')}</tr></thead>
                    <tbody>${shown.map(r => `<tr>${cols.map(c => `<td class="px-2 py-1 text-gray-300 border-b border-white/5">${esc(String(r[c] ?? ''))}</td>`).join('')}</tr>`).join('')}</tbody>
                </table>${rows.length > 10 ? `<p class="text-[10px] text-gray-600 mt-1">Mostrando 10 de ${rows.length} filas</p>` : ''}</div>`;
            }
            messagesEl.insertAdjacentHTML('beforeend', `
                <div class="flex items-start space-x-3">
                    <div class="bg-indigo-500/15 border border-indigo-500/30 p-2 rounded-xl shrink-0">
                        <svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div class="bg-white/5 border border-white/5 rounded-2xl rounded-tl-sm px-4 py-3 text-sm text-gray-300 leading-relaxed max-w-xl overflow-x-auto">
                        ${renderMarkdown(answer)}${extra}
                    </div>
                </div>`);
        }

        function addTyping() {
            messagesEl.insertAdjacentHTML('beforeend', `
                <div id="typing" class="flex items-start space-x-3">
                    <div class="bg-indigo-500/15 border border-indigo-500/30 p-2 rounded-xl shrink-0">
                        <svg class="w-4 h-4 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                        </svg>
                    </div>
                    <div class="bg-white/5 border border-white/5 rounded-2xl rounded-tl-sm px-4 py-3">
                        <span class="typing-dot inline-block w-1.5 h-1.5 rounded-full bg-indigo-300"></span>
                        <span class="typing-dot inline-block w-1.5 h-1.5 rounded-full bg-indigo-300 ml-1"></span>
                        <span class="typing-dot inline-block w-1.5 h-1.5 rounded-full bg-indigo-300 ml-1"></span>
                    </div>
                </div>`);
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        function scrollBottom() {
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        async function send(text) {
            if (!text.trim()) return;
            addUserBubble(text);
            history.push({ role: 'user', content: text });
            input.value = '';
            input.disabled = true;
            sendBtn.disabled = true;
            addTyping();

            try {
                const res = await fetch('{{ route('chat.ask') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ message: text, history: history.slice(-8) }),
                });
                document.getElementById('typing')?.remove();

                if (!res.ok) {
                    addAssistantBubble('Ocurrió un error procesando tu pregunta (HTTP ' + res.status + ').', null, null);
                } else {
                    const data = await res.json();
                    addAssistantBubble(data.answer || 'Sin respuesta.', data.sql, data.rows);
                    history.push({ role: 'assistant', content: data.answer || '' });
                }
            } catch (e) {
                document.getElementById('typing')?.remove();
                addAssistantBubble('No pude conectar con el servidor. Verifica que siga corriendo.', null, null);
            }

            input.disabled = false;
            sendBtn.disabled = false;
            input.focus();
            scrollBottom();
        }

        function askSuggestion(btn) {
            send(btn.textContent.trim());
        }

        form.addEventListener('submit', e => {
            e.preventDefault();
            send(input.value);
        });
    </script>
</body>
</html>
