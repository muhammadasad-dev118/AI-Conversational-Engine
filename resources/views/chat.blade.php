<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>AI Chatbot | Laravel 11</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased h-full bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-slate-100 flex flex-col">
    
    <!-- Header -->
    <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 py-4 px-6 fixed top-0 w-full z-10">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-xl flex items-center justify-center text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-bold">AI Assistant</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Powered by Laravel 11 & OpenAI</p>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <span class="flex h-2 w-2 rounded-full bg-green-500"></span>
                <span class="text-sm font-medium">Online</span>
            </div>
        </div>
    </header>

    <!-- Main Chat Area -->
    <main class="flex-1 overflow-y-auto pt-20 pb-24 px-4 sm:px-6">
        <div id="chat-container" class="max-w-4xl mx-auto space-y-6 py-8">
            @foreach($chat->messages as $message)
                <div class="flex {{ $message->role === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%] sm:max-w-[70%] rounded-2xl px-4 py-3 shadow-sm {{ $message->role === 'user' ? 'bg-indigo-600 text-white rounded-tr-none' : 'bg-white dark:bg-slate-800 dark:text-slate-200 rounded-tl-none border border-slate-200 dark:border-slate-700' }}">
                        <p class="text-sm sm:text-base leading-relaxed whitespace-pre-wrap">{{ $message->content }}</p>
                        <span class="text-[10px] mt-1 block opacity-70 {{ $message->role === 'user' ? 'text-right' : 'text-left' }}">
                            {{ $message->created_at->format('H:i') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Thinking Indicator -->
        <div id="thinking" class="max-w-4xl mx-auto hidden transition-all duration-300">
            <div class="flex justify-start">
                <div class="max-w-[70%] bg-white dark:bg-slate-800 rounded-2xl rounded-tl-none px-4 py-3 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div class="flex space-x-1">
                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-1.5 h-1.5 bg-slate-400 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Input Bar -->
    <div class="fixed bottom-0 w-full bg-white dark:bg-slate-800 border-t border-slate-200 dark:border-slate-700 py-4 px-4 sm:px-6">
        <div class="max-w-4xl mx-auto">
            <form id="chat-form" class="flex items-end space-x-3">
                <div class="flex-1 relative">
                    <textarea 
                        id="user-input"
                        rows="1"
                        placeholder="Type your message..."
                        class="w-full bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl px-4 py-3 focus:ring-2 focus:ring-indigo-600 focus:border-transparent outline-none resize-none transition-all duration-200 text-sm sm:text-base"
                    ></textarea>
                </div>
                <button 
                    type="submit" 
                    id="send-btn"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white p-3 rounded-xl transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                </button>
            </form>
            <p class="text-[10px] text-center text-slate-400 mt-2">OpenAI model: {{ env('OPENAI_MODEL', 'gpt-4o-mini') }}</p>
        </div>
    </div>

    <script>
        const chatForm = document.getElementById('chat-form');
        const chatContainer = document.getElementById('chat-container');
        const userInput = document.getElementById('user-input');
        const thinkingIndicator = document.getElementById('thinking');
        const sendBtn = document.getElementById('send-btn');

        // Auto-resize textarea
        userInput.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });

        // Submit on Enter (but not Shift+Enter)
        userInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                chatForm.dispatchEvent(new Event('submit'));
            }
        });

        chatForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const message = userInput.value.trim();
            if (!message) return;

            // Add user message to UI
            addMessageToUI('user', message);
            userInput.value = '';
            userInput.style.height = 'auto';
            
            // Show thinking indicator
            thinkingIndicator.classList.remove('hidden');
            scrollToBottom();
            
            // Disable input
            userInput.disabled = true;
            sendBtn.disabled = true;

            try {
                const response = await fetch('{{ route('chat.send') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message })
                });

                const data = await response.json();
                
                // Remove thinking indicator
                thinkingIndicator.classList.add('hidden');
                
                if (data.role === 'assistant') {
                    addMessageToUI('assistant', data.content);
                } else {
                    addMessageToUI('system', data.content || 'Something went wrong.');
                }
            } catch (error) {
                thinkingIndicator.classList.add('hidden');
                addMessageToUI('system', 'Technical error occurred.');
                console.error(error);
            } finally {
                userInput.disabled = false;
                sendBtn.disabled = false;
                userInput.focus();
                scrollToBottom();
            }
        });

        function addMessageToUI(role, content) {
            const div = document.createElement('div');
            div.className = `flex ${role === 'user' ? 'justify-end' : 'justify-start'}`;
            
            const innerDiv = document.createElement('div');
            innerDiv.className = `max-w-[85%] sm:max-w-[70%] rounded-2xl px-4 py-3 shadow-sm ${
                role === 'user' 
                ? 'bg-indigo-600 text-white rounded-tr-none' 
                : (role === 'system' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-white dark:bg-slate-800 dark:text-slate-200 rounded-tl-none border border-slate-200 dark:border-slate-700')
            }`;
            
            const p = document.createElement('p');
            p.className = 'text-sm sm:text-base leading-relaxed whitespace-pre-wrap';
            p.textContent = content;
            
            const timeSpan = document.createElement('span');
            timeSpan.className = `text-[10px] mt-1 block opacity-70 ${role === 'user' ? 'text-right' : 'text-left'}`;
            timeSpan.textContent = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            
            innerDiv.appendChild(p);
            innerDiv.appendChild(timeSpan);
            div.appendChild(innerDiv);
            chatContainer.appendChild(div);
            scrollToBottom();
        }

        function scrollToBottom() {
            window.scrollTo({
                top: document.body.scrollHeight,
                behavior: 'smooth'
            });
        }

        // Initial scroll
        scrollToBottom();
    </script>
</body>
</html>
