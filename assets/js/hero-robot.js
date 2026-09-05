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
        const bubbleModeBadge = document.getElementById('speech-mode-badge');
        const bubbleIdentityEl = document.querySelector('.speech-bot-identity');
        const modeChips = document.querySelectorAll('.speech-mode-chips .mode-chip');
        const nextBtn = document.getElementById('speech-next-btn');

        const modelPath = container.getAttribute('data-model-path') || 'assets/models/cute_robot.glb';
        const loaderEl = document.getElementById('hero-robot-loader');
        const progressEl = document.getElementById('hero-robot-progress');

        // Eye Color Themes (using MeshBasicMaterial for rich, unblown digital OLED display colors)
        let THREE_AVAILABLE = (typeof THREE !== 'undefined');
        const eyeColorThemes = {
            talk: {
                color: THREE_AVAILABLE ? new THREE.Color(0x10b981) : null, // Vibrant Emerald Mint
                badgeClass: 'badge-talk',
                badgeText: 'TALK'
            },
            boost: {
                color: THREE_AVAILABLE ? new THREE.Color(0xf59e0b) : null, // Electric Solar Amber / Gold
                badgeClass: 'badge-boost',
                badgeText: 'BOOST'
            },
            grill: {
                color: THREE_AVAILABLE ? new THREE.Color(0xef4444) : null, // Fiery Crimson Laser Red
                badgeClass: 'badge-grill',
                badgeText: 'GRILL-ME'
            }
        };

        let currentMode = 'talk';
        let targetTheme = eyeColorThemes.talk;
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
                    text: "Need career confidence? Tap **⚡ /boost**! Want tough mock interview practice? Tap **🔥 /grill-me**!",
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

        function typeDialogue(text, onComplete) {
            currentDialogueFullText = text || '';
            if (typewriterTimer) {
                clearInterval(typewriterTimer);
                typewriterTimer = null;
            }

            if (!bubbleTextEl) return;

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
                } else {
                    clearInterval(typewriterTimer);
                    typewriterTimer = null;
                    isTypingDialogue = false;
                    setTimeout(function () {
                        if (!isTypingDialogue && cursorSpan.parentNode) {
                            cursorSpan.parentNode.removeChild(cursorSpan);
                        }
                    }, 800);
                    if (typeof onComplete === 'function') onComplete();
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
        }

        function formatSpeechText(str) {
            let safe = str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
            
            // Closed bold **...**
            safe = safe.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            // In-progress unclosed bold **...
            safe = safe.replace(/\*\*(.+)$/g, '<strong>$1</strong>');
            // Closed italics *...*
            safe = safe.replace(/\*([^*]+?)\*/g, '<em>$1</em>');
            // In-progress unclosed italics *...
            safe = safe.replace(/\*([^*]+)$/g, '<em>$1</em>');
            // Clean trailing single asterisk if user is mid-typing
            safe = safe.replace(/\*$/g, '');
            return safe;
        }

        // Set Active Mode ('talk', 'boost', 'grill')
        function setMode(mode, triggerDialogue) {
            if (!dialogueCatalog[mode]) mode = 'talk';
            currentMode = mode;
            targetTheme = eyeColorThemes[mode] || eyeColorThemes.talk;

            if (bubbleModeBadge) {
                bubbleModeBadge.className = 'speech-mode-badge badge-' + mode;
                bubbleModeBadge.textContent = (mode === 'grill' ? 'GRILL-ME' : (mode === 'boost' ? 'BOOST' : 'TALK'));
            }

            if (bubbleIdentityEl) {
                bubbleIdentityEl.classList.remove('mode-talk', 'mode-boost', 'mode-grill');
                bubbleIdentityEl.classList.add('mode-' + mode);
            }

            modeChips.forEach(function (chip) {
                const chipMode = chip.getAttribute('data-mode');
                if (chipMode === mode) {
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
                    } else if (mode === 'grill') {
                        triggerEyeAnim('squint', 0.95);
                        triggerBodyAnim('lean', 1.1);
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

        // Bubble Card Click (fast-forward if typing, else advance dialogue)
        if (bubbleCard) {
            bubbleCard.addEventListener('click', function (e) {
                if (e.target.closest('.mode-chip') || e.target.closest('.speech-next-btn')) return;
                if (isTypingDialogue) {
                    completeDialogueInstantly();
                } else {
                    playNextDialogue(false);
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

        // Initial welcome message in speech bubble immediately
        setMode('talk', true);

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
        const camera = new THREE.PerspectiveCamera(38, container.clientWidth / container.clientHeight, 0.1, 100);
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

        renderer.setSize(container.clientWidth, container.clientHeight);
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
            const width = container.clientWidth;
            const height = container.clientHeight;
            if (width === 0 || height === 0) return;

            camera.aspect = width / height;
            if (width < 450) {
                camera.position.z = 6.0;
            } else if (width < 768) {
                camera.position.z = 5.8;
            } else {
                camera.position.z = 5.6;
            }
            camera.updateProjectionMatrix();
            renderer.setSize(width, height);
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
            get isVisible() { return isVisible; }
        };
    }
})();
