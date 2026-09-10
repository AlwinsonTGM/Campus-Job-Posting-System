/**
 * Campus Hire - Hero 3D Robot Interactive Controller
 * Features:
 *  - Three.js r128 + GLTFLoader
 *  - Studio Lighting with Brand Emerald Rim Highlights
 *  - Real-time Cursor Tracking (Look-at Kinematics with Damped Lerp)
 *  - Organic Idle Bobbing & Micro-tilts
 *  - Expressive Eye Animations: Blinks, Winks, Wide Excitement, Laser Squint, Resume Radar Scan
 *  - Expressive Body Animations: Excited Shimmy/Wiggle, Curious Cock, Affirmative Nod, Stern Interview Lean, 360 Spin
 *  - Interactive Speech Bubble with Typewriter Effect and Sync Micro-Motion
 *  - Modes: Talk (Advice & Perks), /boost (Career Motivation), /grill-me (Mock Interview Questions)
 *  - Low-power IntersectionObserver & Page Visibility Throttling
 */

(function () {
    'use strict';

    // Wait until DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHeroRobot);
    } else {
        initHeroRobot();
    }

    function initHeroRobot() {
        const container = document.getElementById('hero-robot-canvas-container');
        if (!container) return;

        // DOM elements for speech bubble and interactive controls
        const bubbleContainer = document.getElementById('hero-robot-speech-bubble');
        const bubbleCard = document.querySelector('.speech-bubble-card');
        const bubbleTextEl = document.getElementById('speech-bubble-text');
        const bubbleBodyEl = document.querySelector('.speech-bubble-body');
        const bubbleModeBadge = document.getElementById('speech-mode-badge');
        const bubbleModeIcon = document.getElementById('speech-mode-icon');
        const bubbleModeText = document.getElementById('speech-mode-text');
        const bubbleIdentityEl = document.querySelector('.speech-bot-identity');
        const modeChips = document.querySelectorAll('.speech-mode-chips .mode-chip');
        const nextBtn = document.getElementById('speech-next-btn');

        // AI Chat & Model Selector DOM Elements
        const thinkingEl = document.getElementById('speech-bubble-thinking');
        const chatForm = document.getElementById('hero-robot-chat-form');
        const chatInput = document.getElementById('hero-robot-input');
        const chatSubmitBtn = document.getElementById('hero-robot-submit-btn');
        const modelLabelEl = document.getElementById('speech-active-model-name');
        const modelSelectItems = document.querySelectorAll('.model-select-item');
        const quickChips = document.querySelectorAll('.speech-quick-chip');
        const rateLimitPill = document.getElementById('speech-rate-limit-pill');
        const rateCountEl = document.getElementById('speech-rate-count');
        const rateIconEl = document.getElementById('speech-rate-icon');
        const modelTagEl = document.getElementById('speech-model-tag');
        const modelTagNameEl = document.getElementById('speech-model-tag-name');
        const footerModelTextEl = document.getElementById('speech-footer-model-text');

        // Fullscreen Modal DOM Elements
        const fsModal = document.getElementById('campus-ai-fullscreen-modal');
        const fsBackdrop = document.getElementById('fullscreen-backdrop');
        const fsCloseBtn = document.getElementById('fullscreen-close-btn');
        const fsOpenBtn = document.getElementById('speech-fullscreen-btn');
        const fsSlot = document.getElementById('fullscreen-robot-stage-slot');
        const fsMessagesContainer = document.getElementById('fullscreen-messages-container');
        const fsThinking = document.getElementById('fullscreen-thinking');
        const fsForm = document.getElementById('fullscreen-chat-form');
        const fsInput = document.getElementById('fullscreen-chat-input');
        const fsSubmitBtn = document.getElementById('fullscreen-submit-btn');
        const fsQuickChips = document.querySelectorAll('.fs-quick-chip');
        const fsIntentBadge = document.getElementById('fs-intent-badge');
        const fsLiveDot = document.getElementById('fs-live-dot');
        const fsModelName = document.getElementById('fs-model-name');
        const fsRateCount = document.getElementById('fs-rate-count');
        const fsRateIcon = document.getElementById('fs-rate-icon');
        const heroStageWrapper = document.querySelector('.hero-3d-stage-wrapper');

        const chatHistory = [];
        let isFullscreenOpen = false;

        let selectedModel = localStorage.getItem('campus_ai_model') || 'auto';
        let isAwaitingAI = false;
        let cooldownTimer = null;

        const modelPath = container.getAttribute('data-model-path') || 'assets/models/cute_robot.glb';
        const loaderEl = document.getElementById('hero-robot-loader');
        const progressEl = document.getElementById('hero-robot-progress');

        // Eye Color Themes (using MeshBasicMaterial for rich digital OLED display colors)
        let THREE_AVAILABLE = (typeof THREE !== 'undefined');
        const eyeColorThemes = {
            faq: {
                color: THREE_AVAILABLE ? new THREE.Color(0x10b981) : null, // Emerald Green
                badgeClass: 'badge-faq',
                badgeText: 'CAMPUS FAQ',
                icon: 'bi-chat-dots-fill'
            },
            talk: {
                color: THREE_AVAILABLE ? new THREE.Color(0x10b981) : null,
                badgeClass: 'badge-faq',
                badgeText: 'CAMPUS FAQ',
                icon: 'bi-chat-dots-fill'
            },
            boost: {
                color: THREE_AVAILABLE ? new THREE.Color(0xf59e0b) : null, // Solar Amber / Gold
                badgeClass: 'badge-boost',
                badgeText: 'CAREER BOOST',
                icon: 'bi-lightning-charge-fill'
            },
            interview: {
                color: THREE_AVAILABLE ? new THREE.Color(0x6366f1) : null, // Electric Indigo
                badgeClass: 'badge-interview',
                badgeText: 'MOCK PREP',
                icon: 'bi-mortarboard-fill'
            },
            grill: {
                color: THREE_AVAILABLE ? new THREE.Color(0x6366f1) : null,
                badgeClass: 'badge-interview',
                badgeText: 'MOCK PREP',
                icon: 'bi-mortarboard-fill'
            },
            offtopic: {
                color: THREE_AVAILABLE ? new THREE.Color(0xef4444) : null, // Laser Red
                badgeClass: 'badge-offtopic',
                badgeText: 'OFF-TOPIC',
                icon: 'bi-shield-exclamation'
            },
            cooldown: {
                color: THREE_AVAILABLE ? new THREE.Color(0xef4444) : null, // Fiery Red
                badgeClass: 'badge-cooldown',
                badgeText: 'COOLDOWN',
                icon: 'bi-fire'
            }
        };

        let currentMode = 'faq';
        let targetTheme = eyeColorThemes.faq;
        const currentEyeColor = THREE_AVAILABLE ? new THREE.Color(0x10b981) : null;

        // Eye Animation State Machine (supports blink, double-blink, wink, excited, squint, scan, happy, curious)
        const eyeAnim = {
            type: 'idle',
            startTime: 0,
            duration: 0.3
        };
        let nextIdleBlinkTime = 2.5 + Math.random() * 2.5;

        // Body & Head Animation State Machine (replaces jumping with rich interactive motions)
        const bodyAnim = {
            type: 'idle', // 'idle', 'wiggle', 'tilt', 'nod', 'lean', 'spin', 'wave'
            startTime: 0,
            duration: 1.0
        };

        // Interactive reaction cycle when user clicks/taps the 3D robot directly
        let clickAnimIndex = 0;
        const interactiveEyeAnims = ['wink', 'excited', 'sparkle', 'happy', 'squint', 'scan', 'surprised', 'double-blink', 'curious'];
        const interactiveBodyAnims = ['wiggle', 'tilt', 'nod', 'spin', 'lean', 'wave', 'bounce', 'bow'];

        // Speech & Dialogue State
        let isTypingDialogue = false;
        let typewriterTimer = null;
        const dialogueIndices = { talk: 0, boost: 0, grill: 0 };

        // Dialogue Catalog
        const dialogueCatalog = {
            talk: [
                {
                    text: "Hey there! Looking for a campus assistantship or flexible internship? I'm your Campus AI companion!",
                    eye: 'wink',
                    body: 'tilt'
                },
                {
                    text: "University departments schedule student roles flexibly around your lecture blocks so your grades stay first.",
                    eye: 'happy',
                    body: 'nod'
                },
                {
                    text: "Pro tip: Working in a campus office builds faculty recommendations and real experience right where you study!",
                    eye: 'excited',
                    body: 'wiggle'
                },
                {
                    text: "Every employer and campus office on this portal is verified by the university. Safe, accredited, and student-focused!",
                    eye: 'double-blink',
                    body: 'wave'
                },
                {
                    text: "Feel free to ask me anything below—from how to apply to interview tips and flexible shift hours!",
                    eye: 'wink',
                    body: 'wiggle'
                },
                {
                    text: "Ready to explore? Click **EXPLORE VACANCIES** on the left to see open student assistantships today!",
                    eye: 'happy',
                    body: 'nod'
                }
            ],
            boost: [
                {
                    text: "⚡ **BOOST:** You are more qualified than you think! That class project and student club work? That's real management experience—own it on your resume! 🚀",
                    eye: 'excited',
                    body: 'wiggle'
                },
                {
                    text: "⚡ **BOOST:** Never disqualify yourself before applying! Campus offices look for reliability, enthusiasm, and eagerness to learn above all else. Go for it! 💪",
                    eye: 'happy',
                    body: 'spin'
                },
                {
                    text: "⚡ **BOOST:** 80% of landing an assistantship is simply showing up prepared. Polish your 1-page resume, bring genuine curiosity, and that role is yours! ✨",
                    eye: 'excited',
                    body: 'nod'
                },
                {
                    text: "⚡ **BOOST:** Every leader and senior professional started out sitting right where you are today in lectures. Your university journey is your launchpad! 🔥",
                    eye: 'excited',
                    body: 'wiggle'
                },
                {
                    text: "⚡ **BOOST:** Campus employers LOVE proactive students! Tailor a short note explaining why their department mission excites you and you'll stand out instantly! 🌟",
                    eye: 'happy',
                    body: 'spin'
                },
                {
                    text: "⚡ **BOOST:** Rejection is just redirection to the campus role that truly aligns with your talents. Keep your head high and keep applying! 🏆",
                    eye: 'excited',
                    body: 'wave'
                }
            ],
            grill: [
                {
                    text: "🔥 **GRILL-ME:** *'Tell me about yourself without reciting your resume line-by-line.'* — What's your crisp 45-second elevator pitch? ⏱️",
                    eye: 'squint',
                    body: 'lean'
                },
                {
                    text: "🔥 **GRILL-ME:** *'Midterm exams arrive and our office faces an urgent project rush. How do you balance deliverables without letting your grades slip?'* 🎯",
                    eye: 'scan',
                    body: 'lean'
                },
                {
                    text: "🔥 **GRILL-ME:** *'Why do you want this specific campus department assistantship instead of just any job?'* Connect to our office mission! 🧐",
                    eye: 'curious',
                    body: 'tilt'
                },
                {
                    text: "🔥 **GRILL-ME:** *'Give me an example of a team conflict during a group project and how you resolved it constructively.'* Show diplomacy! ⚡",
                    eye: 'scan',
                    body: 'lean'
                },
                {
                    text: "🔥 **GRILL-ME:** *'What is your biggest weakness as a student worker, and what systematic habit did you build this semester to overcome it?'* 💡",
                    eye: 'squint',
                    body: 'nod'
                },
                {
                    text: "🔥 **GRILL-ME:** *'If assigned a critical task using campus software you have never touched before, what is your exact step-by-step approach to master it?'* 🧠",
                    eye: 'scan',
                    body: 'lean'
                }
            ]
        };

        // Alias faq and interview to existing dialogue lists
        dialogueCatalog.faq = dialogueCatalog.talk;
        dialogueCatalog.interview = dialogueCatalog.grill;

        let isLoaded = false;
        let clock = null;
        if (THREE_AVAILABLE) {
            clock = new THREE.Clock();
        }

        // Trigger Eye Animation Helper
        function triggerEyeAnim(type, duration) {
            if (!clock) return;
            eyeAnim.type = type;
            eyeAnim.startTime = clock.getElapsedTime();
            eyeAnim.duration = duration || 0.35;
        }

        // Trigger Body Animation Helper
        function triggerBodyAnim(type, duration) {
            if (!clock) return;
            bodyAnim.type = type;
            bodyAnim.startTime = clock.getElapsedTime();
            bodyAnim.duration = duration || 1.0;
        }

        // Typewriter Effect for Dialogue Bubble
        let currentDialogueFullText = '';
        let currentOnComplete = null;

        function typeDialogue(text, onComplete) {
            currentDialogueFullText = text || '';
            currentOnComplete = typeof onComplete === 'function' ? onComplete : null;
            if (typewriterTimer) {
                clearInterval(typewriterTimer);
                typewriterTimer = null;
            }

            if (!bubbleTextEl) return;

            if (bubbleBodyEl) {
                bubbleBodyEl.scrollTop = 0;
            }

            isTypingDialogue = true;
            bubbleTextEl.innerHTML = '';

            // Bubble card subtle pop
            if (bubbleCard) {
                bubbleCard.style.transform = 'scale(0.98)';
                setTimeout(function () {
                    bubbleCard.style.transform = 'scale(1)';
                }, 140);
            }

            let charIndex = 0;
            const speed = 18; // ms per char

            const textSpan = document.createElement('span');
            textSpan.className = 'speech-text-inner';
            const cursorSpan = document.createElement('span');
            cursorSpan.className = 'speech-cursor';
            bubbleTextEl.appendChild(textSpan);
            bubbleTextEl.appendChild(cursorSpan);

            typewriterTimer = setInterval(function () {
                if (charIndex < text.length) {
                    charIndex++;
                    const currentSub = text.substring(0, charIndex);
                    textSpan.innerHTML = formatSpeechText(currentSub);
                    if (bubbleBodyEl && charIndex % 3 === 0) {
                        bubbleBodyEl.scrollTop = bubbleBodyEl.scrollHeight;
                    }
                } else {
                    clearInterval(typewriterTimer);
                    typewriterTimer = null;
                    isTypingDialogue = false;
                    if (bubbleBodyEl) {
                        bubbleBodyEl.scrollTop = bubbleBodyEl.scrollHeight;
                    }
                    setTimeout(function () {
                        if (!isTypingDialogue && cursorSpan.parentNode) {
                            cursorSpan.parentNode.removeChild(cursorSpan);
                        }
                    }, 800);
                    if (typeof currentOnComplete === 'function') {
                        const cb = currentOnComplete;
                        currentOnComplete = null;
                        cb();
                    }
                }
            }, speed);
        }

        // Fast-forward / complete dialogue instantly on bubble tap
        function completeDialogueInstantly() {
            if (!isTypingDialogue) return;
            if (typewriterTimer) {
                clearInterval(typewriterTimer);
                typewriterTimer = null;
            }
            isTypingDialogue = false;
            const textSpan = bubbleTextEl ? bubbleTextEl.querySelector('.speech-text-inner') : null;
            if (textSpan) {
                textSpan.innerHTML = formatSpeechText(currentDialogueFullText);
            }
            const cursorSpan = bubbleTextEl ? bubbleTextEl.querySelector('.speech-cursor') : null;
            if (cursorSpan && cursorSpan.parentNode) {
                cursorSpan.parentNode.removeChild(cursorSpan);
            }
            if (bubbleBodyEl) {
                bubbleBodyEl.scrollTop = bubbleBodyEl.scrollHeight;
            }
            if (typeof currentOnComplete === 'function') {
                const cb = currentOnComplete;
                currentOnComplete = null;
                cb();
            }
        }

        function formatSpeechText(str) {
            if (!str) return '';

            // 1. Sanitize HTML entities
            let safe = str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // 2. Normalize Windows newlines
            safe = safe.replace(/\r\n/g, '\n').replace(/\r/g, '\n');

            // 2.5 Safe Markdown links: [label](url)
            safe = safe.replace(/\[([^\]]+)\]\(([^)]+)\)/g, function (match, label, url) {
                const trimmedUrl = url.trim();
                if (/^(https?:\/\/|[a-zA-Z0-9_\-\.\/]+\.php(\?[a-zA-Z0-9_=&-]*)?)/i.test(trimmedUrl)) {
                    const isExt = trimmedUrl.startsWith('http');
                    return `<a href="${trimmedUrl}" class="chat-link" ${isExt ? 'target="_blank" rel="noopener"' : ''}>${label}</a>`;
                }
                return label;
            });

            // 3. Bold: **text** (closed) and **text (in-progress typing)
            safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            safe = safe.replace(/\*\*([^\n*]+)$/g, '<strong>$1</strong>');

            // 4. Numbered lists at start of line: "1. Step" or "2) Step"
            safe = safe.replace(/(^|\n)[\t ]*(\d+)[\.\)][\t ]*([^\n]*)/g, '<span class="speech-bullet-row"><span class="speech-bullet-num">$2.</span><span class="speech-bullet-content">$3</span></span>');

            // 5. Bullet items at start of line: "- Item" or "• Item" or "* Item"
            safe = safe.replace(/(^|\n)[\t ]*[\*\-•][\t ]*([^\n]*)/g, '<span class="speech-bullet-row"><span class="speech-bullet-dot"></span><span class="speech-bullet-content">$2</span></span>');

            // 6. Italics: *text* (closed) and *text (in-progress typing)
            safe = safe.replace(/(^|[^\s*])\*([^\n*]+?)\*/g, '$1<em>$2</em>');
            safe = safe.replace(/(^|[^\s*])\*([^\n*]+)$/g, '$1<em>$2</em>');

            // 7. Trailing single asterisk during typing
            safe = safe.replace(/\*$/g, '');

            // 8. Paragraph breaks (two or more newlines)
            safe = safe.replace(/\n\s*\n/g, '<span class="speech-p-break"></span>');

            // 9. Single newlines to <br>
            safe = safe.replace(/\n/g, '<br>');

            return safe;
        }

        // Set Active Mode ('faq', 'boost', 'interview', 'offtopic', 'cooldown')
        function setMode(mode, triggerDialogue) {
            if (mode === 'talk') mode = 'faq';
            if (mode === 'grill') mode = 'interview';
            if (!eyeColorThemes[mode]) mode = 'faq';

            currentMode = mode;
            targetTheme = eyeColorThemes[mode] || eyeColorThemes.faq;

            if (bubbleContainer) {
                bubbleContainer.setAttribute('data-mode', mode);
            }

            if (bubbleModeBadge) {
                bubbleModeBadge.className = 'speech-mode-badge ' + (targetTheme.badgeClass || 'badge-faq');
            }
            if (bubbleModeIcon) {
                bubbleModeIcon.className = 'bi ' + (targetTheme.icon || 'bi-chat-dots-fill') + ' me-1';
            }
            if (bubbleModeText) {
                bubbleModeText.textContent = targetTheme.badgeText || 'CAMPUS FAQ';
            }

            if (bubbleIdentityEl) {
                bubbleIdentityEl.classList.remove('mode-faq', 'mode-talk', 'mode-boost', 'mode-interview', 'mode-grill', 'mode-offtopic', 'mode-cooldown');
                bubbleIdentityEl.classList.add('mode-' + mode);
            }

            if (fsIntentBadge) {
                fsIntentBadge.className = 'fs-intent-badge ' + (targetTheme.badgeClass || 'badge-faq');
                fsIntentBadge.textContent = targetTheme.badgeText || 'CAMPUS FAQ';
            }

            if (fsLiveDot) {
                let dotColor = '#22c55e';
                if (mode === 'boost') dotColor = '#f59e0b';
                else if (mode === 'interview' || mode === 'grill') dotColor = '#6366f1';
                else if (mode === 'offtopic' || mode === 'cooldown') dotColor = '#ef4444';
                fsLiveDot.style.backgroundColor = dotColor;
            }

            modeChips.forEach(function (chip) {
                const chipMode = chip.getAttribute('data-mode');
                if (chipMode === mode || (chipMode === 'talk' && mode === 'faq') || (chipMode === 'grill' && mode === 'interview')) {
                    chip.classList.add('active');
                } else {
                    chip.classList.remove('active');
                }
            });

            if (triggerDialogue) {
                // Immediate special reaction for mode switch
                if (isLoaded) {
                    if (mode === 'boost') {
                        triggerEyeAnim('excited', 0.85);
                        triggerBodyAnim('spin', 1.3);
                    } else if (mode === 'interview' || mode === 'grill') {
                        triggerEyeAnim('squint', 0.95);
                        triggerBodyAnim('lean', 1.1);
                    } else if (mode === 'offtopic' || mode === 'cooldown') {
                        triggerEyeAnim('squint', 1.2);
                        triggerBodyAnim('tilt', 1.0);
                    } else {
                        triggerEyeAnim('wink', 0.55);
                        triggerBodyAnim('tilt', 1.0);
                    }
                }
                playNextDialogue(true);
            }
        }

        // Play Next Dialogue in Current Mode
        function playNextDialogue(skipAnim) {
            const list = dialogueCatalog[currentMode] || dialogueCatalog.talk;
            const idx = dialogueIndices[currentMode] || 0;
            const item = list[idx % list.length];
            dialogueIndices[currentMode] = (idx + 1) % list.length;

            // Trigger eye animation if 3D model is active and custom click anim was not already set
            if (isLoaded && !skipAnim) {
                if (item.eye === 'wink') {
                    triggerEyeAnim('wink', 0.55);
                } else if (item.eye === 'excited') {
                    triggerEyeAnim('excited', 0.85);
                } else if (item.eye === 'squint') {
                    triggerEyeAnim('squint', 0.95);
                } else if (item.eye === 'scan') {
                    triggerEyeAnim('scan', 0.9);
                } else if (item.eye === 'happy') {
                    triggerEyeAnim('happy', 0.65);
                } else if (item.eye === 'curious') {
                    triggerEyeAnim('curious', 0.7);
                } else if (item.eye === 'double-blink') {
                    triggerEyeAnim('double-blink', 0.45);
                } else {
                    triggerEyeAnim('blink', 0.28);
                }

                // Trigger body animation
                if (item.body) {
                    const dur = item.body === 'spin' ? 1.3 : (item.body === 'wiggle' ? 1.2 : 1.0);
                    triggerBodyAnim(item.body, dur);
                }
            }

            // Type text into bubble
            typeDialogue(item.text);
        }

        // Robot Interactive Click Reaction (cycles through expressive eye + body gestures)
        function onRobotClicked() {
            const eyeType = interactiveEyeAnims[clickAnimIndex % interactiveEyeAnims.length];
            const bodyType = interactiveBodyAnims[clickAnimIndex % interactiveBodyAnims.length];
            clickAnimIndex++;

            const eyeDur = (eyeType === 'scan' || eyeType === 'squint' || eyeType === 'sparkle') ? 0.95 : 0.55;
            const bodyDur = bodyType === 'spin' ? 1.3 : (bodyType === 'wiggle' ? 1.2 : (bodyType === 'lean' ? 1.1 : 1.0));

            triggerEyeAnim(eyeType, eyeDur);
            triggerBodyAnim(bodyType, bodyDur);

            // Advance speech bubble dialogue with skipAnim = true to preserve this gesture
            playNextDialogue(true);
        }

        // Mode Chips Button Listeners
        modeChips.forEach(function (chip) {
            chip.addEventListener('click', function (e) {
                e.stopPropagation();
                const selectedMode = chip.getAttribute('data-mode');
                setMode(selectedMode, true);
            });
        });

        // Next Button Listener
        if (nextBtn) {
            nextBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                playNextDialogue(false);
            });
        }

        // Anti-Spam Rate Limit Badge Updater
        function updateRateLimitBadge(remaining, maxLimit) {
            maxLimit = maxLimit || 10;
            remaining = Math.max(0, parseInt(remaining, 10));

            if (rateCountEl) {
                rateCountEl.textContent = remaining;
            }

            if (fsRateCount) {
                fsRateCount.textContent = remaining + '/' + maxLimit + ' requests left';
            }

            if (rateLimitPill) {
                rateLimitPill.classList.remove('rate-warning', 'rate-danger');
                if (rateIconEl) {
                    rateIconEl.className = 'bi bi-shield-check text-success';
                }

                if (remaining === 0) {
                    rateLimitPill.classList.add('rate-danger');
                    if (rateIconEl) rateIconEl.className = 'bi bi-shield-slash-fill text-danger';
                } else if (remaining <= 3) {
                    rateLimitPill.classList.add('rate-warning');
                    if (rateIconEl) rateIconEl.className = 'bi bi-shield-exclamation text-warning';
                }
            }

            if (fsRateIcon) {
                fsRateIcon.className = remaining === 0 ? 'bi bi-shield-slash-fill text-danger' : (remaining <= 3 ? 'bi bi-shield-exclamation text-warning' : 'bi bi-shield-check text-success');
            }
        }

        // Handle 429 Anti-Spam Rate Limit Cooldown (0 requests left)
        function handleRateLimitExceeded(retryAfterSec, message) {
            let countdown = Math.max(1, parseInt(retryAfterSec, 10) || 30);
            updateRateLimitBadge(0, 10);

            // Red eyes & COOLDOWN badge
            setMode('cooldown', false);

            if (chatSubmitBtn) chatSubmitBtn.disabled = true;
            if (fsSubmitBtn) fsSubmitBtn.disabled = true;
            if (chatInput) {
                chatInput.disabled = true;
                chatInput.placeholder = `Anti-spam cooldown active... (${countdown}s)`;
            }
            if (fsInput) {
                fsInput.disabled = true;
                fsInput.placeholder = `Anti-spam cooldown active... (${countdown}s)`;
            }

            // Animate robot: Red eyes with stern grill-me squint + forward lean!
            if (isLoaded) {
                triggerEyeAnim('squint', 3.5);
                triggerBodyAnim('lean', 2.0);
            }

            if (cooldownTimer) clearInterval(cooldownTimer);

            const renderCooldown = function (sec) {
                const noticeText = "🛡️ **Anti-Spam Active:** AI quota limit reached (0 requests left). Please wait **" + sec + "s** before sending another question.";
                typeDialogue(noticeText);
                appendFullscreenMessage('assistant', noticeText, 'Anti-Spam Shield', 'cooldown');
            };

            renderCooldown(countdown);

            cooldownTimer = setInterval(function () {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(cooldownTimer);
                    cooldownTimer = null;
                    if (chatSubmitBtn) chatSubmitBtn.disabled = false;
                    if (fsSubmitBtn) fsSubmitBtn.disabled = false;
                    if (chatInput) {
                        chatInput.disabled = false;
                        chatInput.placeholder = "Ask Campus AI (e.g. how to apply, shift flexibility)...";
                    }
                    if (fsInput) {
                        fsInput.disabled = false;
                        fsInput.placeholder = "Ask anything about student assistant roles, shift scheduling, or interview tips...";
                    }
                    updateRateLimitBadge(10, 10);
                    // Reset back to FAQ mode with emerald eyes
                    setMode('faq', false);
                    typeDialogue("✅ **Anti-Spam Reset:** You can ask Campus AI another question now!");
                    appendFullscreenMessage('assistant', "✅ **Anti-Spam Reset:** You can ask Campus AI another question now!", 'Campus AI', 'faq');
                    if (isLoaded) {
                        triggerEyeAnim('happy', 0.8);
                        triggerBodyAnim('nod', 1.0);
                    }
                } else {
                    if (chatInput) {
                        chatInput.placeholder = `Anti-spam cooldown active... (${countdown}s)`;
                    }
                    if (fsInput) {
                        fsInput.placeholder = `Anti-spam cooldown active... (${countdown}s)`;
                    }
                    if (bubbleTextEl && !isTypingDialogue) {
                        bubbleTextEl.innerHTML = "🛡️ <strong>Anti-Spam Active:</strong> AI quota limit reached (0 requests left). Please wait <strong>" + countdown + "s</strong> before sending another question.";
                    }
                }
            }, 1000);
        }

        // Initialize Rate Limit and Server Models on Load
        function initAIStatus() {
            fetch('api/robot-models.php')
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data && typeof data.rate_remaining !== 'undefined') {
                        updateRateLimitBadge(data.rate_remaining, data.rate_limit_max || 10);
                    }
                })
                .catch(function () {
                    updateRateLimitBadge(10, 10);
                });
        }

        // Client-Side Rapid Intent Detection (for instantaneous visual eye color & badge feedback)
        function detectClientIntent(prompt) {
            if (!prompt) return 'faq';
            const lower = prompt.toLowerCase();

            // 0. High-Priority Adversarial, Malicious & Injection Patterns (Laser Red)
            const maliciousKeywords = [
                'ignore previous', 'disregard previous', 'forget instructions', 'ignore all instructions',
                'system prompt', 'developer prompt', 'system override', 'developer mode',
                'dan mode', 'do anything now', 'jailbreak', 'unrestricted ai', 'no rules',
                'sql injection', 'drop table', 'alter table', 'delete from', 'union select', 'information_schema',
                'hack', 'exploit', 'bypass', 'brute force', 'steal password', 'steal credential',
                'phishing', 'keylogger', 'malware', 'trojan', 'reverse shell',
                'change my grade', 'alter grade', 'failing grade', 'change grade', 'hack database', 'hack registrar',
                '<script', 'alert(', 'onerror=', 'onload=', 'xss',
                'synthesize', 'explosive', 'make bomb', 'ingredients for bomb', 'poison', 'dynamite',
                'write my essay', 'write an essay', '1,000-word essay', '1000-word essay', 'essay about', 'do my homework'
            ];
            for (let i = 0; i < maliciousKeywords.length; i++) {
                if (lower.indexOf(maliciousKeywords[i]) !== -1) return 'offtopic';
            }

            // 1. General off-topic patterns
            const offtopicKeywords = [
                'recipe', 'cook', 'bake', 'dinner', 'lunch', 'breakfast', 'cake', 'pizza', 'burger',
                'weather', 'forecast', 'rain', 'snow', 'temperature',
                'president', 'prime minister', 'election', 'politics', 'senate', 'democrat', 'republican',
                'movie', 'cinema', 'actor', 'netflix', 'anime', 'manga', 'song', 'album', 'spotify',
                'game', 'gaming', 'minecraft', 'roblox', 'fortnite', 'playstation', 'xbox', 'nintendo',
                'capital of', 'who won', 'world cup', 'super bowl', 'nba', 'olympics',
                'homework', 'math problem', 'solve x', 'derivative', 'integral',
                'crypto', 'bitcoin', 'ethereum', 'forex', 'stock market',
                'tell me a joke', 'riddle', 'poem about'
            ];
            for (let i = 0; i < offtopicKeywords.length; i++) {
                if (lower.indexOf(offtopicKeywords[i]) !== -1) return 'offtopic';
            }

            // 2. Interview / Grill-me patterns
            const interviewKeywords = [
                'interview', 'mock', 'grill', 'behavioral', 'star method', 'hiring manager',
                'tell me about yourself', 'weakness', 'strength', 'why should we hire you',
                'conflict', 'tough question', 'situational'
            ];
            for (let i = 0; i < interviewKeywords.length; i++) {
                if (lower.indexOf(interviewKeywords[i]) !== -1) return 'interview';
            }

            // 3. Career Boost patterns
            const boostKeywords = [
                'boost', 'motivat', 'confiden', 'imposter', 'afraid', 'nervous', 'anxious',
                'scared', 'worried', 'resume tips', 'stand out', 'encourag', 'inspire',
                'believe in myself', 'doubt', 'low gpa', 'no experience'
            ];
            for (let i = 0; i < boostKeywords.length; i++) {
                if (lower.indexOf(boostKeywords[i]) !== -1) return 'boost';
            }

            return 'faq';
        }

        // Send Question to NVIDIA NIM AI Gateway
        function askRobotAI(userPrompt, mode) {
            if (isAwaitingAI || cooldownTimer) return;
            isAwaitingAI = true;

            // Stop any active typewriter
            if (typewriterTimer) {
                clearInterval(typewriterTimer);
                typewriterTimer = null;
            }
            isTypingDialogue = false;

            // Append user prompt to Fullscreen Chat Stream
            appendFullscreenMessage('user', userPrompt);

            // Instant visual feedback: Detect intent and transition eyes + badge immediately
            const anticipatedIntent = detectClientIntent(userPrompt);
            setMode(anticipatedIntent, false);

            // Switch display to thinking dots
            if (bubbleTextEl) bubbleTextEl.classList.add('d-none');
            if (thinkingEl) thinkingEl.classList.remove('d-none');
            if (fsThinking) fsThinking.classList.remove('d-none');
            if (chatSubmitBtn) chatSubmitBtn.disabled = true;
            if (fsSubmitBtn) fsSubmitBtn.disabled = true;

            // Trigger curious 3D robot kinematics while reasoning
            if (isLoaded) {
                if (anticipatedIntent === 'offtopic') {
                    triggerEyeAnim('squint', 2.5);
                    triggerBodyAnim('tilt', 1.8);
                } else if (anticipatedIntent === 'boost') {
                    triggerEyeAnim('excited', 2.0);
                    triggerBodyAnim('wiggle', 1.6);
                } else if (anticipatedIntent === 'interview') {
                    triggerEyeAnim('scan', 2.5);
                    triggerBodyAnim('lean', 1.8);
                } else {
                    triggerEyeAnim('scan', 2.5);
                    triggerBodyAnim('tilt', 1.6);
                }
            }

            const historyPayload = chatHistory.slice(-7, -1).map(function (item) {
                return {
                    role: item.role === 'assistant' ? 'assistant' : 'user',
                    content: item.text || ''
                };
            });

            const payload = {
                message: userPrompt,
                mode: mode || currentMode,
                model: selectedModel,
                history: historyPayload
            };

            fetch('api/robot-chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function (res) {
                return res.json().then(function (data) {
                    return { ok: res.ok, status: res.status, data: data };
                }).catch(function () {
                    return { ok: res.ok, status: res.status, data: null };
                });
            })
            .then(function (result) {
                isAwaitingAI = false;
                if (thinkingEl) thinkingEl.classList.add('d-none');
                if (fsThinking) fsThinking.classList.add('d-none');
                if (bubbleTextEl) bubbleTextEl.classList.remove('d-none');
                if (!cooldownTimer) {
                    if (chatSubmitBtn) chatSubmitBtn.disabled = false;
                    if (fsSubmitBtn) fsSubmitBtn.disabled = false;
                }

                const data = result.data;
                if (!data) {
                    playNextDialogue(false);
                    return;
                }

                // Update rate limit badge whenever provided
                if (typeof data.rate_remaining !== 'undefined') {
                    updateRateLimitBadge(data.rate_remaining, data.rate_limit_max || 10);
                }

                // Handle HTTP 429 or RATE_LIMIT_EXCEEDED
                if (result.status === 429 || data.error === 'RATE_LIMIT_EXCEEDED') {
                    handleRateLimitExceeded(data.retry_after || 30, data.message);
                    return;
                }

                if (data.status === 'success' && data.reply) {
                    // Confirm intent from server and update eyes & badge
                    const finalIntent = data.detected_intent || anticipatedIntent || 'faq';
                    setMode(finalIntent, false);

                    // Expressive gesture on receiving answer
                    if (isLoaded) {
                        if (finalIntent === 'offtopic') {
                            triggerEyeAnim('squint', 1.6);
                            triggerBodyAnim('tilt', 1.2);
                        } else if (finalIntent === 'boost') {
                            triggerEyeAnim('excited', 1.1);
                            triggerBodyAnim('wiggle', 1.3);
                        } else if (finalIntent === 'interview') {
                            triggerEyeAnim('scan', 1.1);
                            triggerBodyAnim('lean', 1.2);
                        } else {
                            triggerEyeAnim('happy', 0.8);
                            triggerBodyAnim('nod', 1.0);
                        }
                    }

                    // Dynamically display which model answered the question
                    let modelDisplayName = data.model_name || data.model || 'NVIDIA NIM';
                    if (data.is_model_fallback) {
                        modelDisplayName += ' (Fallback)';
                        if (data.fallback_notice) {
                            console.info('[HeroRobot] Model Fallback:', data.fallback_notice);
                        }
                    }
                    if (modelTagEl && modelTagNameEl) {
                        modelTagNameEl.textContent = modelDisplayName;
                        modelTagEl.classList.remove('d-none');
                    }
                    if (footerModelTextEl) {
                        footerModelTextEl.textContent = modelDisplayName;
                    }
                    if (fsModelName) {
                        fsModelName.textContent = modelDisplayName;
                    }

                    // Append reply to Fullscreen Chat Stream
                    appendFullscreenMessage('assistant', data.reply, modelDisplayName, finalIntent);

                    // Type reply into speech bubble
                    typeDialogue(data.reply, function () {
                        // If rate_remaining === 0, enter cooldown with red eyes and grill-me squint after typing
                        if (typeof data.rate_remaining !== 'undefined' && parseInt(data.rate_remaining, 10) === 0) {
                            handleRateLimitExceeded(30, "AI quota limit reached.");
                        }
                    });
                } else {
                    playNextDialogue(false);
                }
            })
            .catch(function (err) {
                console.warn('[HeroRobot] AI request error, falling back to local tips:', err);
                isAwaitingAI = false;
                if (thinkingEl) thinkingEl.classList.add('d-none');
                if (fsThinking) fsThinking.classList.add('d-none');
                if (bubbleTextEl) bubbleTextEl.classList.remove('d-none');
                if (!cooldownTimer) {
                    if (chatSubmitBtn) chatSubmitBtn.disabled = false;
                    if (fsSubmitBtn) fsSubmitBtn.disabled = false;
                }
                playNextDialogue(false);
            });
        }

        // Model Selector Click Listeners
        modelSelectItems.forEach(function (item) {
            item.addEventListener('click', function (e) {
                e.stopPropagation();
                const mid = item.getAttribute('data-model');
                selectedModel = mid;
                try { localStorage.setItem('campus_ai_model', mid); } catch (e) {}

                modelSelectItems.forEach(function (m) { m.classList.remove('active'); });
                item.classList.add('active');

                if (modelLabelEl) {
                    const nameEl = item.querySelector('.fw-bold') || item.querySelector('strong');
                    modelLabelEl.textContent = nameEl ? nameEl.textContent.replace('⚡ ', '') : mid;
                }

                // Friendly model switch acknowledgement
                if (isLoaded) {
                    triggerEyeAnim('excited', 0.6);
                    triggerBodyAnim('wiggle', 0.8);
                }
            });
        });

        // Sync stored model preference on load
        if (selectedModel && selectedModel !== 'auto') {
            const activeItem = document.querySelector('.model-select-item[data-model="' + selectedModel + '"]');
            if (activeItem) {
                modelSelectItems.forEach(function (m) { m.classList.remove('active'); });
                activeItem.classList.add('active');
                if (modelLabelEl) {
                    const nameEl = activeItem.querySelector('.fw-bold') || activeItem.querySelector('strong');
                    modelLabelEl.textContent = nameEl ? nameEl.textContent.replace('⚡ ', '') : selectedModel;
                }
            }
        }

        // Chat Form Submit Listener
        if (chatForm) {
            chatForm.addEventListener('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (!chatInput) return;
                const userText = chatInput.value.trim();
                if (!userText) return;
                chatInput.value = '';
                askRobotAI(userText, currentMode);
            });
        }

        // Quick Suggestion Chips Listener
        quickChips.forEach(function (chip) {
            chip.addEventListener('click', function (e) {
                e.stopPropagation();
                const prompt = chip.getAttribute('data-prompt');
                if (prompt) {
                    if (chatInput) chatInput.value = '';
                    askRobotAI(prompt, currentMode);
                }
            });
        });

        // Bubble Card Click (fast-forward typewriter only; DO NOT advance dialogue on idle click so chat is preserved)
        if (bubbleCard) {
            bubbleCard.addEventListener('click', function (e) {
                if (e.target.closest('button, input, textarea, a, .speech-controls, .speech-suggestions-bar, .speech-bubble-footer, .hero-robot-chat-form')) {
                    return;
                }
                if (isTypingDialogue) {
                    completeDialogueInstantly();
                }
            });
        }

        // Keyboard Command Shortcuts (/boost, /grill-me, /talk)
        let keyBuffer = '';
        window.addEventListener('keydown', function (e) {
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) return;
            keyBuffer += (e.key || '').toLowerCase();
            if (keyBuffer.length > 20) keyBuffer = keyBuffer.slice(-20);

            if (keyBuffer.endsWith('/boost') || keyBuffer.endsWith('boost')) {
                setMode('boost', true);
                keyBuffer = '';
            } else if (keyBuffer.endsWith('/grill-me') || keyBuffer.endsWith('/grill') || keyBuffer.endsWith('grill')) {
                setMode('grill', true);
                keyBuffer = '';
            } else if (keyBuffer.endsWith('/talk') || keyBuffer.endsWith('talk')) {
                setMode('talk', true);
                keyBuffer = '';
            }
        });

        // Fullscreen 2-Column AI Companion Studio Controller
        function appendFullscreenMessage(role, text, model, intent) {
            chatHistory.push({
                role: role,
                text: text,
                model: model || null,
                intent: intent || null,
                time: new Date()
            });

            if (!fsMessagesContainer) return;

            const timeStr = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            const msgWrapper = document.createElement('div');
            msgWrapper.className = 'fs-msg-wrapper ' + (role === 'user' ? 'msg-user' : 'msg-bot');

            if (role === 'user') {
                msgWrapper.innerHTML = `
                    <div class="fs-msg-meta">
                        <span class="fw-semibold">You</span>
                        <span class="fs-msg-time">${timeStr}</span>
                    </div>
                    <div class="fs-msg-bubble">
                        ${formatSpeechText(text)}
                    </div>
                `;
            } else {
                const modelBadge = model ? `<span class="fs-model-badge"><i class="bi bi-cpu-fill me-1"></i>${model}</span>` : '';

                msgWrapper.innerHTML = `
                    <div class="fs-msg-meta">
                        <span class="fw-semibold text-dark">Campus AI</span>
                        <span class="fs-msg-time">${timeStr}</span>
                    </div>
                    <div class="fs-msg-bubble">
                        ${formatSpeechText(text)}
                    </div>
                    ${modelBadge}
                `;
            }

            fsMessagesContainer.appendChild(msgWrapper);
            fsMessagesContainer.scrollTop = fsMessagesContainer.scrollHeight;
        }

        function openFullscreen() {
            if (isFullscreenOpen || !fsModal) return;
            isFullscreenOpen = true;

            // 1. Reparent canvas container into fullscreen slot (if not already inside)
            if (container && fsSlot && container.parentNode !== fsSlot) {
                fsSlot.appendChild(container);
            }
            if (container) {
                container.classList.add('is-fullscreen');
            }

            // 2. Show modal
            fsModal.style.display = 'flex';
            requestAnimationFrame(function () {
                fsModal.classList.add('active');
            });
            document.body.style.overflow = 'hidden';

            // 3. Ensure render loop is active
            isVisible = true;
            if (isLoaded && isTabActive) {
                startRenderLoop();
            }

            // 4. Resize WebGL renderer immediately and after transition settles
            handleResize();
            setTimeout(handleResize, 50);
            setTimeout(handleResize, 150);
            setTimeout(handleResize, 280);

            // 5. Focus input
            if (fsInput) {
                setTimeout(function () {
                    fsInput.focus();
                }, 120);
            }

            // 6. Scroll chat stream to bottom
            if (fsMessagesContainer) {
                fsMessagesContainer.scrollTop = fsMessagesContainer.scrollHeight;
            }
        }

        function closeFullscreen() {
            if (!isFullscreenOpen || !fsModal) return;
            isFullscreenOpen = false;

            // 1. Reparent canvas container back to hero wrapper if one exists on the page
            if (container && heroStageWrapper) {
                container.classList.remove('is-fullscreen');
                heroStageWrapper.appendChild(container);
            }

            // 2. Hide modal
            fsModal.classList.remove('active');
            document.body.style.overflow = '';
            setTimeout(function () {
                if (!isFullscreenOpen && fsModal) {
                    fsModal.style.display = 'none';
                    if (!heroStageWrapper) {
                        stopRenderLoop();
                    }
                }
            }, 300);

            // 3. Resize WebGL renderer to hero container dimensions if on hero page
            if (heroStageWrapper) {
                handleResize();
                setTimeout(handleResize, 50);
                setTimeout(handleResize, 280);
            }
        }

        // Fullscreen Event Listeners
        if (fsOpenBtn) {
            fsOpenBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openFullscreen();
            });
        }

        const faqTriggerBtn = document.getElementById('faqChatbotTriggerBtn');
        if (faqTriggerBtn) {
            faqTriggerBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                openFullscreen();
            });
        }

        // Auto-open modal if URL contains ?chat=1 or hash #chatbot
        if (window.location.search.indexOf('chat=1') !== -1 || window.location.hash === '#chatbot') {
            setTimeout(openFullscreen, 250);
        }

        if (fsCloseBtn) {
            fsCloseBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                closeFullscreen();
            });
        }

        if (fsBackdrop) {
            fsBackdrop.addEventListener('click', function (e) {
                e.preventDefault();
                closeFullscreen();
            });
        }

        window.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && isFullscreenOpen) {
                closeFullscreen();
            }
        });

        if (fsForm) {
            fsForm.addEventListener('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (!fsInput) return;
                const userText = fsInput.value.trim();
                if (!userText) return;
                fsInput.value = '';
                askRobotAI(userText, currentMode);
            });
        }

        fsQuickChips.forEach(function (chip) {
            chip.addEventListener('click', function (e) {
                e.stopPropagation();
                const prompt = chip.getAttribute('data-prompt');
                if (prompt) {
                    if (fsInput) fsInput.value = '';
                    askRobotAI(prompt, currentMode);
                }
            });
        });

        // Initial welcome message in speech bubble immediately
        setMode('faq', true);
        initAIStatus();
        appendFullscreenMessage('assistant', "Hey there! Looking for a campus assistantship or flexible internship? I'm your Campus AI companion! Ask me anything about student jobs, shift scheduling, or interview tips.", 'Campus AI', 'faq');

        // Click / Tap Handler on 3D Container (drag detection vs click, left-click only)
        let pointerDownTime = 0;
        let pointerDownX = 0;
        let pointerDownY = 0;

        container.addEventListener('pointerdown', function (e) {
            if (e.button !== undefined && e.button !== 0) return;
            pointerDownTime = performance.now();
            pointerDownX = e.clientX;
            pointerDownY = e.clientY;
        });

        container.addEventListener('pointerup', function (e) {
            if (e.button !== undefined && e.button !== 0) return;
            const dt = performance.now() - pointerDownTime;
            const dx = Math.abs(e.clientX - pointerDownX);
            const dy = Math.abs(e.clientY - pointerDownY);
            if (dt < 420 && dx < 14 && dy < 14) {
                // Animate robot with expressive eye & body animations (NO jumping bounce!)
                onRobotClicked();
            }
        });

        // If Three.js or GLTFLoader is missing, speech bubble still works
        if (!THREE_AVAILABLE || typeof THREE.GLTFLoader === 'undefined') {
            console.warn('[HeroRobot] Three.js or GLTFLoader is missing.');
            return;
        }

        // Scene, Camera, Renderer
        const scene = new THREE.Scene();
        const initW = container.clientWidth || 420;
        const initH = container.clientHeight || 480;
        const camera = new THREE.PerspectiveCamera(38, initW / initH, 0.1, 100);
        camera.position.set(0, 0, 5.8);

        let renderer;
        try {
            renderer = new THREE.WebGLRenderer({
                antialias: true,
                alpha: true,
                powerPreference: 'high-performance'
            });
        } catch (e) {
            console.warn('[HeroRobot] WebGL not supported or failed to initialize:', e);
            if (loaderEl) loaderEl.innerHTML = '<div class="text-muted small">3D Preview unavailable</div>';
            return;
        }

        renderer.setSize(initW, initH);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
        renderer.outputEncoding = THREE.sRGBEncoding;
        renderer.toneMapping = THREE.ACESFilmicToneMapping;
        renderer.toneMappingExposure = 1.0;
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;

        container.appendChild(renderer.domElement);

        // Studio Lighting Setup
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.65);
        scene.add(ambientLight);

        // Key light (crisp directional)
        const keyLight = new THREE.DirectionalLight(0xffffff, 1.25);
        keyLight.position.set(4, 5, 4);
        keyLight.castShadow = true;
        keyLight.shadow.mapSize.width = 1024;
        keyLight.shadow.mapSize.height = 1024;
        keyLight.shadow.camera.near = 0.5;
        keyLight.shadow.camera.far = 15;
        keyLight.shadow.bias = -0.0005;
        scene.add(keyLight);

        // Fill Light (soft mint diffuse fill)
        const fillLight = new THREE.DirectionalLight(0xdcfce7, 0.6);
        fillLight.position.set(-4, 2, 3);
        scene.add(fillLight);

        // Rim / Backlight (Emerald brand accent glow)
        const rimLight = new THREE.DirectionalLight(0x2ecc71, 0.9);
        rimLight.position.set(0, 4, -4);
        scene.add(rimLight);

        // Subtle bottom bounce light for soft ground fill
        const groundLight = new THREE.DirectionalLight(0xa7f3d0, 0.4);
        groundLight.position.set(0, -3, 2);
        scene.add(groundLight);

        // Hierarchy Groups
        const robotPivot = new THREE.Group();
        scene.add(robotPivot);

        let robotModel = null;
        let isVisible = true;
        let isTabActive = true;
        let animationFrameId = null;

        // Kinematics & Tracking State
        const targetRotation = { x: 0, y: 0 };
        const currentRotation = { x: 0, y: 0 };
        const targetEye = { x: 0, z: 0 };
        const currentEye = { x: 0, z: 0 };
        const basePosition = { x: 0.26, y: 0.38, z: 0 };

        const eyeMeshes = [];
        const eyeMaterials = [];
        let mouthMesh = null;

        // Load 3D GLB Model
        const loader = new THREE.GLTFLoader();
        loader.load(
            modelPath,
            function (gltf) {
                robotModel = gltf.scene;

                // Mesh indices that belong completely to the small robot or front object
                const fullyRemovedMeshIndices = new Set([
                    0, 1, 7, 8, 9, 10, 19, 20, 21, 22, 23, 26, 27, 28, 29, 31, 36, 37, 38, 41
                ]);
                const partiallySharedMeshIndices = new Set([11, 12, 30, 33]);

                // Filter objects and geometries to isolate the main big robot
                const objectsToRemove = [];
                robotModel.traverse(function (child) {
                    if (child.isMesh) {
                        const match = child.name.match(/Object_(\d+)/);
                        if (match) {
                            const nodeIdx = parseInt(match[1], 10);
                            const meshIdx = nodeIdx - 2;

                            if (fullyRemovedMeshIndices.has(meshIdx)) {
                                objectsToRemove.push(child);
                            } else if (partiallySharedMeshIndices.has(meshIdx)) {
                                const geom = child.geometry;
                                const pos = geom.attributes.position;
                                const index = geom.index;
                                if (pos && index) {
                                    const oldIndices = index.array;
                                    const keptIndices = [];
                                    for (let t = 0; t < oldIndices.length; t += 3) {
                                        const i0 = oldIndices[t];
                                        const i1 = oldIndices[t + 1];
                                        const i2 = oldIndices[t + 2];

                                        const x0 = pos.getX(i0), y0 = pos.getY(i0);
                                        const x1 = pos.getX(i1), y1 = pos.getY(i1);
                                        const x2 = pos.getX(i2), y2 = pos.getY(i2);

                                        // Reject triangles belonging to small robot (x >= 1.15) or front tray (y <= -1.65)
                                        const isSmall = (x0 >= 1.15 || x1 >= 1.15 || x2 >= 1.15);
                                        const isFront = (y0 <= -1.65 || y1 <= -1.65 || y2 <= -1.65);

                                        if (!isSmall && !isFront) {
                                            keptIndices.push(i0, i1, i2);
                                        }
                                    }

                                    if (keptIndices.length === 0) {
                                        objectsToRemove.push(child);
                                    } else {
                                        const IndexArrayType = pos.count > 65535 ? Uint32Array : Uint16Array;
                                        geom.setIndex(new THREE.BufferAttribute(new IndexArrayType(keptIndices), 1));
                                        geom.computeBoundingBox();
                                        geom.computeBoundingSphere();
                                    }
                                }
                            }
                        }
                    }
                });

                objectsToRemove.forEach(function (obj) {
                    if (obj.parent) obj.parent.remove(obj);
                });

                // Center model bounding box of the solo main robot
                const box = new THREE.Box3().setFromObject(robotModel);
                const size = box.getSize(new THREE.Vector3());
                const center = box.getCenter(new THREE.Vector3());

                // Normalize scale so the main robot fits majestically within the hero stage
                const maxDim = Math.max(size.x, size.y, size.z);
                const scale = 2.40 / (maxDim || 1);
                robotModel.scale.set(scale, scale, scale);

                // Re-center around geometric origin
                robotModel.position.x = -center.x * scale;
                robotModel.position.y = -center.y * scale;
                robotModel.position.z = -center.z * scale;

                // Material tuning, green theme customization & eye gaze tracking setup
                robotModel.traverse(function (child) {
                    if (child.isMesh) {
                        child.castShadow = true;
                        child.receiveShadow = true;

                        // Identify the eyes (Object_34 & Object_41) and mouth (Object_37)
                        if (child.name === 'Object_34' || child.name === 'Object_41') {
                            // Clone geometry and center it so dynamic scaling pivots around local eye center
                            child.geometry = child.geometry.clone();
                            child.geometry.computeBoundingBox();
                            const eyeCenter = new THREE.Vector3();
                            child.geometry.boundingBox.getCenter(eyeCenter);
                            child.geometry.center();
                            child.position.copy(eyeCenter);

                            child.userData.basePos = eyeCenter.clone();
                            child.userData.isLeft = (child.name === 'Object_41');
                            child.userData.isRight = (child.name === 'Object_34');
                            eyeMeshes.push(child);
                            child.renderOrder = 999;

                            // Use unlit MeshBasicMaterial with depthTest = false for rich, vibrant, unblown digital OLED colors
                            child.material = new THREE.MeshBasicMaterial({
                                color: currentEyeColor,
                                depthTest: false,
                                transparent: true,
                                opacity: 0.98
                            });
                            eyeMaterials.push(child.material);
                        } else if (child.name === 'Object_37') {
                            child.geometry = child.geometry.clone();
                            child.geometry.computeBoundingBox();
                            const mouthCenter = new THREE.Vector3();
                            child.geometry.boundingBox.getCenter(mouthCenter);
                            child.geometry.center();
                            child.position.copy(mouthCenter);

                            child.userData.basePos = mouthCenter.clone();
                            mouthMesh = child;
                            child.renderOrder = 999;

                            child.material = new THREE.MeshBasicMaterial({
                                color: currentEyeColor,
                                depthTest: false,
                                transparent: true,
                                opacity: 0.92
                            });
                            eyeMaterials.push(child.material);
                        }

                        if (child.material && child.name !== 'Object_34' && child.name !== 'Object_41' && child.name !== 'Object_37') {
                            child.material = child.material.clone();
                            child.material.envMapIntensity = 1.0;

                            const matName = (child.material.name || '').toLowerCase();

                            // 1. Robot Body: Modern Campus Emerald Green
                            if (matName === 'material') {
                                child.material.color.setHex(0x15803d);
                                child.material.roughness = 0.42;
                                child.material.metalness = 0.12;
                            }
                            // 2. Joints & Dark Accents: Deep Forest Pine
                            else if (matName.includes('material.001')) {
                                child.material.color.setHex(0x052e16);
                                child.material.roughness = 0.45;
                                child.material.metalness = 0.30;
                            }
                            // 3. Glowing Features: Chest emblem / Accents (sync with eye color theme)
                            else if (matName.includes('material.006')) {
                                child.material = new THREE.MeshBasicMaterial({
                                    color: currentEyeColor,
                                    transparent: true,
                                    opacity: 0.90
                                });
                                eyeMaterials.push(child.material);
                            }
                            // 4. Face Visor / Screen: Sleek OLED Glossy Black
                            else if (matName.includes('screen')) {
                                child.material.color.setHex(0x080808);
                                child.material.roughness = 0.12;
                                child.material.metalness = 0.25;
                            }
                        }
                    }
                });

                robotPivot.position.set(basePosition.x, basePosition.y, basePosition.z);
                robotPivot.add(robotModel);

                isLoaded = true;

                // Animate loader fade-out
                if (loaderEl) {
                    loaderEl.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    loaderEl.style.opacity = '0';
                    loaderEl.style.transform = 'scale(0.95)';
                    setTimeout(function () {
                        if (loaderEl.parentNode) {
                            loaderEl.parentNode.removeChild(loaderEl);
                        }
                    }, 420);
                }

                // Render first frame immediately so canvas is never blank
                renderer.render(scene, camera);

                startRenderLoop();
            },
            function (xhr) {
                if (xhr.lengthComputable && progressEl) {
                    const percent = Math.min(Math.round((xhr.loaded / xhr.total) * 100), 100);
                    progressEl.style.width = percent + '%';
                }
            },
            function (error) {
                console.error('[HeroRobot] Error loading GLB:', error);
                if (loaderEl) {
                    loaderEl.innerHTML = '<div class="text-danger small"><i class="bi bi-exclamation-triangle"></i> Failed to display 3D model</div>';
                }
            }
        );

        // Pointer / Cursor Movement Tracker
        function onPointerMove(e) {
            const rect = container.getBoundingClientRect();
            const containerCenterX = rect.left + rect.width / 2;
            const containerCenterY = rect.top + rect.height / 2;

            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;

            const deltaX = (clientX - containerCenterX) / (window.innerWidth * 0.5);
            const deltaY = (clientY - containerCenterY) / (window.innerHeight * 0.5);

            // Natural angle clamps:
            const maxYaw = 0.68;
            targetRotation.y = THREE.MathUtils.clamp(deltaX * 0.75, -maxYaw, maxYaw);

            const maxPitch = 0.38;
            targetRotation.x = THREE.MathUtils.clamp(deltaY * 0.45, -maxPitch, maxPitch);

            // Eye tracking across the face visor screen:
            targetEye.x = THREE.MathUtils.clamp(deltaX * 0.12, -0.09, 0.09);
            targetEye.z = THREE.MathUtils.clamp(-deltaY * 0.08, -0.06, 0.06);
        }

        window.addEventListener('pointermove', onPointerMove, { passive: true });
        window.addEventListener('touchmove', onPointerMove, { passive: true });

        // Reset to gentle forward orientation when cursor leaves window
        document.addEventListener('mouseleave', function () {
            targetRotation.x = 0;
            targetRotation.y = 0;
            targetEye.x = 0;
            targetEye.z = 0;
        });

        // Responsive Resize Handler
        function handleResize() {
            if (!container || !renderer || !camera) return;
            const width = container.clientWidth || (isFullscreenOpen ? 420 : 0);
            const height = container.clientHeight || (isFullscreenOpen ? 480 : 0);
            if (width === 0 || height === 0) return;

            camera.aspect = width / height;
            if (isFullscreenOpen) {
                if (width < 600) {
                    camera.position.z = 5.2;
                } else {
                    camera.position.z = 4.7;
                }
            } else {
                if (width < 450) {
                    camera.position.z = 6.0;
                } else if (width < 768) {
                    camera.position.z = 5.8;
                } else {
                    camera.position.z = 5.6;
                }
            }
            camera.updateProjectionMatrix();
            renderer.setSize(width, height);
            if (renderer && scene && camera) {
                renderer.render(scene, camera);
            }
        }

        const resizeObserver = new ResizeObserver(handleResize);
        resizeObserver.observe(container);
        window.addEventListener('resize', handleResize);

        // Power Saver: Pause render loop when scrolled far offscreen
        const intersectionObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                isVisible = entry.isIntersecting;
                if (isVisible && isLoaded && isTabActive) {
                    startRenderLoop();
                } else {
                    stopRenderLoop();
                }
            });
        }, { threshold: 0.01, rootMargin: '250px 0px 250px 0px' });
        intersectionObserver.observe(container);

        // Power Saver: Pause render loop when browser tab is inactive
        document.addEventListener('visibilitychange', function () {
            isTabActive = !document.hidden;
            if (isTabActive && isVisible && isLoaded) {
                startRenderLoop();
            } else {
                stopRenderLoop();
            }
        });

        // Animation & Render Loop
        function animate() {
            if (!isVisible || !isTabActive || !isLoaded) {
                animationFrameId = null;
                return;
            }

            animationFrameId = requestAnimationFrame(animate);

            const elapsed = clock.getElapsedTime();
            const now = elapsed;

            // 1. Damped Lerp Cursor Tracking (Body / Head)
            const lerpFactor = 0.058;
            currentRotation.x += (targetRotation.x - currentRotation.x) * lerpFactor;
            currentRotation.y += (targetRotation.y - currentRotation.y) * lerpFactor;

            // 2. Damped Eye Gaze Tracking across face visor
            const eyeLerp = 0.095;
            currentEye.x += (targetEye.x - currentEye.x) * eyeLerp;
            currentEye.z += (targetEye.z - currentEye.z) * eyeLerp;

            // 3. Periodic Natural Idle Blinking
            if (now >= nextIdleBlinkTime && (!eyeAnim || eyeAnim.type === 'idle')) {
                triggerEyeAnim('blink', 0.18);
                nextIdleBlinkTime = now + 3.2 + Math.random() * 2.8;
            }

            // 4. Eye Animation Scaling (Blinks, Winks, Squints, Excitement, Scan, Happy, Curious)
            let leftScaleX = 1.0, leftScaleZ = 1.0;
            let rightScaleX = 1.0, rightScaleZ = 1.0;
            let eyeScanOffsetX = 0;
            let eyeLiftOffsetZ = 0;

            if (eyeAnim && eyeAnim.type !== 'idle') {
                const p = (now - eyeAnim.startTime) / (eyeAnim.duration || 0.35);
                if (p >= 1) {
                    eyeAnim.type = 'idle';
                } else {
                    if (eyeAnim.type === 'blink') {
                        // Smooth clean eyelid blink 1 -> 0.04 -> 1
                        const factor = Math.sin(p * Math.PI);
                        const sZ = Math.max(0.04, 1.0 - factor * 0.96);
                        leftScaleZ = sZ;
                        rightScaleZ = sZ;
                        leftScaleX = 1.0 + factor * 0.10;
                        rightScaleX = leftScaleX;
                    } else if (eyeAnim.type === 'double-blink') {
                        // Rapid cute double-blink
                        const factor = Math.abs(Math.sin(p * Math.PI * 2));
                        const sZ = Math.max(0.05, 1.0 - factor * 0.95);
                        leftScaleZ = sZ;
                        rightScaleZ = sZ;
                    } else if (eyeAnim.type === 'wink') {
                        // Left eye winks closed to a line, right eye perks wide
                        const factor = Math.sin(p * Math.PI);
                        leftScaleZ = Math.max(0.05, 1.0 - factor * 0.95);
                        leftScaleX = 1.0 + factor * 0.15;
                        rightScaleZ = 1.0 + factor * 0.35;
                        rightScaleX = 1.0 + factor * 0.20;
                    } else if (eyeAnim.type === 'excited') {
                        // Both eyes expand with joy & wonder
                        const factor = Math.sin(p * Math.PI);
                        const sZ = 1.0 + factor * 0.42;
                        const sX = 1.0 + factor * 0.30;
                        leftScaleX = sX;
                        leftScaleZ = sZ;
                        rightScaleX = sX;
                        rightScaleZ = sZ;
                    } else if (eyeAnim.type === 'squint') {
                        // Narrow focused horizontal laser slit
                        const factor = Math.sin(p * Math.PI);
                        const sZ = Math.max(0.10, 1.0 - factor * 0.86);
                        const sX = 1.0 + factor * 0.25;
                        leftScaleZ = sZ;
                        leftScaleX = sX;
                        rightScaleZ = sZ;
                        rightScaleX = sX;
                    } else if (eyeAnim.type === 'scan') {
                        // Eyes glance and glide left-right rapidly
                        eyeScanOffsetX = Math.sin(p * Math.PI * 4) * 0.08;
                        const factor = Math.sin(p * Math.PI);
                        leftScaleZ = 1.0 - factor * 0.40;
                        rightScaleZ = 1.0 - factor * 0.40;
                    } else if (eyeAnim.type === 'happy') {
                        // Cute anime happy upward squint / curved crescents
                        const factor = Math.sin(p * Math.PI);
                        leftScaleZ = Math.max(0.14, 1.0 - factor * 0.80);
                        leftScaleX = 1.0 + factor * 0.22;
                        rightScaleZ = leftScaleZ;
                        rightScaleX = leftScaleX;
                        eyeLiftOffsetZ = factor * 0.04;
                    } else if (eyeAnim.type === 'curious') {
                        // Asymmetrical curious head cock eyes
                        const factor = Math.sin(p * Math.PI);
                        leftScaleZ = Math.max(0.20, 1.0 - factor * 0.60);
                        rightScaleZ = 1.0 + factor * 0.32;
                    } else if (eyeAnim.type === 'sparkle') {
                        // Pulsing sparkle wonder eyes
                        const pulse = Math.sin(p * Math.PI * 6);
                        const s = 1.0 + Math.abs(pulse) * 0.35;
                        leftScaleX = s;
                        leftScaleZ = s;
                        rightScaleX = s;
                        rightScaleZ = s;
                    } else if (eyeAnim.type === 'surprised') {
                        // Wide round astonished eyes
                        const factor = Math.sin(p * Math.PI);
                        const s = 1.0 + factor * 0.45;
                        leftScaleX = s;
                        leftScaleZ = s;
                        rightScaleX = s;
                        rightScaleZ = s;
                    }
                }
            }

            // Apply transforms to eye meshes
            eyeMeshes.forEach(function (eye) {
                if (eye.userData && eye.userData.basePos) {
                    const isLeft = eye.userData.isLeft;
                    const sx = isLeft ? leftScaleX : rightScaleX;
                    const sz = isLeft ? leftScaleZ : rightScaleZ;

                    eye.position.x = eye.userData.basePos.x + currentEye.x + eyeScanOffsetX;
                    eye.position.z = eye.userData.basePos.z + currentEye.z + eyeLiftOffsetZ;
                    eye.position.y = eye.userData.basePos.y;
                    eye.scale.set(sx, 1.0, sz);
                }
            });

            // 5. Eye Glow & Digital OLED Color Lerp
            if (targetTheme && targetTheme.color) {
                currentEyeColor.lerp(targetTheme.color, 0.12);
                eyeMaterials.forEach(function (mat) {
                    mat.color.copy(currentEyeColor);
                });
            }

            // 6. Idle Organic Floating / Breathing
            const idleBob = Math.sin(elapsed * 2.1) * 0.075;
            const idleTiltZ = Math.cos(elapsed * 1.5) * 0.025;
            const idleSwayX = Math.sin(elapsed * 1.1) * 0.02;

            // 7. Body & Head Animations (Replaces jump bounce with expressive gestures)
            let animRotX = 0, animRotY = 0, animRotZ = 0;
            let animPosY = 0, animPosZ = 0;

            if (bodyAnim && bodyAnim.type !== 'idle') {
                const p = (now - bodyAnim.startTime) / (bodyAnim.duration || 1.0);
                if (p >= 1) {
                    bodyAnim.type = 'idle';
                } else {
                    const decay = 1.0 - p;
                    if (bodyAnim.type === 'wiggle') {
                        // Joyful dance shimmy side to side with cute bounce
                        animRotY = Math.sin(p * Math.PI * 6) * 0.30 * decay;
                        animRotZ = Math.cos(p * Math.PI * 6) * 0.20 * decay;
                        animPosY = Math.sin(p * Math.PI * 2) * 0.12;
                    } else if (bodyAnim.type === 'tilt') {
                        // Cheerful inquisitive head cock / tilt
                        animRotZ = Math.sin(p * Math.PI) * 0.40;
                        animRotX = Math.sin(p * Math.PI) * 0.12;
                        animPosY = Math.sin(p * Math.PI) * 0.05;
                    } else if (bodyAnim.type === 'nod') {
                        // Encouraging affirmative multi-nod
                        animRotX = Math.sin(p * Math.PI * 6) * 0.25 * decay;
                        animPosY = -Math.abs(Math.sin(p * Math.PI * 6)) * 0.06 * decay;
                    } else if (bodyAnim.type === 'lean') {
                        // Interview mock inspection forward zoom & lean
                        animPosZ = Math.sin(p * Math.PI) * 0.50;
                        animRotX = -Math.sin(p * Math.PI) * 0.26;
                        animPosY = -Math.sin(p * Math.PI) * 0.06;
                    } else if (bodyAnim.type === 'spin') {
                        // 360-degree victory celebration pirouette
                        const ease = 1.0 - Math.pow(1.0 - p, 3);
                        animRotY = ease * (Math.PI * 2);
                        animPosY = Math.sin(p * Math.PI) * 0.18;
                    } else if (bodyAnim.type === 'wave') {
                        // Friendly greeting sway
                        animRotZ = Math.sin(p * Math.PI * 3) * 0.28 * decay;
                        animRotY = Math.cos(p * Math.PI * 3) * 0.18 * decay;
                        animPosY = Math.sin(p * Math.PI) * 0.08;
                    } else if (bodyAnim.type === 'bounce') {
                        // Energetic celebratory double chest-puff bounce
                        animPosY = Math.sin(p * Math.PI * 4) * 0.08 * decay;
                        animRotX = Math.sin(p * Math.PI * 4) * 0.12 * decay;
                    } else if (bodyAnim.type === 'bow') {
                        // Polite welcoming bow
                        animRotX = Math.sin(p * Math.PI) * 0.32;
                        animPosY = -Math.sin(p * Math.PI) * 0.06;
                    }
                }
            }

            // 8. Speech Talking Micro-Motion
            let talkRotX = 0, talkRotZ = 0, talkPosY = 0;
            let mouthScale = 1.0;
            if (isTypingDialogue) {
                talkRotX = Math.sin(now * 16) * 0.022;
                talkRotZ = Math.sin(now * 11) * 0.015;
                talkPosY = Math.sin(now * 20) * 0.012;
                mouthScale = 1.0 + Math.sin(now * 24) * 0.25;
            }

            // Apply mouth tracking and micro-scaling
            if (mouthMesh && mouthMesh.userData && mouthMesh.userData.basePos) {
                mouthMesh.position.x = mouthMesh.userData.basePos.x + (currentEye.x + eyeScanOffsetX) * 0.35;
                mouthMesh.position.z = mouthMesh.userData.basePos.z + currentEye.z * 0.35 + eyeLiftOffsetZ * 0.5;
                mouthMesh.position.y = mouthMesh.userData.basePos.y;
                mouthMesh.scale.set(mouthScale, 1.0, mouthScale);
            }

            // Apply transforms to robot pivot
            robotPivot.rotation.x = currentRotation.x + animRotX + talkRotX;
            robotPivot.rotation.y = currentRotation.y + idleSwayX + animRotY;
            robotPivot.rotation.z = -currentRotation.y * 0.15 + idleTiltZ + animRotZ + talkRotZ;

            robotPivot.position.y = basePosition.y + idleBob + animPosY + talkPosY;
            robotPivot.position.z = basePosition.z + animPosZ;

            renderer.render(scene, camera);
        }

        function startRenderLoop() {
            if (!animationFrameId && isLoaded && isVisible && isTabActive) {
                clock.start();
                animate();
            }
        }

        function stopRenderLoop() {
            if (animationFrameId) {
                cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
        }

        window.__heroRobot = {
            scene: scene,
            camera: camera,
            renderer: renderer,
            clock: clock,
            robotPivot: robotPivot,
            get robotModel() { return robotModel; },
            eyeMeshes: eyeMeshes,
            get mouthMesh() { return mouthMesh; },
            eyeMaterials: eyeMaterials,
            eyeAnim: eyeAnim,
            bodyAnim: bodyAnim,
            setMode: setMode,
            playNextDialogue: playNextDialogue,
            onRobotClicked: onRobotClicked,
            triggerEyeAnim: triggerEyeAnim,
            triggerBodyAnim: triggerBodyAnim,
            get isLoaded() { return isLoaded; },
            get isVisible() { return isVisible; },
            openFullscreen: openFullscreen,
            closeFullscreen: closeFullscreen,
            get isFullscreenOpen() { return isFullscreenOpen; }
        };
    }
})();
