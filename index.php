<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content-Based SMS Spam Detection — TF-IDF, Lexicon-Based Feature & Naive Bayes</title>
    <meta name="description" content="Deteksi SMS Spam menggunakan kombinasi Lexicon-Based Feature dan Naive Bayes.">
    <meta name="keywords" content="SMS Spam Detection, TF-IDF, Naive Bayes, Lexicon-Based, Machine Learning, NLP">
    <!-- <meta name="author" content="-"> -->

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'selector',
            theme: {
                extend: {
                    fontFamily: {
                        serif: ['Cormorant Garamond', 'Georgia', 'serif'],
                        sans: ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        surface: {
                            light: '#FAF9F7',
                            dark: '#1A1A1A',
                        },
                        card: {
                            light: '#FFFFFF',
                            dark: '#242424',
                        },
                        border: {
                            light: '#E8E5E0',
                            dark: '#333333',
                        },
                        text: {
                            primary: {
                                light: '#1A1A1A',
                                dark: '#E8E5E0',
                            },
                            secondary: {
                                light: '#6B6560',
                                dark: '#9A958F',
                            },
                        },
                        accent: {
                            primary: '#C96442',
                            spam: {
                                bg: '#FEF2F2',
                                border: '#FECACA',
                                text: '#991B1B',
                                darkBg: '#3B1010',
                                darkBorder: '#7F1D1D',
                                darkText: '#FCA5A5',
                            },
                            ham: {
                                bg: '#F0FDF4',
                                border: '#BBF7D0',
                                text: '#166534',
                                darkBg: '#052E16',
                                darkBorder: '#166534',
                                darkText: '#86EFAC',
                            },
                        },
                    },
                    borderRadius: {
                        'card': '24px',
                        'btn': '16px',
                        'pill': '9999px',
                    },
                },
            },
        }
    </script>

    <!-- Custom Styles -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="font-sans antialiased transition-colors duration-500">

    <!-- Theme Toggle -->
    <header class="fixed top-0 left-0 right-0 z-50 backdrop-blur-md bg-surface-light/80 dark:bg-surface-dark/80 border-b border-border-light dark:border-border-dark transition-colors duration-500">
        <div class="max-w-3xl mx-auto px-6 py-3 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-accent-primary animate-pulse-slow"></div>
                <span class="text-xs font-medium tracking-wider uppercase text-text-secondary-light dark:text-text-secondary-dark">Content-Based SMS Spam Detection</span>
            </div>
            <button id="theme-toggle" 
                    class="group relative w-10 h-10 rounded-full flex items-center justify-center border border-border-light dark:border-border-dark hover:bg-card-light dark:hover:bg-card-dark transition-all duration-300"
                    aria-label="Toggle dark mode"
                    title="Toggle dark mode">
                <!-- Sun icon -->
                <svg class="w-4 h-4 text-text-secondary-light dark:text-text-secondary-dark transition-transform duration-500 dark:rotate-180 dark:scale-0 absolute" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z"/>
                </svg>
                <!-- Moon icon -->
                <svg class="w-4 h-4 text-text-secondary-light dark:text-text-secondary-dark transition-transform duration-500 rotate-180 scale-0 dark:rotate-0 dark:scale-100 absolute" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z"/>
                </svg>
            </button>
        </div>
    </header>

    <!-- Main Content -->
    <main class="min-h-screen pt-20 pb-16">

        <!-- Hero / Landing Section -->
        <section class="max-w-3xl mx-auto px-6 pt-16 md:pt-24 pb-8">
            <div class="text-center mb-12 animate-fade-in">
                <h1 class="font-serif text-4xl md:text-5xl lg:text-6xl font-semibold text-text-primary-light dark:text-text-primary-dark leading-tight mb-6 tracking-tight">
                    Content-Based SMS<br class="hidden sm:block"> Spam Detection
                </h1>
                <p class="text-base md:text-lg text-text-secondary-light dark:text-text-secondary-dark leading-relaxed max-w-xl mx-auto">
                    Deteksi SMS Spam menggunakan <span class="text-text-primary-light dark:text-text-primary-dark font-medium">Lexicon-Based Feature</span> dan
                    <span class="text-text-primary-light dark:text-text-primary-dark font-medium">Naive Bayes</span>
                </p>
            </div>

            <!-- Input Composer -->
            <div id="composer" class="composer-card bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-card p-1 transition-all duration-300 animate-slide-up">
                <div class="relative">
                    <textarea 
                        id="sms-input"
                        class="w-full min-h-[120px] max-h-[300px] p-5 pb-14 bg-transparent text-text-primary-light dark:text-text-primary-dark placeholder:text-text-secondary-light/50 dark:placeholder:text-text-secondary-dark/50 text-base leading-relaxed resize-none outline-none rounded-[20px] transition-colors duration-300"
                        placeholder="Masukkan isi SMS yang ingin dianalisis..."
                        maxlength="1000"
                        aria-label="Input SMS untuk analisis spam"
                    ></textarea>

                    <!-- Bottom Bar -->
                    <div class="absolute bottom-3 left-5 right-5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <span id="char-counter" class="text-xs text-text-secondary-light/60 dark:text-text-secondary-dark/60 tabular-nums transition-colors duration-300">0 / 1000</span>
                            <button id="clear-btn" 
                                    class="text-xs text-text-secondary-light/60 dark:text-text-secondary-dark/60 hover:text-accent-primary transition-all duration-200 hidden items-center gap-1"
                                    aria-label="Hapus teks"
                                    title="Hapus teks">
                                <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Hapus
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-text-secondary-light/40 dark:text-text-secondary-dark/40 hidden sm:inline">Ctrl + Enter</span>
                            <button id="analyze-btn"
                                    class="inline-flex items-center gap-2 px-5 py-2 bg-text-primary-light dark:bg-text-primary-dark text-card-light dark:text-card-dark text-sm font-medium rounded-btn hover:opacity-90 active:scale-[0.97] transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed disabled:active:scale-100"
                                    disabled
                                    aria-label="Analisis SMS">
                                <span id="analyze-btn-text">Analisis SMS</span>
                                <svg id="analyze-btn-icon" class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12h15m0 0l-6.75-6.75M19.5 12l-6.75 6.75"/>
                                </svg>
                                <!-- Loading spinner (hidden by default) -->
                                <svg id="analyze-btn-spinner" class="w-4 h-4 animate-spin hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Results Area -->
        <section id="results-area" class="max-w-3xl mx-auto px-6 py-8">

            <!-- Empty State -->
            <div id="empty-state" class="text-center py-16 animate-fade-in">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-border-light/50 dark:bg-border-dark/50 mb-6">
                    <svg class="w-7 h-7 text-text-secondary-light/40 dark:text-text-secondary-dark/40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                    </svg>
                </div>
                <h2 class="font-serif text-xl text-text-primary-light dark:text-text-primary-dark mb-2">Belum ada analisis</h2>
                <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark">Masukkan isi SMS untuk memulai proses deteksi spam.</p>
            </div>

            <!-- Loading State (hidden by default) -->
            <div id="loading-state" class="text-center py-16 hidden">
                <div class="inline-flex flex-col items-center gap-4">
                    <div class="loading-dots flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-text-secondary-light/40 dark:bg-text-secondary-dark/40"></span>
                        <span class="w-2 h-2 rounded-full bg-text-secondary-light/40 dark:bg-text-secondary-dark/40"></span>
                        <span class="w-2 h-2 rounded-full bg-text-secondary-light/40 dark:bg-text-secondary-dark/40"></span>
                    </div>
                    <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark">Thinking...</p>
                </div>
            </div>

            <!-- Result Card (hidden by default) -->
            <div id="result-card" class="hidden animate-slide-up">

                <!-- Prediction Banner -->
                <div id="prediction-banner" class="rounded-card p-6 md:p-8 mb-6 border transition-colors duration-500">
                    <div class="flex items-start gap-4">
                        <div id="prediction-icon" class="flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-lg">
                            <!-- Icon set dynamically -->
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 id="prediction-title" class="font-serif text-2xl md:text-3xl font-semibold mb-1"></h2>
                            <p id="prediction-subtitle" class="text-sm opacity-70"></p>
                        </div>
                    </div>
                </div>

                <!-- Confidence Section -->
                <div class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-card p-6 md:p-8 mb-6 transition-colors duration-500">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-medium text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider">Confidence Score</h3>
                        <span id="confidence-badge" class="inline-flex items-center px-3 py-1 rounded-pill text-sm font-semibold tabular-nums"></span>
                    </div>
                    <div class="w-full h-3 bg-border-light/50 dark:bg-border-dark/50 rounded-full overflow-hidden">
                        <div id="confidence-bar" class="h-full rounded-full transition-all duration-1000 ease-out" style="width: 0%"></div>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <!-- Prediction Detail -->
                    <div class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-card p-6 transition-colors duration-500">
                        <p class="text-xs font-medium text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-2">Prediction</p>
                        <p id="detail-prediction" class="text-lg font-semibold text-text-primary-light dark:text-text-primary-dark tabular-nums"></p>
                    </div>
                    <!-- Lexicon Score -->
                    <div class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-card p-6 transition-colors duration-500">
                        <p class="text-xs font-medium text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-2">Lexicon Score</p>
                        <p id="detail-lexicon" class="text-lg font-semibold text-text-primary-light dark:text-text-primary-dark tabular-nums"></p>
                    </div>
                </div>

                <!-- Clean Text -->
                <div class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-card p-6 md:p-8 transition-colors duration-500">
                    <p class="text-xs font-medium text-text-secondary-light dark:text-text-secondary-dark uppercase tracking-wider mb-3">Clean Text</p>
                    <p id="detail-clean-text" class="text-base text-text-primary-light dark:text-text-primary-dark leading-relaxed font-light italic"></p>
                </div>
            </div>

            <!-- Error State (hidden) -->
            <div id="error-state" class="hidden animate-fade-in">
                <div class="text-center py-12 bg-accent-spam-bg dark:bg-accent-spam-darkBg border border-accent-spam-border dark:border-accent-spam-darkBorder rounded-card p-6">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-accent-spam-border/30 dark:bg-accent-spam-darkBorder/30 mb-4">
                        <svg class="w-5 h-5 text-accent-spam-text dark:text-accent-spam-darkText" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-lg text-accent-spam-text dark:text-accent-spam-darkText mb-1">Terjadi Kesalahan</h3>
                    <p id="error-message" class="text-sm text-accent-spam-text/70 dark:text-accent-spam-darkText/70">Tidak dapat terhubung ke server. Coba lagi nanti.</p>
                </div>
            </div>

        </section>

        <!-- Methods Section -->
        <section class="max-w-3xl mx-auto px-6 py-16 border-t border-border-light dark:border-border-dark mt-8 transition-colors duration-500">
            <h2 class="font-serif text-2xl md:text-3xl text-center text-text-primary-light dark:text-text-primary-dark mb-10">Data Pipeline</h2>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- TF-IDF -->
                <div class="method-card group bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-card p-6 hover:border-accent-primary/30 transition-all duration-300">
                    <div class="w-10 h-10 rounded-full bg-accent-primary/10 flex items-center justify-center mb-4 group-hover:bg-accent-primary/20 transition-colors duration-300">
                        <svg class="w-5 h-5 text-accent-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5M9 11.25v1.5M12 9v3.75m3-6v6"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-text-primary-light dark:text-text-primary-dark mb-2">TF-IDF</h3>
                    <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark leading-relaxed">Mengubah teks menjadi representasi numerik berdasarkan tingkat kepentingan kata.</p>
                </div>

                <!-- Lexicon-Based Feature -->
                <div class="method-card group bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-card p-6 hover:border-accent-primary/30 transition-all duration-300">
                    <div class="w-10 h-10 rounded-full bg-accent-primary/10 flex items-center justify-center mb-4 group-hover:bg-accent-primary/20 transition-colors duration-300">
                        <svg class="w-5 h-5 text-accent-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-text-primary-light dark:text-text-primary-dark mb-2">Lexicon-Based Feature</h3>
                    <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark leading-relaxed">Mengidentifikasi kata-kata yang umum digunakan dalam pesan spam.</p>
                </div>

                <!-- Naive Bayes -->
                <div class="method-card group bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-card p-6 hover:border-accent-primary/30 transition-all duration-300">
                    <div class="w-10 h-10 rounded-full bg-accent-primary/10 flex items-center justify-center mb-4 group-hover:bg-accent-primary/20 transition-colors duration-300">
                        <svg class="w-5 h-5 text-accent-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5"/>
                        </svg>
                    </div>
                    <h3 class="font-serif text-lg font-semibold text-text-primary-light dark:text-text-primary-dark mb-2">Naive Bayes</h3>
                    <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark leading-relaxed">Mengklasifikasikan pesan berdasarkan probabilitas kemunculan fitur.</p>
                </div>
            </div>
        </section>

    </main>

    <!-- Footer -->
    <footer class="border-t border-border-light dark:border-border-dark transition-colors duration-500">
        <div class="max-w-3xl mx-auto px-6 py-12 text-center">
            <h2 class="font-serif text-lg text-text-primary-light dark:text-text-primary-dark mb-2">Content-Based SMS Spam Detection</h2>
            <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark mb-4 leading-relaxed">
                Menggunakan kombinasi Lexicon-Based Feature dan Naive Bayes
            </p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="assets/js/app.js"></script>
</body>
</html>
