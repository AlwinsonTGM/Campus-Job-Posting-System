/**
 * Campus Hire - Hero 3D Robot Interactive Controller
 * Features:
 *  - Three.js r128 + GLTFLoader
 *  - Studio Three-Point Lighting with Brand Emerald Rim Highlights
 *  - Real-time Cursor Tracking (Look-at Kinematics with Damped Lerp)
 *  - Organic Idle Bobbing & Micro-tilts
 *  - Interactive Click/Tap Bounce
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

        if (typeof THREE === 'undefined' || typeof THREE.GLTFLoader === 'undefined') {
            console.warn('[HeroRobot] Three.js or GLTFLoader is missing.');
            return;
        }

        const modelPath = container.getAttribute('data-model-path') || 'assets/models/cute_robot.glb';
        const loaderEl = document.getElementById('hero-robot-loader');
        const progressEl = document.getElementById('hero-robot-progress');

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

        // Studio Lighting Setup (balanced to avoid washing out colors)
        const ambientLight = new THREE.AmbientLight(0xffffff, 0.65);
        scene.add(ambientLight);

        // Key light (crisp key light)
        const keyLight = new THREE.DirectionalLight(0xffffff, 1.25);
        keyLight.position.set(4, 5, 4);
        keyLight.castShadow = true;
        keyLight.shadow.mapSize.width = 1024;
        keyLight.shadow.mapSize.height = 1024;
        keyLight.shadow.camera.near = 0.5;
        keyLight.shadow.camera.far = 15;
        keyLight.shadow.bias = -0.0005;
        scene.add(keyLight);

        // Fill Light (Soft mint diffuse fill)
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
        let isLoaded = false;
        let isVisible = true;
        let isTabActive = true;
        let animationFrameId = null;

        // Kinematics & Tracking State
        const targetRotation = { x: 0, y: 0 };
        const currentRotation = { x: 0, y: 0 };
        const targetEye = { x: 0, z: 0 };
        const currentEye = { x: 0, z: 0 };
        const basePosition = { x: 0, y: -0.15, z: 0 };
        let bounceOffset = 0;
        let bounceVelocity = 0;

        const eyeMeshes = [];
        let mouthMesh = null;

        const clock = new THREE.Clock();

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

                        // Identify the eyes (Mesh 32 & 39 -> Object_34 & Object_41) and mouth (Object_37)
                        if (child.name === 'Object_34' || child.name === 'Object_41') {
                            eyeMeshes.push(child);
                            child.renderOrder = 999;
                        } else if (child.name === 'Object_37') {
                            mouthMesh = child;
                            child.renderOrder = 999;
                        }

                        if (child.material) {
                            child.material = child.material.clone();
                            child.material.envMapIntensity = 1.0;

                            const matName = (child.material.name || '').toLowerCase();

                            // 1. Robot Body: Modern Campus Emerald Green
                            if (matName === 'material') {
                                child.material.color.setHex(0x15803d); // Deep saturated campus emerald green
                                child.material.roughness = 0.42;
                                child.material.metalness = 0.12;
                            }
                            // 2. Joints & Dark Accents: Deep Forest Pine
                            else if (matName.includes('material.001')) {
                                child.material.color.setHex(0x052e16); // Deep dark forest pine
                                child.material.roughness = 0.45;
                                child.material.metalness = 0.30;
                            }
                            // 3. Eyes & Glowing Features: Neon Mint / Luminous Green
                            else if (matName.includes('material.006')) {
                                child.material.color.setHex(0xdcfce7); // Glowing mint white
                                child.material.emissive.setHex(0x22c55e); // Neon emerald glow
                                child.material.emissiveIntensity = 1.3;
                                child.material.roughness = 0.10;
                                child.material.metalness = 0.05;
                                child.material.depthTest = false; // Always render on top of visor
                            }
                            // 4. Face Visor / Screen: Sleek OLED Glossy Black
                            else if (matName.includes('screen')) {
                                child.material.color.setHex(0x080808); // Pitch black glass visor
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
            // Center of the 3D container on the screen
            const containerCenterX = rect.left + rect.width / 2;
            const containerCenterY = rect.top + rect.height / 2;

            // Compute delta from robot to cursor
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;

            const deltaX = (clientX - containerCenterX) / (window.innerWidth * 0.5);
            const deltaY = (clientY - containerCenterY) / (window.innerHeight * 0.5);

            // Natural angle clamps:
            // Yaw (looking left/right): max ~38 degrees
            const maxYaw = 0.68;
            targetRotation.y = THREE.MathUtils.clamp(deltaX * 0.75, -maxYaw, maxYaw);

            // Pitch (looking up/down): max ~22 degrees
            const maxPitch = 0.38;
            targetRotation.x = THREE.MathUtils.clamp(deltaY * 0.45, -maxPitch, maxPitch);

            // Eye tracking across the face visor screen:
            // DeltaX shifts eyes horizontally (+X = right, -X = left)
            // -DeltaY shifts eyes vertically (+Z = up, -Z = down in local coordinate frame)
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

        // Click / Tap Bounce Interaction
        container.addEventListener('click', function () {
            // Trigger jump impulse
            bounceVelocity = 0.085;
        });

        // Responsive Resize Handler
        function handleResize() {
            if (!container || !renderer || !camera) return;
            const width = container.clientWidth;
            const height = container.clientHeight;
            if (width === 0 || height === 0) return;

            camera.aspect = width / height;
            // Adjust camera zoom slightly for compact mobile screens
            if (width < 450) {
                camera.position.z = 6.4;
            } else if (width < 768) {
                camera.position.z = 6.0;
            } else {
                camera.position.z = 5.8;
            }
            camera.updateProjectionMatrix();
            renderer.setSize(width, height);
        }

        const resizeObserver = new ResizeObserver(handleResize);
        resizeObserver.observe(container);
        window.addEventListener('resize', handleResize);

        // Power Saver: Pause render loop when scrolled offscreen
        const intersectionObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                isVisible = entry.isIntersecting;
                if (isVisible && isLoaded && isTabActive) {
                    startRenderLoop();
                } else {
                    stopRenderLoop();
                }
            });
        }, { threshold: 0.05 });
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

            // 1. Damped Lerp Cursor Tracking (Body / Head)
            const lerpFactor = 0.058;
            currentRotation.x += (targetRotation.x - currentRotation.x) * lerpFactor;
            currentRotation.y += (targetRotation.y - currentRotation.y) * lerpFactor;

            // 2. Damped Eye Gaze Tracking across face visor
            const eyeLerp = 0.095;
            currentEye.x += (targetEye.x - currentEye.x) * eyeLerp;
            currentEye.z += (targetEye.z - currentEye.z) * eyeLerp;

            eyeMeshes.forEach(function (eye) {
                eye.position.x = currentEye.x;
                eye.position.z = currentEye.z;
                eye.position.y = 0;
            });

            if (mouthMesh) {
                mouthMesh.position.x = currentEye.x * 0.35;
                mouthMesh.position.z = currentEye.z * 0.35;
                mouthMesh.position.y = 0;
            }

            // 2. Idle Organic Floating / Breathing
            const idleBob = Math.sin(elapsed * 2.1) * 0.075;
            const idleTiltZ = Math.cos(elapsed * 1.5) * 0.025;
            const idleSwayX = Math.sin(elapsed * 1.1) * 0.02;

            // 3. Click Bounce Physics Simulation (Elastic Spring)
            if (bounceVelocity !== 0 || bounceOffset !== 0) {
                bounceOffset += bounceVelocity;
                bounceVelocity -= 0.007; // Gravity
                if (bounceOffset <= 0) {
                    bounceOffset = 0;
                    bounceVelocity = -bounceVelocity * 0.42; // Elastic bounce floor
                    if (Math.abs(bounceVelocity) < 0.008) {
                        bounceVelocity = 0;
                    }
                }
            }

            // Apply transforms to pivot
            robotPivot.rotation.x = currentRotation.x;
            robotPivot.rotation.y = currentRotation.y + idleSwayX;
            robotPivot.rotation.z = -currentRotation.y * 0.15 + idleTiltZ;

            robotPivot.position.y = basePosition.y + idleBob + bounceOffset;

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
    }
})();
