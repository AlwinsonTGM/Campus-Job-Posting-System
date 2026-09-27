/**
 * Campus Job Posting System — Technical Architecture Knowledge Graph
 * Obsidian-Style Force-Directed Interactive Network Graph
 * Vanilla ES6+ Canvas 2D Implementation (Zero external dependencies)
 * Features:
 *   - Architectural Scale & Tiered Component Weighting
 *   - Native In-Circle Bootstrap Vector Icons
 *   - Streaming Data Flow Particle Pulses
 *   - Tactile Radial Click Shockwaves & Ripple Physics
 *   - Interactive Direct Neighbor Illumination & Contextual Dimming
 *   - Kinetic Rotating Orbit Rings & Scale Progress Arcs
 */

(function () {
  'use strict';

  // --------------------------------------------------------------------------
  // 1. GRAPH DATA SPECIFICATION (Architectural Sizing & Hierarchy)
  // --------------------------------------------------------------------------
  const GRAPH_DATA = {
    nodes: [
      // Central Platform Hub (Anchor - Largest Architectural Weight)
      {
        id: 'hub',
        name: 'Campus Job Posting System',
        shortName: 'KLD Platform Hub',
        cluster: 'core',
        tag: 'Platform Ecosystem',
        tier: 'Central Core Platform',
        scaleScore: '100%',
        icon: 'bi-mortarboard-fill',
        iconChar: '\uf6fd',
        detail: 'Central student assistantship application, scheduling, and campus office hiring ecosystem built for Kolehiyo ng Lungsod ng Dasmariñas (KLD).',
        radius: 46,
        fixed: true
      },

      // Domain Cluster Nodes (Major Architecture Pillars)
      {
        id: 'c-frontend',
        name: '3D & Frontend UI',
        shortName: 'Frontend & 3D',
        cluster: 'frontend',
        tag: 'Domain Cluster',
        tier: 'Tier 1 Architecture Pillar',
        scaleScore: '88%',
        icon: 'bi-window-stack',
        iconChar: '\uf6d2',
        detail: 'Hardware-accelerated Three.js robot kinematics, responsive Bootstrap layout, client-side entropy validation, and tactile paper tokens.',
        radius: 32
      },
      {
        id: 'c-backend',
        name: 'Core Backend & Server',
        shortName: 'Backend & Server',
        cluster: 'backend',
        tag: 'Domain Cluster',
        tier: 'Tier 1 Architecture Pillar',
        scaleScore: '92%',
        icon: 'bi-hdd-rack-fill',
        iconChar: '\uf40e',
        detail: 'Native PHP 8.2 modular architecture, multi-role RBAC authorization boundaries, Apache HTTP security headers, and live notification poller.',
        radius: 32
      },
      {
        id: 'c-data',
        name: 'Data & Persistence Engine',
        shortName: 'Data Engine',
        cluster: 'data',
        tag: 'Domain Cluster',
        tier: 'Tier 1 Architecture Pillar',
        scaleScore: '90%',
        icon: 'bi-database-fill-gear',
        iconChar: '\uf8b9',
        detail: 'Relational ACID persistence via MySQL/MariaDB InnoDB, strict PHP PDO parameter binding, and atomic migration datasets.',
        radius: 32
      },
      {
        id: 'c-ai',
        name: 'AI Cloud & Security/QA',
        shortName: 'AI & Security',
        cluster: 'ai',
        tag: 'Domain Cluster',
        tier: 'Tier 1 Architecture Pillar',
        scaleScore: '85%',
        icon: 'bi-shield-shaded',
        iconChar: '\uf53b',
        detail: 'Dual-tier NVIDIA NIM cloud inference with local heuristic fallback, bcrypt cryptography, anti-CSRF defenses, and Playwright E2E suites.',
        radius: 32
      },

      // Leaf Nodes: Frontend & 3D
      {
        id: 'threejs',
        name: 'Three.js WebGL & GLTFLoader',
        shortName: 'Three.js 3D',
        cluster: 'frontend',
        tag: '3D Graphics Engine',
        tier: 'Tier 2 Heavy Graphics Engine',
        scaleScore: '86%',
        icon: 'bi-boxes',
        iconChar: '\uf685',
        radius: 26,
        detail: 'Hardware-accelerated 3D mascot (cute_robot.glb) with 13 procedural kinematics & OLED visor shader.'
      },
      {
        id: 'bootstrap',
        name: 'Bootstrap 5.3 + Custom Tokens',
        shortName: 'Bootstrap 5.3',
        cluster: 'frontend',
        tag: 'UI Framework',
        tier: 'Tier 2 UI Framework',
        scaleScore: '82%',
        icon: 'bi-bootstrap-fill',
        iconChar: '\uf1a6',
        radius: 23,
        detail: 'Accessible 12-column responsive layout, candidate review drawers, paper modals, and vector iconography.'
      },
      {
        id: 'vanillajs',
        name: 'Vanilla JavaScript (ES6+)',
        shortName: 'Vanilla JS',
        cluster: 'frontend',
        tag: 'Client Runtime',
        tier: 'Tier 3 Client Controller',
        scaleScore: '72%',
        icon: 'bi-filetype-js',
        iconChar: '\uf74c',
        radius: 20,
        detail: 'Client-side Shannon password entropy meter, debounced spotlight search (Ctrl+K), and 3D Coverflow.'
      },
      {
        id: 'chartjs',
        name: 'Chart.js 4.4 Data Visualizations',
        shortName: 'Chart.js',
        cluster: 'frontend',
        tag: 'Analytics & Charts',
        tier: 'Tier 4 Analytics Library',
        scaleScore: '68%',
        icon: 'bi-bar-chart-line-fill',
        iconChar: '\uf17b',
        radius: 19,
        detail: 'Interactive canvas data visualizations for administrative reports, hiring quota progress gauges, and applicant demographics.'
      },
      {
        id: 'css-tokens',
        name: 'Modular CSS3 Design Tokens',
        shortName: 'CSS3 Tokens',
        cluster: 'frontend',
        tag: 'Design System',
        tier: 'Tier 4 Design Architecture',
        scaleScore: '66%',
        icon: 'bi-palette',
        iconChar: '\uf4b1',
        radius: 19,
        detail: '7 scoped domain stylesheets, tactile paper palette (#FBF9F4 canvas, #161616 ink), and zero pill clutter.'
      },

      // Leaf Nodes: Core Backend & Server
      {
        id: 'php',
        name: 'Native PHP 8.2+ Architecture',
        shortName: 'PHP 8.2+ MVC',
        cluster: 'backend',
        tag: 'Core Backend',
        tier: 'Tier 2 Core System Backbone',
        scaleScore: '96%',
        icon: 'bi-filetype-php',
        iconChar: '\uf757',
        radius: 28,
        detail: 'Modular template hierarchy, strict session lifecycle, 14 domain controllers, and multi-role RBAC authorization boundaries.'
      },
      {
        id: 'poller',
        name: 'Real-Time Notification Poller',
        shortName: 'Live Poller',
        cluster: 'backend',
        tag: 'Live State Sync',
        tier: 'Tier 3 Real-Time Sync',
        scaleScore: '74%',
        icon: 'bi-broadcast',
        iconChar: '\uf1d6',
        radius: 21,
        detail: '30-second interval polling with visibilitychange lifecycle pause and floating alert toasts.'
      },
      {
        id: 'apache',
        name: 'Apache 2.4 & XAMPP Stack',
        shortName: 'Apache 2.4',
        cluster: 'backend',
        tag: 'Web Server',
        tier: 'Tier 4 Web Server Host',
        scaleScore: '65%',
        icon: 'bi-server',
        iconChar: '\uf52c',
        radius: 19,
        detail: 'URL rewrite rules and strict HTTP security headers (X-Frame-Options, CSP, Referrer).'
      },

      // Leaf Nodes: Data & Persistence
      {
        id: 'mysql',
        name: 'MySQL 8.0 / MariaDB (InnoDB)',
        shortName: 'MySQL / InnoDB',
        cluster: 'data',
        tag: 'Relational Persistence',
        tier: 'Tier 2 Relational Persistence',
        scaleScore: '94%',
        icon: 'bi-database',
        iconChar: '\uf8c4',
        radius: 27,
        detail: 'ACID transactional integrity, foreign key cascades, atomic job states, and utf8mb4 full unicode persistence.'
      },
      {
        id: 'pdo',
        name: 'PHP PDO Prepared Statements',
        shortName: 'PDO Layer',
        cluster: 'data',
        tag: 'Database Abstraction',
        tier: 'Tier 3 Parameterized Driver',
        scaleScore: '78%',
        icon: 'bi-link-45deg',
        iconChar: '\uf470',
        radius: 22,
        detail: 'Strict parameter binding (zero SQL injection vulnerabilities) with singleton connection pool.'
      },
      {
        id: 'migrations',
        name: 'Atomic Migrations & Seeding',
        shortName: 'Migrations',
        cluster: 'data',
        tag: 'Database Pipeline',
        tier: 'Tier 4 Schema Pipeline',
        scaleScore: '62%',
        icon: 'bi-arrow-repeat',
        iconChar: '\uf130',
        radius: 18,
        detail: 'Transactional migration engine (migrate.php) with Demo vs. Clean Slate dataset switcher.'
      },

      // Leaf Nodes: AI & Security/QA
      {
        id: 'nvidia',
        name: 'NVIDIA NIM AI Cloud Gateway',
        shortName: 'NVIDIA NIM',
        cluster: 'ai',
        tag: 'Campus AI Assistant',
        tier: 'Tier 2 Cloud Inference Engine',
        scaleScore: '88%',
        icon: 'bi-cpu',
        iconChar: '\uf2d6',
        radius: 25,
        detail: 'Streaming inference via Llama-3.3-70B & DeepSeek-R1 with sliding multi-turn conversational memory.'
      },
      {
        id: 'security',
        name: 'Bcrypt & Anti-CSRF Tokens',
        shortName: 'Bcrypt & CSRF',
        cluster: 'ai',
        tag: 'Security Architecture',
        tier: 'Tier 3 Cryptographic Security',
        scaleScore: '82%',
        icon: 'bi-shield-lock-fill',
        iconChar: '\uf537',
        radius: 23,
        detail: 'Bcrypt password hashing, cryptographic session tokens on all state mutations, and IDOR boundary checks.'
      },
      {
        id: 'playwright',
        name: 'Playwright E2E Test Suite',
        shortName: 'Playwright E2E',
        cluster: 'ai',
        tag: 'QA & Automation',
        tier: 'Tier 3 Automated E2E Suite',
        scaleScore: '76%',
        icon: 'bi-check-all',
        iconChar: '\uf269',
        radius: 21,
        detail: 'TypeScript automated test suites across smoke, security fuzzing, half-screen viewports, and multi-role auth.'
      },
      {
        id: 'fallback-ai',
        name: 'Local Heuristic Fallback',
        shortName: 'Offline AI',
        cluster: 'ai',
        tag: 'Resilience Engine',
        tier: 'Tier 4 Resilience Fallback',
        scaleScore: '58%',
        icon: 'bi-shield-check',
        iconChar: '\uf52f',
        radius: 18,
        detail: 'Zero-downtime offline intelligence via plural-aware regex and curated campus knowledge rules.'
      }
    ],

    edges: [
      // Central Hub to 4 Domain Clusters
      { source: 'hub', target: 'c-frontend', length: 200, width: 3.0 },
      { source: 'hub', target: 'c-backend',  length: 200, width: 3.0 },
      { source: 'hub', target: 'c-data',     length: 200, width: 3.0 },
      { source: 'hub', target: 'c-ai',       length: 200, width: 3.0 },

      // Frontend Cluster to Leaves
      { source: 'c-frontend', target: 'threejs',    length: 108, width: 2.2 },
      { source: 'c-frontend', target: 'bootstrap',  length: 100, width: 2.0 },
      { source: 'c-frontend', target: 'vanillajs',  length: 95,  width: 1.8 },
      { source: 'c-frontend', target: 'chartjs',    length: 92,  width: 1.6 },
      { source: 'c-frontend', target: 'css-tokens', length: 90,  width: 1.6 },

      // Backend Cluster to Leaves
      { source: 'c-backend', target: 'php',    length: 112, width: 2.4 },
      { source: 'c-backend', target: 'poller', length: 98,  width: 1.8 },
      { source: 'c-backend', target: 'apache', length: 92,  width: 1.6 },

      // Data Cluster to Leaves
      { source: 'c-data', target: 'mysql',      length: 110, width: 2.4 },
      { source: 'c-data', target: 'pdo',        length: 98,  width: 1.8 },
      { source: 'c-data', target: 'migrations', length: 92,  width: 1.6 },

      // AI & Security Cluster to Leaves
      { source: 'c-ai', target: 'nvidia',      length: 106, width: 2.2 },
      { source: 'c-ai', target: 'security',    length: 100, width: 2.0 },
      { source: 'c-ai', target: 'playwright',  length: 96,  width: 1.8 },
      { source: 'c-ai', target: 'fallback-ai', length: 90,  width: 1.6 },

      // Cross-cluster interconnected relations (Obsidian Web aesthetic)
      { source: 'php', target: 'pdo', length: 125, width: 1.3, dashed: true },
      { source: 'nvidia', target: 'fallback-ai', length: 90, width: 1.3, dashed: true },
      { source: 'vanillajs', target: 'poller', length: 125, width: 1.3, dashed: true },
      { source: 'css-tokens', target: 'bootstrap', length: 90, width: 1.3, dashed: true },
      { source: 'chartjs', target: 'vanillajs', length: 90, width: 1.3, dashed: true }
    ]
  };

  // --------------------------------------------------------------------------
  // 2. CLASS: TechGraphEngine
  // --------------------------------------------------------------------------
  class TechGraphEngine {
    constructor(canvasId, containerId) {
      this.canvas = document.getElementById(canvasId);
      if (!this.canvas) return;

      this.ctx = this.canvas.getContext('2d');
      this.container = document.getElementById(containerId);
      this.tooltip = document.getElementById('tech-graph-tooltip');
      this.inspector = document.getElementById('tech-graph-inspector');

      // Theme & Colors Cache
      this.theme = 'light';
      this.colors = {};
      this.updateThemeColors();

      // State
      this.width = 0;
      this.height = 0;
      this.dpr = window.devicePixelRatio || 1;
      this.activeCluster = 'all';
      this.hoveredNode = null;
      this.pinnedNode = null;
      this.draggedNode = null;
      this.mousePos = { x: -999, y: -999 };
      this.animId = null;
      this.time = 0;

      // Deep copy nodes & edges for physics simulation
      this.nodes = GRAPH_DATA.nodes.map(n => ({
        ...n,
        radius: n.radius || 20,
        x: 0,
        y: 0,
        vx: 0,
        vy: 0,
        baseX: 0,
        baseY: 0,
        driftOffset: Math.random() * Math.PI * 2
      }));

      this.nodeMap = new Map(this.nodes.map(n => [n.id, n]));

      this.edges = GRAPH_DATA.edges.map(e => ({
        ...e,
        sourceNode: this.nodeMap.get(e.source),
        targetNode: this.nodeMap.get(e.target)
      }));

      // Interactive Dynamic Effects
      this.pulses = [];
      this.shockwaves = [];
      this.initPulses();

      // Initialize
      this.initDimensions();
      this.initPositions();
      this.bindEvents();

      // Default pin Central Platform Hub
      const defaultPin = this.nodeMap.get('hub') || this.nodes[0];
      this.pinNode(defaultPin);

      // Pre-warm font rendering for in-circle Bootstrap Icons
      if (document.fonts) {
        document.fonts.load('16px "bootstrap-icons"').then(() => {
          this.render();
        }).catch(() => {});
      }

      this.startLoop();
    }

    getClusterTheme(clusterId) {
      const isDark = this.theme === 'dark';
      const map = {
        core: {
          accent: '#2ECC5E',
          fill: isDark ? '#142E1D' : '#161616',
          stroke: '#2ECC5E',
          icon: isDark ? '#2ECC5E' : '#2ECC5E',
          glow: 'rgba(46, 204, 94, 0.45)',
          pulse: '#2ECC5E'
        },
        frontend: {
          accent: isDark ? '#38BDF8' : '#0284C7',
          fill: isDark ? '#082F49' : '#F0F9FF',
          stroke: isDark ? '#38BDF8' : '#0284C7',
          icon: isDark ? '#38BDF8' : '#0284C7',
          glow: isDark ? 'rgba(56, 189, 248, 0.45)' : 'rgba(2, 132, 199, 0.35)',
          pulse: isDark ? '#38BDF8' : '#0284C7'
        },
        backend: {
          accent: isDark ? '#FBBF24' : '#D97706',
          fill: isDark ? '#451A03' : '#FFFBEB',
          stroke: isDark ? '#FBBF24' : '#D97706',
          icon: isDark ? '#FBBF24' : '#D97706',
          glow: isDark ? 'rgba(251, 191, 36, 0.45)' : 'rgba(217, 119, 6, 0.35)',
          pulse: isDark ? '#FBBF24' : '#D97706'
        },
        data: {
          accent: isDark ? '#818CF8' : '#4F46E5',
          fill: isDark ? '#1E1B4B' : '#EEF2FF',
          stroke: isDark ? '#818CF8' : '#4F46E5',
          icon: isDark ? '#818CF8' : '#4F46E5',
          glow: isDark ? 'rgba(129, 140, 248, 0.45)' : 'rgba(79, 70, 229, 0.35)',
          pulse: isDark ? '#818CF8' : '#4F46E5'
        },
        ai: {
          accent: isDark ? '#34D399' : '#059669',
          fill: isDark ? '#064E3B' : '#ECFDF5',
          stroke: isDark ? '#34D399' : '#059669',
          icon: isDark ? '#34D399' : '#059669',
          glow: isDark ? 'rgba(52, 211, 153, 0.45)' : 'rgba(5, 150, 105, 0.35)',
          pulse: isDark ? '#34D399' : '#059669'
        }
      };
      return map[clusterId] || map.frontend;
    }

    updateThemeColors() {
      const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      this.theme = isDark ? 'dark' : 'light';

      if (isDark) {
        this.colors = {
          hubFill: '#161616',
          hubStroke: '#2ECC5E',
          hubText: '#FFFFFF',
          clusterFill: '#1E1E24',
          clusterStroke: '#2ECC5E',
          clusterText: '#E8E6E3',
          leafFill: '#141418',
          leafStroke: '#2E2E33',
          leafStrokeHover: '#2ECC5E',
          leafText: '#C9C6C0',
          labelBg: 'rgba(20, 20, 24, 0.85)',
          edgeDefault: 'rgba(58, 58, 66, 0.75)',
          edgeHover: 'rgba(46, 204, 94, 0.95)',
          edgeDashed: 'rgba(155, 151, 143, 0.35)',
          dimOpacity: 0.18
        };
      } else {
        this.colors = {
          hubFill: '#161616',
          hubStroke: '#2ECC5E',
          hubText: '#FFFFFF',
          clusterFill: '#F4EFE6',
          clusterStroke: '#161616',
          clusterText: '#161616',
          leafFill: '#FFFFFF',
          leafStroke: '#E5E0D4',
          leafStrokeHover: '#2ECC5E',
          leafText: '#4A463F',
          labelBg: 'rgba(255, 255, 255, 0.85)',
          edgeDefault: 'rgba(224, 218, 204, 0.9)',
          edgeHover: 'rgba(46, 204, 94, 0.95)',
          edgeDashed: 'rgba(110, 106, 97, 0.35)',
          dimOpacity: 0.18
        };
      }
    }

    initDimensions() {
      const rect = this.canvas.parentElement.getBoundingClientRect();
      this.width = rect.width;
      this.height = rect.height || 560;

      this.canvas.width = this.width * this.dpr;
      this.canvas.height = this.height * this.dpr;
      this.ctx.scale(this.dpr, this.dpr);
    }

    initPositions() {
      const cx = this.width / 2;
      const cy = this.height / 2;

      // Hub at exact center
      const hub = this.nodeMap.get('hub');
      if (hub) {
        hub.x = cx;
        hub.y = cy;
        hub.baseX = cx;
        hub.baseY = cy;
      }

      // Responsive cluster reach utilizing full horizontal & vertical space
      const clusterDistX = Math.min(this.width * 0.28, 300);
      const clusterDistY = Math.min(this.height * 0.28, 165);

      const clusterConfig = [
        { id: 'c-frontend', angle: -Math.PI * 0.75 }, // Top-Left
        { id: 'c-backend',  angle: -Math.PI * 0.25 }, // Top-Right
        { id: 'c-data',     angle: Math.PI * 0.25 },  // Bottom-Right
        { id: 'c-ai',       angle: Math.PI * 0.75 }   // Bottom-Left
      ];

      clusterConfig.forEach(cfg => {
        const cluster = this.nodeMap.get(cfg.id);
        if (cluster) {
          cluster.x = cx + Math.cos(cfg.angle) * clusterDistX;
          cluster.y = cy + Math.sin(cfg.angle) * clusterDistY;
          cluster.baseX = cluster.x;
          cluster.baseY = cluster.y;
        }
      });

      // Distribute Leaves around their respective cluster
      const clusters = ['frontend', 'backend', 'data', 'ai'];
      clusters.forEach(cId => {
        const clusterNode = this.nodeMap.get('c-' + cId);
        if (!clusterNode) return;

        const leaves = this.nodes.filter(n => n.cluster === cId && n.id !== clusterNode.id);
        const count = leaves.length;
        const baseAngle = Math.atan2(clusterNode.y - cy, clusterNode.x - cx);
        const arc = (count >= 5) ? (Math.PI * 0.88) : (Math.PI * 0.70);
        const startAngle = baseAngle - arc / 2;
        const leafDist = (this.width > 768) ? ((count >= 5) ? 104 : 96) : ((count >= 5) ? 72 : 66);

        leaves.forEach((leaf, idx) => {
          const angle = (count > 1) ? (startAngle + (idx / (count - 1)) * arc) : baseAngle;
          leaf.x = clusterNode.x + Math.cos(angle) * leafDist;
          leaf.y = clusterNode.y + Math.sin(angle) * leafDist;
          leaf.baseX = leaf.x;
          leaf.baseY = leaf.y;
        });
      });
    }

    initPulses() {
      this.pulses = [];
      this.edges.forEach((edge) => {
        const count = (edge.source === 'hub' || edge.width > 2.2) ? 2 : 1;
        for (let i = 0; i < count; i++) {
          const theme = this.getClusterTheme(edge.targetNode ? edge.targetNode.cluster : 'core');
          this.pulses.push({
            edge,
            progress: (i / count) + Math.random() * 0.25,
            speed: 0.0035 + Math.random() * 0.0035,
            size: edge.source === 'hub' ? 3.4 : 2.5,
            color: theme.pulse
          });
        }
      });
    }

    createShockwave(x, y, cluster) {
      const theme = this.getClusterTheme(cluster);
      this.shockwaves.push({
        x,
        y,
        radius: 12,
        maxRadius: 75,
        color: theme.accent,
        alpha: 0.85
      });
    }

    // --------------------------------------------------------------------------
    // 3. PHYSICS & SIMULATION
    // --------------------------------------------------------------------------
    updatePhysics() {
      this.time += 0.02;
      const cx = this.width / 2;
      const cy = this.height / 2;

      // 1. Center Hub anchored to center
      const hub = this.nodeMap.get('hub');
      if (hub && !hub.isDragging) {
        hub.x += (cx - hub.x) * 0.12;
        hub.y += (cy - hub.y) * 0.12;
      }

      // 2. Edge Spring Forces (Hooke's Law: F = -k * (d - d0))
      for (let i = 0; i < this.edges.length; i++) {
        const edge = this.edges[i];
        const s = edge.sourceNode;
        const t = edge.targetNode;
        if (!s || !t) continue;

        const dx = t.x - s.x;
        const dy = t.y - s.y;
        const dist = Math.sqrt(dx * dx + dy * dy) || 1;
        const targetLen = edge.length || 85;
        const diff = (dist - targetLen) / dist;
        const force = diff * 0.038;

        if (!s.isDragging && !s.fixed) {
          s.vx += dx * force;
          s.vy += dy * force;
        }
        if (!t.isDragging && !t.fixed) {
          t.vx -= dx * force;
          t.vy -= dy * force;
        }
      }

      // 3. Node Repulsion (Coulomb's Law with Architectural Radii)
      for (let i = 0; i < this.nodes.length; i++) {
        for (let j = i + 1; j < this.nodes.length; j++) {
          const a = this.nodes[i];
          const b = this.nodes[j];
          const dx = b.x - a.x;
          const dy = b.y - a.y;
          const distSq = dx * dx + dy * dy;
          const dist = Math.sqrt(distSq) || 1;
          const minDist = a.radius + b.radius + 36;

          if (dist < minDist) {
            const repel = (minDist - dist) / dist * 0.13;
            if (!a.isDragging && !a.fixed) {
              a.vx -= dx * repel;
              a.vy -= dy * repel;
            }
            if (!b.isDragging && !b.fixed) {
              b.vx += dx * repel;
              b.vy += dy * repel;
            }
          }
        }
      }

      // 4. Update Node Positions + Ambient Organic Drift
      for (let i = 0; i < this.nodes.length; i++) {
        const n = this.nodes[i];
        if (n.isDragging) continue;

        if (!n.fixed) {
          // Gentle spring pull toward base anchor to maintain balanced layout
          n.vx += (n.baseX - n.x) * 0.015;
          n.vy += (n.baseY - n.y) * 0.015;

          // Organic breathing drift
          n.vx += Math.sin(this.time + n.driftOffset) * 0.06;
          n.vy += Math.cos(this.time + n.driftOffset) * 0.06;

          // Velocity Damping
          n.vx *= 0.85;
          n.vy *= 0.85;

          // Velocity Clamp
          const speed = Math.sqrt(n.vx * n.vx + n.vy * n.vy);
          if (speed > 4.5) {
            n.vx = (n.vx / speed) * 4.5;
            n.vy = (n.vy / speed) * 4.5;
          }

          n.x += n.vx;
          n.y += n.vy;

          // Boundary Constraints
          const pad = n.radius + 16;
          if (n.x < pad) n.x = pad;
          if (n.x > this.width - pad) n.x = this.width - pad;
          if (n.y < pad) n.y = pad;
          if (n.y > this.height - pad) n.y = this.height - pad;
        }
      }
    }

    // --------------------------------------------------------------------------
    // 4. RENDER LOOP
    // --------------------------------------------------------------------------
    render() {
      this.ctx.clearRect(0, 0, this.width, this.height);

      const activeNode = this.hoveredNode || this.pinnedNode;

      // Identify connected nodes for contextual illumination
      const connectedIds = new Set();
      if (activeNode) {
        connectedIds.add(activeNode.id);
        for (let i = 0; i < this.edges.length; i++) {
          const edge = this.edges[i];
          if (edge.source === activeNode.id) connectedIds.add(edge.target);
          if (edge.target === activeNode.id) connectedIds.add(edge.source);
        }
      }

      // A. Render Edges
      for (let i = 0; i < this.edges.length; i++) {
        const edge = this.edges[i];
        const s = edge.sourceNode;
        const t = edge.targetNode;
        if (!s || !t) continue;

        const isHighlighted = activeNode && (s.id === activeNode.id || t.id === activeNode.id);
        const isDimmed = this.activeCluster !== 'all' &&
                         s.cluster !== this.activeCluster &&
                         t.cluster !== this.activeCluster &&
                         s.id !== 'hub';

        this.ctx.save();
        this.ctx.beginPath();
        this.ctx.moveTo(s.x, s.y);
        this.ctx.lineTo(t.x, t.y);

        if (edge.dashed) {
          this.ctx.setLineDash([4, 4]);
          this.ctx.strokeStyle = isHighlighted ? this.colors.edgeHover : this.colors.edgeDashed;
        } else {
          this.ctx.strokeStyle = isHighlighted ? this.colors.edgeHover : this.colors.edgeDefault;
        }

        const edgeAlpha = isDimmed ? this.colors.dimOpacity : (isHighlighted ? 1.0 : (activeNode ? 0.35 : 0.85));
        this.ctx.globalAlpha = edgeAlpha;
        this.ctx.lineWidth = isHighlighted ? edge.width + 1.4 : edge.width;
        this.ctx.stroke();
        this.ctx.restore();
      }

      // B. Render Animated Data Packet Flow (Streaming Pulses)
      for (let p = 0; p < this.pulses.length; p++) {
        const pulse = this.pulses[p];
        pulse.progress += pulse.speed;
        if (pulse.progress > 1) pulse.progress = 0;

        const s = pulse.edge.sourceNode;
        const t = pulse.edge.targetNode;
        if (!s || !t) continue;

        const isEdgeActive = activeNode && (s.id === activeNode.id || t.id === activeNode.id);
        const isDimmed = this.activeCluster !== 'all' &&
                         s.cluster !== this.activeCluster &&
                         t.cluster !== this.activeCluster &&
                         s.id !== 'hub';
        if (isDimmed) continue;

        const px = s.x + (t.x - s.x) * pulse.progress;
        const py = s.y + (t.y - s.y) * pulse.progress;

        this.ctx.save();
        this.ctx.beginPath();
        this.ctx.arc(px, py, isEdgeActive ? pulse.size + 1.2 : pulse.size, 0, Math.PI * 2);
        this.ctx.fillStyle = pulse.color;
        this.ctx.shadowColor = pulse.color;
        this.ctx.shadowBlur = isEdgeActive ? 10 : 5;
        this.ctx.globalAlpha = isEdgeActive ? 1.0 : (activeNode ? 0.4 : 0.85);
        this.ctx.fill();
        this.ctx.restore();
      }

      // C. Render Interactive Click Shockwaves
      for (let i = this.shockwaves.length - 1; i >= 0; i--) {
        const sw = this.shockwaves[i];
        sw.radius += 2.4;
        sw.alpha *= 0.93;
        if (sw.alpha < 0.02 || sw.radius > sw.maxRadius) {
          this.shockwaves.splice(i, 1);
          continue;
        }

        this.ctx.save();
        this.ctx.beginPath();
        this.ctx.arc(sw.x, sw.y, sw.radius, 0, Math.PI * 2);
        this.ctx.strokeStyle = sw.color;
        this.ctx.lineWidth = 2.4;
        this.ctx.globalAlpha = sw.alpha;
        this.ctx.stroke();
        this.ctx.restore();
      }

      // D. Render Nodes
      for (let i = 0; i < this.nodes.length; i++) {
        const n = this.nodes[i];
        const isHovered = this.hoveredNode && this.hoveredNode.id === n.id;
        const isPinned = this.pinnedNode && this.pinnedNode.id === n.id;
        const isNeighbor = activeNode && connectedIds.has(n.id);
        const isClusterFiltered = this.activeCluster !== 'all' && n.cluster !== this.activeCluster && n.id !== 'hub';

        const theme = this.getClusterTheme(n.cluster);

        this.ctx.save();
        const nodeAlpha = isClusterFiltered ? this.colors.dimOpacity : ((activeNode && !isNeighbor) ? 0.28 : 1.0);
        this.ctx.globalAlpha = nodeAlpha;

        // 1. Ambient Rotating Orbit Rings
        if (n.id === 'hub') {
          // Slow counter-clockwise rotating dashed ring
          this.ctx.save();
          this.ctx.setLineDash([5, 5]);
          this.ctx.strokeStyle = theme.accent;
          this.ctx.lineWidth = 1.6;
          this.ctx.globalAlpha = nodeAlpha * 0.55;
          this.ctx.beginPath();
          this.ctx.arc(n.x, n.y, n.radius + 10, -this.time * 0.4, -this.time * 0.4 + Math.PI * 2);
          this.ctx.stroke();
          this.ctx.restore();
        } else if (n.id.startsWith('c-')) {
          // Rotating accent orbit ring for domain pillars
          this.ctx.save();
          this.ctx.setLineDash([3, 4]);
          this.ctx.strokeStyle = theme.accent;
          this.ctx.lineWidth = 1.4;
          this.ctx.globalAlpha = nodeAlpha * 0.45;
          this.ctx.beginPath();
          this.ctx.arc(n.x, n.y, n.radius + 7, this.time * 0.5, this.time * 0.5 + Math.PI * 2);
          this.ctx.stroke();
          this.ctx.restore();
        }

        // 2. Outer Glow Aura on Hover / Pin / Neighbor
        if (isHovered || isPinned || isNeighbor) {
          this.ctx.beginPath();
          this.ctx.arc(n.x, n.y, n.radius + 9, 0, Math.PI * 2);
          this.ctx.fillStyle = theme.glow;
          this.ctx.fill();
        }

        // 3. Base Circle (Architecturally Sized)
        this.ctx.beginPath();
        const r = isHovered ? n.radius + 2.5 : n.radius;
        this.ctx.arc(n.x, n.y, r, 0, Math.PI * 2);

        this.ctx.fillStyle = (n.id === 'hub') ? this.colors.hubFill : theme.fill;
        this.ctx.strokeStyle = (isHovered || isPinned) ? theme.accent : theme.stroke;
        this.ctx.lineWidth = (isHovered || isPinned) ? 3.2 : 2.2;
        this.ctx.fill();
        this.ctx.stroke();

        // 4. Architectural Scale Progress Arc (Around Border)
        if (n.scaleScore && n.id !== 'hub') {
          const pct = parseInt(n.scaleScore, 10) / 100;
          this.ctx.save();
          this.ctx.beginPath();
          this.ctx.arc(n.x, n.y, r + 3, -Math.PI / 2, -Math.PI / 2 + pct * Math.PI * 2);
          this.ctx.strokeStyle = theme.accent;
          this.ctx.lineWidth = 2.0;
          this.ctx.lineCap = 'round';
          this.ctx.globalAlpha = nodeAlpha * ((isHovered || isPinned) ? 0.95 : 0.45);
          this.ctx.stroke();
          this.ctx.restore();
        }

        // 5. In-Circle Vector Icon
        this.ctx.save();
        this.ctx.textAlign = 'center';
        this.ctx.textBaseline = 'middle';

        if (n.id === 'hub') {
          // Hub: Vector Mortarboard Icon + Bold Moniker
          this.ctx.fillStyle = theme.icon;
          this.ctx.font = '22px "bootstrap-icons"';
          this.ctx.fillText(n.iconChar || '\uf6fd', n.x, n.y - 7);

          this.ctx.fillStyle = this.colors.hubText;
          this.ctx.font = '800 10.5px Inter, sans-serif';
          this.ctx.fillText('KLD HUB', n.x, n.y + 13);
        } else if (n.id.startsWith('c-')) {
          // Domain Pillars: Large Central Icon
          this.ctx.fillStyle = theme.icon;
          this.ctx.font = '20px "bootstrap-icons"';
          this.ctx.fillText(n.iconChar, n.x, n.y);
        } else {
          // Leaf Nodes: Scaled Icon matching component size
          const iconSize = Math.max(13, Math.round(r * 0.85));
          this.ctx.fillStyle = theme.icon;
          this.ctx.font = `${iconSize}px "bootstrap-icons"`;
          this.ctx.fillText(n.iconChar, n.x, n.y);
        }
        this.ctx.restore();

        // 6. Crisp Label with Backdrop Underneath Node
        this.ctx.save();
        this.ctx.textAlign = 'center';
        this.ctx.textBaseline = 'top';

        if (n.id.startsWith('c-')) {
          this.ctx.font = 'bold 12px Inter, sans-serif';
          this.ctx.fillStyle = theme.accent;
          this.ctx.fillText(n.shortName, n.x, n.y + r + 6);
        } else if (n.id !== 'hub') {
          this.ctx.font = (isHovered || isPinned) ? 'bold 11px Inter, sans-serif' : '600 10px Inter, sans-serif';
          this.ctx.fillStyle = (isHovered || isPinned) ? theme.accent : this.colors.leafText;
          this.ctx.fillText(n.shortName, n.x, n.y + r + 5);
        }
        this.ctx.restore();

        this.ctx.restore();
      }
    }

    startLoop() {
      const step = () => {
        this.updatePhysics();
        this.render();
        this.animId = requestAnimationFrame(step);
      };
      this.animId = requestAnimationFrame(step);
    }

    // --------------------------------------------------------------------------
    // 5. INTERACTION & EVENT LISTENERS
    // --------------------------------------------------------------------------
    getNodeAt(x, y) {
      for (let i = this.nodes.length - 1; i >= 0; i--) {
        const n = this.nodes[i];
        const dx = x - n.x;
        const dy = y - n.y;
        if (dx * dx + dy * dy <= (n.radius + 10) * (n.radius + 10)) {
          return n;
        }
      }
      return null;
    }

    bindEvents() {
      // Click Trigger (Pin & Shockwave)
      this.canvas.addEventListener('click', (e) => {
        const rect = this.canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const target = this.getNodeAt(x, y);

        if (target) {
          this.pinNode(target);
          this.createShockwave(target.x, target.y, target.cluster);
        }
      });

      // Mouse Move (Hover & Drag)
      this.canvas.addEventListener('mousemove', (e) => {
        const rect = this.canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        this.mousePos = { x, y };

        if (this.draggedNode) {
          this.draggedNode.x = x;
          this.draggedNode.y = y;
          this.draggedNode.vx = 0;
          this.draggedNode.vy = 0;
          this.updateTooltip(this.draggedNode, x, y);
          return;
        }

        const hovered = this.getNodeAt(x, y);
        if (hovered !== this.hoveredNode) {
          this.hoveredNode = hovered;
          this.canvas.style.cursor = hovered ? 'pointer' : 'grab';
          if (hovered) {
            this.showTooltip(hovered, x, y);
          } else {
            this.hideTooltip();
          }
        } else if (hovered) {
          this.updateTooltip(hovered, x, y);
        }
      });

      // Mouse Down (Start Drag)
      this.canvas.addEventListener('mousedown', (e) => {
        const rect = this.canvas.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        const target = this.getNodeAt(x, y);

        if (target) {
          this.draggedNode = target;
          target.isDragging = true;
          this.canvas.style.cursor = 'grabbing';
          this.pinNode(target);
          this.createShockwave(target.x, target.y, target.cluster);
        }
      });

      // Window Mouse Up (End Drag)
      window.addEventListener('mouseup', () => {
        if (this.draggedNode) {
          this.draggedNode.isDragging = false;
          this.draggedNode.baseX = this.draggedNode.x;
          this.draggedNode.baseY = this.draggedNode.y;
          this.draggedNode = null;
          this.canvas.style.cursor = this.hoveredNode ? 'pointer' : 'grab';
        }
      });

      // Canvas Leave
      this.canvas.addEventListener('mouseleave', () => {
        if (!this.draggedNode) {
          this.hoveredNode = null;
          this.hideTooltip();
        }
      });

      // Touch Events for Mobile
      this.canvas.addEventListener('touchstart', (e) => {
        if (e.touches.length === 1) {
          const rect = this.canvas.getBoundingClientRect();
          const x = e.touches[0].clientX - rect.left;
          const y = e.touches[0].clientY - rect.top;
          const target = this.getNodeAt(x, y);
          if (target) {
            this.draggedNode = target;
            target.isDragging = true;
            this.pinNode(target);
            this.createShockwave(target.x, target.y, target.cluster);
            this.showTooltip(target, x, y);
            e.preventDefault();
          }
        }
      }, { passive: false });

      this.canvas.addEventListener('touchmove', (e) => {
        if (this.draggedNode && e.touches.length === 1) {
          const rect = this.canvas.getBoundingClientRect();
          const x = e.touches[0].clientX - rect.left;
          const y = e.touches[0].clientY - rect.top;
          this.draggedNode.x = x;
          this.draggedNode.y = y;
          this.updateTooltip(this.draggedNode, x, y);
          e.preventDefault();
        }
      }, { passive: false });

      this.canvas.addEventListener('touchend', () => {
        if (this.draggedNode) {
          this.draggedNode.isDragging = false;
          this.draggedNode.baseX = this.draggedNode.x;
          this.draggedNode.baseY = this.draggedNode.y;
          this.draggedNode = null;
          setTimeout(() => this.hideTooltip(), 1500);
        }
      });

      // Window Resize Listener
      window.addEventListener('resize', () => {
        this.initDimensions();
        this.initPositions();
      });

      // Dark Mode Mutation Observer
      const observer = new MutationObserver(() => {
        this.updateThemeColors();
      });
      observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['data-theme']
      });

      // Category Filter Buttons
      const filterButtons = document.querySelectorAll('.tech-graph-filter-btn');
      filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
          filterButtons.forEach(b => b.classList.remove('is-active'));
          btn.classList.add('is-active');
          this.setFilter(btn.getAttribute('data-cluster') || 'all');
        });
      });

      // Reset View Button
      const resetBtn = document.getElementById('tech-graph-reset-btn');
      if (resetBtn) {
        resetBtn.addEventListener('click', () => {
          this.resetLayout();
        });
      }
    }

    // --------------------------------------------------------------------------
    // 6. TOOLTIP & INSPECTOR HUD HELPERS
    // --------------------------------------------------------------------------
    showTooltip(node, x, y) {
      if (!this.tooltip) return;
      this.tooltip.querySelector('.tech-graph-tooltip-tag').textContent = `${node.tag || 'Component'} · ${node.scaleScore || '75%'}`;
      this.tooltip.querySelector('.tech-graph-tooltip-title').textContent = node.name;
      this.tooltip.querySelector('.tech-graph-tooltip-desc').textContent = node.detail;
      this.tooltip.classList.add('is-visible');
      this.updateTooltip(node, x, y);
    }

    updateTooltip(node, x, y) {
      if (!this.tooltip) return;
      const posX = Math.max(100, Math.min(this.width - 100, x));
      const posY = Math.max(70, y);
      this.tooltip.style.left = posX + 'px';
      this.tooltip.style.top = posY + 'px';
    }

    hideTooltip() {
      if (!this.tooltip) return;
      this.tooltip.classList.remove('is-visible');
    }

    pinNode(node) {
      if (!node || !this.inspector) return;
      this.pinnedNode = node;

      const theme = this.getClusterTheme(node.cluster);

      const iconWrap = this.inspector.querySelector('.tech-graph-inspector-icon');
      const iconEl = this.inspector.querySelector('.tech-graph-inspector-icon i');
      const tagEl = this.inspector.querySelector('.tech-graph-inspector-tag');
      const clusterEl = this.inspector.querySelector('.tech-graph-inspector-cluster');
      const tierEl = this.inspector.querySelector('#tech-graph-inspector-tier') || this.inspector.querySelector('.tech-graph-inspector-tier');
      const titleEl = this.inspector.querySelector('.tech-graph-inspector-title');
      const descEl = this.inspector.querySelector('.tech-graph-inspector-desc');

      if (iconWrap) {
        iconWrap.style.borderColor = theme.accent;
      }
      if (iconEl) {
        iconEl.className = 'bi ' + (node.icon || 'bi-cpu');
        iconEl.style.color = theme.accent;
      }
      if (tagEl) {
        tagEl.textContent = node.tag || 'Component';
        tagEl.style.color = theme.accent;
      }
      if (clusterEl) {
        const clusterNames = {
          core: 'Ecosystem Hub',
          frontend: '3D & UI',
          backend: 'Core Backend',
          data: 'Persistence',
          ai: 'AI & Security'
        };
        clusterEl.textContent = clusterNames[node.cluster] || 'Architecture';
      }
      if (tierEl) {
        tierEl.innerHTML = `<i class="bi bi-diagram-2" style="color: ${theme.accent};"></i> ${node.tier || 'Architecture Service'} · ${node.scaleScore || '75%'} Scale`;
      }
      if (titleEl) titleEl.textContent = node.name;
      if (descEl) descEl.textContent = node.detail;
    }

    setFilter(clusterId) {
      this.activeCluster = clusterId;
      if (clusterId !== 'all') {
        const clusterNode = this.nodeMap.get('c-' + clusterId);
        if (clusterNode) {
          this.pinNode(clusterNode);
          this.createShockwave(clusterNode.x, clusterNode.y, clusterNode.cluster);
        }
      }
    }

    resetLayout() {
      this.initPositions();
      this.activeCluster = 'all';
      const filterButtons = document.querySelectorAll('.tech-graph-filter-btn');
      filterButtons.forEach(btn => {
        if (btn.getAttribute('data-cluster') === 'all') {
          btn.classList.add('is-active');
        } else {
          btn.classList.remove('is-active');
        }
      });
      const hub = this.nodeMap.get('hub');
      if (hub) {
        this.pinNode(hub);
        this.createShockwave(hub.x, hub.y, hub.cluster);
      }
    }
  }

  // Auto-initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('tech-graph-canvas')) {
      window.techGraphEngine = new TechGraphEngine('tech-graph-canvas', 'tech-graph-canvas-wrap');
    }
  });

})();
