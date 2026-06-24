/**
 * SMS Spam Detection — Application Logic
 * Content-Based SMS Spam Detection
 * Using TF-IDF, Lexicon-Based Feature & Naive Bayes
 */

(function () {
    'use strict';

    // ─── DOM Elements ──────────────────────────────────────────────
    const $ = (sel) => document.querySelector(sel);
    const textarea      = $('#sms-input');
    const charCounter    = $('#char-counter');
    const clearBtn       = $('#clear-btn');
    const analyzeBtn     = $('#analyze-btn');
    const analyzeBtnText = $('#analyze-btn-text');
    const analyzeBtnIcon = $('#analyze-btn-icon');
    const analyzeBtnSpinner = $('#analyze-btn-spinner');
    const themeToggle    = $('#theme-toggle');

    // State containers
    const emptyState   = $('#empty-state');
    const loadingState = $('#loading-state');
    const resultCard   = $('#result-card');
    const errorState   = $('#error-state');

    // Result elements
    const predictionBanner   = $('#prediction-banner');
    const predictionIcon     = $('#prediction-icon');
    const predictionTitle    = $('#prediction-title');
    const predictionSubtitle = $('#prediction-subtitle');
    const confidenceBadge    = $('#confidence-badge');
    const confidenceBar      = $('#confidence-bar');
    const detailPrediction   = $('#detail-prediction');
    const detailLexicon      = $('#detail-lexicon');
    const detailCleanText    = $('#detail-clean-text');
    const errorMessage       = $('#error-message');

    // ─── Constants ─────────────────────────────────────────────────
    const API_URL = 'api.php';
    const MAX_CHARS = 1000;

    // ─── Theme Management ──────────────────────────────────────────
    function getPreferredTheme() {
        const stored = localStorage.getItem('sms-spam-theme');
        if (stored) return stored;
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    function applyTheme(theme) {
        const html = document.documentElement;

        // Enable transitions temporarily
        html.classList.add('theme-transitioning');

        if (theme === 'dark') {
            html.classList.add('dark');
            html.setAttribute('data-theme', 'dark');
        } else {
            html.classList.remove('dark');
            html.setAttribute('data-theme', 'light');
        }

        localStorage.setItem('sms-spam-theme', theme);

        // Remove transition class after animation completes
        setTimeout(() => {
            html.classList.remove('theme-transitioning');
        }, 550);
    }

    function toggleTheme() {
        const current = document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        applyTheme(current === 'dark' ? 'light' : 'dark');
    }

    // Apply theme on load (before paint)
    applyTheme(getPreferredTheme());

    themeToggle.addEventListener('click', toggleTheme);

    // ─── Textarea Auto-Resize ──────────────────────────────────────
    function autoResize() {
        textarea.style.height = 'auto';
        const maxHeight = 300;
        textarea.style.height = Math.min(textarea.scrollHeight, maxHeight) + 'px';
    }

    // ─── Character Counter ─────────────────────────────────────────
    function updateCharCounter() {
        const len = textarea.value.length;
        charCounter.textContent = `${len} / ${MAX_CHARS}`;

        // Color the counter when near limit
        if (len > MAX_CHARS * 0.9) {
            charCounter.classList.add('!text-accent-primary');
        } else {
            charCounter.classList.remove('!text-accent-primary');
        }
    }

    // ─── Button State ──────────────────────────────────────────────
    function updateButtonState() {
        const hasText = textarea.value.trim().length > 0;
        analyzeBtn.disabled = !hasText;

        // Show/hide clear button
        if (hasText) {
            clearBtn.classList.remove('hidden');
            clearBtn.classList.add('inline-flex');
        } else {
            clearBtn.classList.add('hidden');
            clearBtn.classList.remove('inline-flex');
        }
    }

    // ─── Input Events ──────────────────────────────────────────────
    textarea.addEventListener('input', () => {
        autoResize();
        updateCharCounter();
        updateButtonState();
    });

    clearBtn.addEventListener('click', () => {
        textarea.value = '';
        autoResize();
        updateCharCounter();
        updateButtonState();
        textarea.focus();
    });

    // ─── Keyboard Shortcut (Ctrl + Enter) ──────────────────────────
    textarea.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            if (!analyzeBtn.disabled) {
                analyzeSMS();
            }
        }
    });

    // ─── Analyze Button Click ──────────────────────────────────────
    analyzeBtn.addEventListener('click', (e) => {
        e.preventDefault();
        analyzeSMS();
    });

    // ─── State Management ──────────────────────────────────────────
    function showState(state) {
        // Hide all
        emptyState.classList.add('hidden');
        loadingState.classList.add('hidden');
        resultCard.classList.add('hidden');
        errorState.classList.add('hidden');

        // Brief delay for transition
        requestAnimationFrame(() => {
            switch (state) {
                case 'empty':
                    emptyState.classList.remove('hidden');
                    break;
                case 'loading':
                    loadingState.classList.remove('hidden');
                    break;
                case 'result':
                    resultCard.classList.remove('hidden');
                    break;
                case 'error':
                    errorState.classList.remove('hidden');
                    break;
            }
        });
    }

    function setLoadingButton(isLoading) {
        if (isLoading) {
            analyzeBtn.disabled = true;
            analyzeBtnText.textContent = 'Menganalisis...';
            analyzeBtnIcon.classList.add('hidden');
            analyzeBtnSpinner.classList.remove('hidden');
        } else {
            analyzeBtn.disabled = !textarea.value.trim();
            analyzeBtnText.textContent = 'Analisis SMS';
            analyzeBtnIcon.classList.remove('hidden');
            analyzeBtnSpinner.classList.add('hidden');
        }
    }

    // ─── API Call ──────────────────────────────────────────────────
    async function analyzeSMS() {
        const smsText = textarea.value.trim();
        if (!smsText) return;

        // Show loading
        showState('loading');
        setLoadingButton(true);

        try {
            const response = await fetch(API_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ sms: smsText }),
            });

            if (!response.ok) {
                throw new Error(`Server error: ${response.status}`);
            }

            const data = await response.json();
            renderResult(data);
            showState('result');

            // Smooth scroll to result
            setTimeout(() => {
                resultCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 100);

        } catch (err) {
            console.error('Analysis failed:', err);
            errorMessage.textContent = err.message || 'Tidak dapat terhubung ke server. Coba lagi nanti.';
            showState('error');
        } finally {
            setLoadingButton(false);
        }
    }

    // ─── Render Result ─────────────────────────────────────────────
    function renderResult(data) {
        const isSpam = data.prediction === 'SPAM';
        const confidence = parseFloat(data.confidence) || 0;
        const lexiconScore = data.lexicon_score !== undefined ? data.lexicon_score : '—';
        const cleanText = data.clean_text || '—';

        // Prediction Banner
        if (isSpam) {
            predictionBanner.className = 'rounded-card p-6 md:p-8 mb-6 border transition-colors duration-500 bg-accent-spam-bg dark:bg-accent-spam-darkBg border-accent-spam-border dark:border-accent-spam-darkBorder';
            predictionIcon.className = 'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-lg bg-accent-spam-border/40 dark:bg-accent-spam-darkBorder/40';
            predictionIcon.textContent = '⚠️';
            predictionTitle.textContent = 'Kemungkinan Spam';
            predictionTitle.className = 'font-serif text-2xl md:text-3xl font-semibold mb-1 text-accent-spam-text dark:text-accent-spam-darkText';
            predictionSubtitle.textContent = 'Berdasarkan analisis konten, SMS ini memiliki ciri-ciri pesan spam. Sebaiknya abaikan dan jangan klik tautan yang ada di dalamnya.';
            predictionSubtitle.className = 'text-sm text-accent-spam-text/70 dark:text-accent-spam-darkText/70';
        } else {
            predictionBanner.className = 'rounded-card p-6 md:p-8 mb-6 border transition-colors duration-500 bg-accent-ham-bg dark:bg-accent-ham-darkBg border-accent-ham-border dark:border-accent-ham-darkBorder';
            predictionIcon.className = 'flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-lg bg-accent-ham-border/40 dark:bg-accent-ham-darkBorder/40';
            predictionIcon.textContent = '✓';
            predictionTitle.textContent = 'Terlihat Legitimate';
            predictionTitle.className = 'font-serif text-2xl md:text-3xl font-semibold mb-1 text-accent-ham-text dark:text-accent-ham-darkText';
            predictionSubtitle.textContent = 'Dari sisi konten, SMS ini tidak menunjukkan pola spam. Namun, tetap pastikan keaslian pengirim sebelum merespons atau memberikan informasi pribadi.';
            predictionSubtitle.className = 'text-sm text-accent-ham-text/70 dark:text-accent-ham-darkText/70';
        }

        // Confidence
        const confColor = isSpam
            ? 'bg-accent-spam-text/10 text-accent-spam-text dark:bg-accent-spam-darkText/10 dark:text-accent-spam-darkText'
            : 'bg-accent-ham-text/10 text-accent-ham-text dark:bg-accent-ham-darkText/10 dark:text-accent-ham-darkText';
        const barColor = isSpam
            ? 'bg-accent-spam-text dark:bg-accent-spam-darkText'
            : 'bg-accent-ham-text dark:bg-accent-ham-darkText';

        confidenceBadge.className = `inline-flex items-center px-3 py-1 rounded-pill text-sm font-semibold tabular-nums ${confColor}`;
        confidenceBadge.textContent = `${confidence.toFixed(2)}%`;

        // Animate confidence bar
        confidenceBar.style.width = '0%';
        confidenceBar.className = `h-full rounded-full transition-all duration-1000 ease-out ${barColor}`;
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                confidenceBar.style.width = `${Math.min(confidence, 100)}%`;
            });
        });

        // Details
        detailPrediction.textContent = data.prediction || '—';
        detailLexicon.textContent = lexiconScore;
        detailCleanText.textContent = cleanText;
    }

    // ─── Initialize ────────────────────────────────────────────────
    function init() {
        autoResize();
        updateCharCounter();
        updateButtonState();
        showState('empty');

        // Focus textarea on desktop
        if (window.innerWidth > 768) {
            setTimeout(() => textarea.focus(), 600);
        }
    }

    // Run
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
