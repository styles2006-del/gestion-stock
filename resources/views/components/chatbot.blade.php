{{-- Chatbot flottant --}}
<div id="chatbot" class="fixed bottom-4 right-4 z-50">
    {{-- Bouton --}}
    <button type="button" id="chatbot-toggle"
        class="flex h-14 w-14 items-center justify-center rounded-full bg-accent-500 text-white shadow-lg shadow-accent-500/30 transition hover:bg-accent-600 active:scale-95"
        aria-label="Chatbot">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
        </svg>
    </button>

    {{-- Panneau --}}
    <div id="chatbot-panel"
        class="absolute bottom-16 right-0 hidden w-[340px] max-w-[calc(100vw-2rem)] overflow-hidden rounded-2xl border border-black/10 bg-white shadow-2xl dark:border-white/10 dark:bg-ink-900"
        style="height: 520px; max-height: calc(100vh - 6rem);">
        <div class="flex h-full flex-col">
            {{-- En-tête --}}
            <div class="flex items-center justify-between border-b border-black/5 px-4 py-3 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <div
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-accent-500/15 text-accent-600 dark:text-accent-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-ink-800 dark:text-white">Assistant stock</p>
                        <p class="text-[11px] text-ink-400">En ligne</p>
                    </div>
                </div>
                <button type="button" id="chatbot-close" class="btn-ghost !p-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Messages --}}
            <div id="chatbot-messages" class="no-scrollbar flex-1 space-y-3 overflow-y-auto px-4 py-4 text-sm">
                <div class="flex justify-start">
                    <div
                        class="max-w-[85%] rounded-2xl rounded-tl-sm bg-ink-100 px-3.5 py-2.5 text-ink-800 dark:bg-white/10 dark:text-ink-100">
                        Bonjour ! Posez-moi une question sur le stock, les articles, les mouvements...
                    </div>
                </div>
            </div>

            {{-- Suggestions --}}
            <div id="chatbot-suggestions"
                class="flex flex-wrap gap-1.5 border-t border-black/5 px-3 py-2 dark:border-white/10"></div>

            {{-- Saisie --}}
            <form id="chatbot-form"
                class="flex items-center gap-2 border-t border-black/5 px-3 py-3 dark:border-white/10">
                <input type="text" id="chatbot-input" class="input !rounded-full !py-2"
                    placeholder="Votre question..." autocomplete="off">
                <button type="submit" class="btn-primary !rounded-full !px-3 !py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M12 5l7 7-7 7" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    (function() {
        const toggle = document.getElementById('chatbot-toggle');
        const panel = document.getElementById('chatbot-panel');
        const closeBtn = document.getElementById('chatbot-close');
        const form = document.getElementById('chatbot-form');
        const input = document.getElementById('chatbot-input');
        const messages = document.getElementById('chatbot-messages');
        const suggestionsEl = document.getElementById('chatbot-suggestions');
        const url = '{{ route('chatbot.ask') }}';
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        function openPanel() {
            panel.classList.remove('hidden');
            input.focus();
        }

        function closePanel() {
            panel.classList.add('hidden');
        }

        toggle.addEventListener('click', () => {
            panel.classList.contains('hidden') ? openPanel() : closePanel();
        });
        closeBtn.addEventListener('click', closePanel);

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function addMessage(text, from) {
            const wrapper = document.createElement('div');
            wrapper.className = 'flex ' + (from === 'user' ? 'justify-end' : 'justify-start');

            const bubble = document.createElement('div');
            bubble.className = 'max-w-[85%] whitespace-pre-wrap rounded-2xl px-3.5 py-2.5 ' +
                (from === 'user' ?
                    'rounded-tr-sm bg-accent-500 text-white' :
                    'rounded-tl-sm bg-ink-100 text-ink-800 dark:bg-white/10 dark:text-ink-100');
            bubble.innerHTML = escapeHtml(text).replace(/\n/g, '<br>');

            wrapper.appendChild(bubble);
            messages.appendChild(wrapper);
            messages.scrollTop = messages.scrollHeight;
        }

        function showTyping() {
            const wrapper = document.createElement('div');
            wrapper.id = 'chatbot-typing';
            wrapper.className = 'flex justify-start';
            wrapper.innerHTML =
                '<div class="max-w-[85%] rounded-2xl rounded-tl-sm bg-ink-100 px-3.5 py-2.5 text-ink-400 dark:bg-white/10">…</div>';
            messages.appendChild(wrapper);
            messages.scrollTop = messages.scrollHeight;
        }

        function hideTyping() {
            const el = document.getElementById('chatbot-typing');
            if (el) el.remove();
        }

        function renderSuggestions(list) {
            suggestionsEl.innerHTML = '';
            (list || []).forEach((s) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className =
                    'rounded-full bg-black/5 px-2.5 py-1 text-xs text-ink-600 transition hover:bg-black/10 dark:bg-white/10 dark:text-ink-200';
                btn.textContent = s;
                btn.addEventListener('click', () => {
                    input.value = s;
                    form.requestSubmit();
                });
                suggestionsEl.appendChild(btn);
            });
        }

        async function send() {
            const msg = input.value.trim();
            if (!msg) return;
            input.value = '';
            addMessage(msg, 'user');
            showTyping();

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        message: msg
                    }),
                });
                const data = await res.json();
                hideTyping();
                addMessage(data.reply || '…', 'bot');
                renderSuggestions(data.suggestions);
            } catch (e) {
                hideTyping();
                addMessage('Erreur de connexion.', 'bot');
            }
        }

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            send();
        });

        renderSuggestions(
            {{ Js::from(
                app()->getLocale() === 'fr'
                    ? ['Articles disponibles', 'Stock faible', 'Statistiques', 'Derniers mouvements', 'Entrepôts']
                    : ['Available articles', 'Low stock', 'Statistics', 'Recent movements', 'Warehouses'],
            ) }}
            );

    })();
</script>
