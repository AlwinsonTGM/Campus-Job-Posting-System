/**
 * Campus Job Posting System — Technical Architecture Knowledge Graph
 * Obsidian-Style Force-Directed Interactive Network Graph
 * Vanilla ES6+ Canvas 2D Implementation (Zero external dependencies)
 */

(function () {
  'use strict';

  // --------------------------------------------------------------------------
  // 1. GRAPH DATA SPECIFICATION (14 Technologies + 4 Clusters + 1 Central Hub)
  // --------------------------------------------------------------------------
  const GRAPH_DATA = {
    nodes: [
      // Central Platform Hub
      {
        id: 'hub',
        name: 'Campus Job Posting System',
        shortName: 'KLD System Hub',
        cluster: 'core',
        tag: 'Platform Ecosystem',
        icon: 'bi-mortarboard-fill',
        detail: 'Central student assistantship application, scheduling, and campus office hiring ecosystem built for Kolehiyo ng Lungsod ng Dasmariñas (KLD).',
        radius: 28,
        fixed: true
      },

      // Domain Cluster Nodes
      {
        id: 'c-frontend',
        name: '3D & Frontend UI',
        shortName: 'Frontend & 3D',
        cluster: 'frontend',
        tag: 'Domain Cluster',
        icon: 'bi-window-stack',
        detail: 'Hardware-accelerated Three.js robot kinematics, responsive Bootstrap layout, client-side entropy validation, and tactile paper tokens.',
        radius: 19
      },
      {
        id: 'c-backend',
        name: 'Core Backend & Server',
        shortName: 'Backend & Server',
        cluster: 'backend',
        tag: 'Domain Cluster',
        icon: 'bi-hdd-rack-fill',
        detail: 'Native PHP 8.2 modular architecture, multi-role RBAC authorization boundaries, Apache HTTP security headers, and live notification poller.',
        radius: 19
      },
      {
        id: 'c-data',
        name: 'Data & Persistence Engine',
        shortName: 'Data Engine',
        cluster: 'data',
        tag: 'Domain Cluster',
        icon: 'bi-database-fill-gear',
        detail: 'Relational ACID persistence via MySQL/MariaDB InnoDB, strict PHP PDO parameter binding, and atomic migration datasets.',
        radius: 19
      },
      {
        id: 'c-ai',
        name: 'AI Cloud & Security/QA',
        shortName: 'AI & Security',
        cluster: 'ai',
        tag: 'Domain Cluster',
        icon: 'bi-shield-shaded',
        detail: 'Dual-tier NVIDIA NIM cloud inference with local heuristic fallback, bcrypt cryptography, anti-CSRF defenses, and Playwright E2E suites.',
        radius: 19
      },

      // Leaf Nodes: Frontend & 3D
      {
        id: 'threejs',
        name: 'Three.js WebGL & GLTFLoader',
        shortName: 'Three.js 3D',
        cluster: 'frontend',
        tag: '3D Engine & Mascot',
        icon: 'bi-boxes',
        detail: 'Hardware-accelerated 3D mascot (cute_robot.glb) with 13 procedural kinematics & OLED visor shader.'
      },
      {
        id: 'css-tokens',
        name: 'Modular CSS3 Design Tokens',
        shortName: 'CSS3 Tokens',
        cluster: 'frontend',
        tag: 'Design System',
        icon: 'bi-palette',
        detail: '7 scoped domain stylesheets, tactile paper palette (#FBF9F4 canvas, #161616 ink), and zero pill clutter.'
      },
      {
        id: 'bootstrap',
        name: 'Bootstrap 5.3 + Icons 1.11',
        shortName: 'Bootstrap 5.3',
        cluster: 'frontend',
        tag: 'UI Framework',
        icon: 'bi-bootstrap-fill',
        detail: 'Accessible 12-column responsive layout, candidate review drawers, paper modals, and vector iconography.'
      },
      {
        id: 'vanillajs',
        name: 'Vanilla JavaScript (ES6+)',
        shortName: 'Vanilla JS',
        cluster: 'frontend',
        tag: 'Client Runtime',
        icon: 'bi-filetype-js',
        detail: 'Client-side Shannon password entropy meter, debounced spotlight search (Ctrl+K), and 3D Coverflow.'
      },

      // Leaf Nodes: Core Backend & Server
      {
        id: 'php',
        name: 'Native PHP 8.2+ Architecture',
        shortName: 'PHP 8.2+',
        cluster: 'backend',
        tag: 'Core Backend',
        icon: 'bi-filetype-php',
        detail: 'Modular template hierarchy, strict session lifecycle, and multi-role RBAC authorization boundaries.'
      },
      {
        id: 'apache',
        name: 'Apache 2.4 & XAMPP Stack',
        shortName: 'Apache 2.4',
        cluster: 'backend',
        tag: 'Web Server',
        icon: 'bi-hdd-network',
        detail: 'URL rewrite rules and strict HTTP security headers (X-Frame-Options, CSP, Referrer).'
      },
      {
        id: 'poller',
        name: 'Real-Time Notification Poller',
        shortName: 'Live Poller',
        cluster: 'backend',
        tag: 'Live State Sync',
        icon: 'bi-bell',
        detail: '30-second interval polling with visibilitychange lifecycle pause and floating alert toasts.'
      },

      // Leaf Nodes: Data & Persistence
      {
        id: 'mysql',
        name: 'MySQL 8.0 / MariaDB (InnoDB)',
        shortName: 'MySQL 8.0',
        cluster: 'data',
        tag: 'Relational Persistence',
        icon: 'bi-database-fill-check',
        detail: 'ACID transactional integrity, foreign key cascades, and utf8mb4 full unicode persistence.'
      },
      {
        id: 'pdo',
        name: 'PHP PDO Prepared Statements',
        shortName: 'PDO Layer',
        cluster: 'data',
        tag: 'Database Abstraction',
        icon: 'bi-link-45deg',
        detail: 'Strict parameter binding (zero SQL injection vulnerabilities) with singleton connection pool.'
      },
      {
        id: 'migrations',
        name: 'Atomic Migrations & Seeding',
        shortName: 'Migrations',
        cluster: 'data',
        tag: 'Database Pipeline',
        icon: 'bi-arrow-repeat',
        detail: 'Transactional migration engine (migrate.php) with Demo vs. Clean Slate dataset switcher.'
      },

      // Leaf Nodes: AI & Security/QA
      {
        id: 'nvidia',
        name: 'NVIDIA NIM AI Cloud Gateway',
        shortName: 'NVIDIA NIM',
        cluster: 'ai',
        tag: 'Campus AI Assistant',
        icon: 'bi-cpu',
        detail: 'Streaming inference via Llama-3.3-70B & DeepSeek-R1 with sliding multi-turn conversational memory.'
      },
      {
        id: 'fallback-ai',
        name: 'Local Heuristic Fallback',
        shortName: 'Offline AI',
        cluster: 'ai',
        tag: 'Resilience Engine',
        icon: 'bi-shield-shaded',
        detail: 'Zero-downtime offline intelligence via plural-aware regex and curated campus knowledge rules.'
      },
      {
        id: 'security',
        name: 'Bcrypt & Anti-CSRF Tokens',
        shortName: 'Bcrypt & CSRF',
        cluster: 'ai',
        tag: 'Security Architecture',
        icon: 'bi-shield-lock-fill',
        detail: 'Bcrypt password hashing, cryptographic session tokens on all state mutations, and IDOR boundary checks.'
      },
      {
        id: 'playwright',
        name: 'Playwright E2E Test Suite',
        shortName: 'Playwright E2E',
        cluster: 'ai',
        tag: 'QA & Automation',
        icon: 'bi-check-all',
        detail: 'TypeScript automated test suites across smoke, security fuzzing, half-screen viewports, and multi-role auth.'
      }
    ],

    edges: [
      // Central Hub to 4 Domain Clusters
      { source: 'hub', target: 'c-frontend', length: 110, width: 2.2 },
      { source: 'hub', target: 'c-backend', length: 110, width: 2.2 },
      { source: 'hub', target: 'c-data', length: 110, width: 2.2 },
      { source: 'hub', target: 'c-ai', length: 110, width: 2.2 },

      // Frontend Cluster to Leaves
      { source: 'c-frontend', target: 'threejs', length: 65, width: 1.4 },
      { source: 'c-frontend', target: 'css-tokens', length: 60, width: 1.4 },
      { source: 'c-frontend', target: 'bootstrap', length: 60, width: 1.4 },
      { source: 'c-frontend', target: 'vanillajs', length: 65, width: 1.4 },

      // Backend Cluster to Leaves
      { source: 'c-backend', target: 'php', length: 60, width: 1.4 },
      { source: 'c-backend', target: 'apache', length: 60, width: 1.4 },
      { source: 'c-backend', target: 'poller', length: 65, width: 1.4 },

      // Data Cluster to Leaves
      { source: 'c-data', target: 'mysql', length: 60, width: 1.4 },
      { source: 'c-data', target: 'pdo', length: 60, width: 1.4 },
      { source: 'c-data', target: 'migrations', length: 65, width: 1.4 },

      // AI & Security Cluster to Leaves
      { source: 'c-ai', target: 'nvidia', length: 65, width: 1.4 },
      { source: 'c-ai', target: 'fallback-ai', length: 60, width: 1.4 },
      { source: 'c-ai', target: 'security', length: 60, width: 1.4 },
      { source: 'c-ai', target: 'playwright', length: 65, width: 1.4 },

      // Cross-cluster interconnected relations (Obsidian Web aesthetic)
      { source: 'php', target: 'pdo', length: 75, width: 0.9, dashed: true },
      { source: 'nvidia', target: 'fallback-ai', length: 55, width: 0.9, dashed: true },
      { source: 'vanillajs', target: 'poller', length: 75, width: 0.9, dashed: true },
      { source: 'css-tokens', target: 'bootstrap', length: 55, width: 0.9, dashed: true }
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

      // Theme Colors Cache
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
        radius: n.radius || 12,
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

      // Initialize
      this.initDimensions();
      this.initPositions();
      this.bindEvents();
      this.pinNode(this.nodeMap.get('threejs') || this.nodes[0]);
      this.startLoop();
    }

    updateThemeColors() {
      const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
      this.theme = isDark ? 'dark' : 'light';

      if (isDark) {
        this.colors = {
          hubFill: '#2ECC5E',
          hubStroke: '#FFFFFF',
          hubText: '#161616',
          clusterFill: '#232328',
          clusterStroke: '#2ECC5E',
          clusterText: '#E8E6E3',
          leafFill: '#1A1A1E',
          leafStroke: '#2E2E33',
          leafStrokeHover: '#2ECC5E',
          leafText: '#C9C6C0',
          labelBg: 'rgba(26, 26, 30, 0.75)',
          edgeDefault: 'rgba(46, 46, 51, 0.75)',
          edgeHover: 'rgba(46, 204, 94, 0.9)',
          edgeDashed: 'rgba(155, 151, 143, 0.35)',
          dimOpacity: 0.15
        };
      } else {
        this.colors = {
          hubFill: '#161616',
          hubStroke: '#2ECC5E',
          hubText: '#FFFFFF',
          clusterFill: '#F1EBDC',
          clusterStroke: '#161616',
          clusterText: '#161616',
          leafFill: '#FFFFFF',
          leafStroke: '#E5E0D4',
          leafStrokeHover: '#2ECC5E',
          leafText: '#4A463F',
          labelBg: 'rgba(255, 255, 255, 0.75)',
          edgeDefault: 'rgba(229, 224, 212, 0.9)',
          edgeHover: 'rgba(46, 204, 94, 0.9)',
          edgeDashed: 'rgba(110, 106, 97, 0.35)',
          dimOpacity: 0.15
        };
      }
    }

    initDimensions() {
      const rect = this.canvas.parentElement.getBoundingClientRect();
      this.width = rect.width;
      this.height = rect.height || 460;

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

      // Cluster Angles (4 Quadrants with comfortable spacing)
      const clusterDist = Math.min(this.width, this.height) * 0.28;
      const clusterConfig = [
        { id: 'c-frontend', angle: -Math.PI * 0.75, dist: clusterDist }, // Top-Left
        { id: 'c-backend',  angle: -Math.PI * 0.25, dist: clusterDist }, // Top-Right
        { id: 'c-data',     angle: Math.PI * 0.25,  dist: clusterDist }, // Bottom-Right
        { id: 'c-ai',       angle: Math.PI * 0.75,  dist: clusterDist }  // Bottom-Left
      ];

      clusterConfig.forEach(cfg => {
        const cluster = this.nodeMap.get(cfg.id);
        if (cluster) {
          cluster.x = cx + Math.cos(cfg.angle) * cfg.dist;
          cluster.y = cy + Math.sin(cfg.angle) * cfg.dist;
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
        const arc = Math.PI * 0.65;
        const startAngle = baseAngle - arc / 2;
        const leafDist = 65;

        leaves.forEach((leaf, idx) => {
          const angle = (count > 1) ? (startAngle + (idx / (count - 1)) * arc) : baseAngle;
          leaf.x = clusterNode.x + Math.cos(angle) * leafDist;
          leaf.y = clusterNode.y + Math.sin(angle) * leafDist;
          leaf.baseX = leaf.x;
          leaf.baseY = leaf.y;
        });
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
        hub.x += (cx - hub.x) * 0.1;
        hub.y += (cy - hub.y) * 0.1;
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
        const targetLen = edge.length || 65;
        const diff = (dist - targetLen) / dist;
        const force = diff * 0.04;

        if (!s.isDragging && !s.fixed) {
          s.vx += dx * force;
          s.vy += dy * force;
        }
        if (!t.isDragging && !t.fixed) {
          t.vx -= dx * force;
          t.vy -= dy * force;
        }
      }

      // 3. Node Repulsion (Coulomb's Law)
      for (let i = 0; i < this.nodes.length; i++) {
        for (let j = i + 1; j < this.nodes.length; j++) {
          const a = this.nodes[i];
          const b = this.nodes[j];
          const dx = b.x - a.x;
          const dy = b.y - a.y;
          const distSq = dx * dx + dy * dy;
          const dist = Math.sqrt(distSq) || 1;
          const minDist = a.radius + b.radius + 26;

          if (dist < minDist) {
            const repel = (minDist - dist) / dist * 0.12;
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

      // 4. Update Node Positions + Subtle Obsidian Organic Ambient Drift
      for (let i = 0; i < this.nodes.length; i++) {
        const n = this.nodes[i];
        if (n.isDragging) continue;

        if (!n.fixed) {
          // Gentle spring pull toward base anchor to maintain balanced cluster layout
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
          const pad = n.radius + 12;
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

      // Render Edges
      for (let i = 0; i < this.edges.length; i++) {
        const edge = this.edges[i];
        const s = edge.sourceNode;
        const t = edge.targetNode;
        if (!s || !t) continue;

        const isHighlighted = (this.hoveredNode && (s.id === this.hoveredNode.id || t.id === this.hoveredNode.id)) ||
                              (this.pinnedNode && (s.id === this.pinnedNode.id || t.id === this.pinnedNode.id));

        const isDimmed = this.activeCluster !== 'all' &&
                         s.cluster !== this.activeCluster &&
                         t.cluster !== this.activeCluster &&
                         s.id !== 'hub';

        this.ctx.save();
        this.ctx.beginPath();
        this.ctx.moveTo(s.x, s.y);
        this.ctx.lineTo(t.x, t.y);

        if (edge.dashed) {
          this.ctx.setLineDash([3, 3]);
          this.ctx.strokeStyle = isHighlighted ? this.colors.edgeHover : this.colors.edgeDashed;
        } else {
          this.ctx.strokeStyle = isHighlighted ? this.colors.edgeHover : this.colors.edgeDefault;
        }

        this.ctx.globalAlpha = isDimmed ? this.colors.dimOpacity : (isHighlighted ? 1.0 : 0.85);
        this.ctx.lineWidth = isHighlighted ? edge.width + 1.2 : edge.width;
        this.ctx.stroke();
        this.ctx.restore();
      }

      // Render Nodes
      for (let i = 0; i < this.nodes.length; i++) {
        const n = this.nodes[i];
        const isHovered = this.hoveredNode && this.hoveredNode.id === n.id;
        const isPinned = this.pinnedNode && this.pinnedNode.id === n.id;
        const isDimmed = this.activeCluster !== 'all' && n.cluster !== this.activeCluster && n.id !== 'hub';

        this.ctx.save();
        this.ctx.globalAlpha = isDimmed ? this.colors.dimOpacity : 1.0;

        // Outer glow on hover or pinned
        if (isHovered || isPinned) {
          this.ctx.beginPath();
          this.ctx.arc(n.x, n.y, n.radius + 6, 0, Math.PI * 2);
          this.ctx.fillStyle = isPinned ? 'rgba(46, 204, 94, 0.25)' : 'rgba(46, 204, 94, 0.15)';
          this.ctx.fill();
        }

        // Main Node Circle
        this.ctx.beginPath();
        const r = isHovered ? n.radius + 2 : n.radius;
        this.ctx.arc(n.x, n.y, r, 0, Math.PI * 2);

        if (n.id === 'hub') {
          this.ctx.fillStyle = this.colors.hubFill;
          this.ctx.strokeStyle = this.colors.hubStroke;
          this.ctx.lineWidth = 3;
        } else if (n.id.startsWith('c-')) {
          this.ctx.fillStyle = this.colors.clusterFill;
          this.ctx.strokeStyle = this.colors.clusterStroke;
          this.ctx.lineWidth = 2.5;
        } else {
          this.ctx.fillStyle = this.colors.leafFill;
          this.ctx.strokeStyle = (isHovered || isPinned) ? this.colors.leafStrokeHover : this.colors.leafStroke;
          this.ctx.lineWidth = (isHovered || isPinned) ? 2.5 : 1.5;
        }

        this.ctx.fill();
        this.ctx.stroke();

        // Node Label Rendering
        this.ctx.textAlign = 'center';
        this.ctx.textBaseline = 'top';

        if (n.id === 'hub') {
          this.ctx.fillStyle = this.colors.hubText;
          this.ctx.font = 'bold 11px Inter, sans-serif';
          this.ctx.fillText('KLD', n.x, n.y - 12);
          this.ctx.font = '700 8.5px Inter, sans-serif';
          this.ctx.fillText('HUB', n.x, n.y + 1);
        } else if (n.id.startsWith('c-')) {
          this.ctx.font = 'bold 10px Inter, sans-serif';
          this.ctx.fillStyle = this.colors.clusterText;
          this.ctx.fillText(n.shortName, n.x, n.y + r + 4);
        } else {
          // Leaf label: crisp, with subtle glow when active
          this.ctx.font = (isHovered || isPinned) ? 'bold 9.5px Inter, sans-serif' : '500 8.5px Inter, sans-serif';
          this.ctx.fillStyle = (isHovered || isPinned) ? this.colors.leafStrokeHover : this.colors.leafText;
          this.ctx.fillText(n.shortName, n.x, n.y + r + 4);
        }

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
        if (dx * dx + dy * dy <= (n.radius + 8) * (n.radius + 8)) {
          return n;
        }
      }
      return null;
    }

    bindEvents() {
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
      this.tooltip.querySelector('.tech-graph-tooltip-tag').textContent = node.tag || 'Component';
      this.tooltip.querySelector('.tech-graph-tooltip-title').textContent = node.name;
      this.tooltip.querySelector('.tech-graph-tooltip-desc').textContent = node.detail;
      this.tooltip.classList.add('is-visible');
      this.updateTooltip(node, x, y);
    }

    updateTooltip(node, x, y) {
      if (!this.tooltip) return;
      const pad = 120;
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

      const iconEl = this.inspector.querySelector('.tech-graph-inspector-icon i');
      const tagEl = this.inspector.querySelector('.tech-graph-inspector-tag');
      const clusterEl = this.inspector.querySelector('.tech-graph-inspector-cluster');
      const titleEl = this.inspector.querySelector('.tech-graph-inspector-title');
      const descEl = this.inspector.querySelector('.tech-graph-inspector-desc');

      if (iconEl) iconEl.className = 'bi ' + (node.icon || 'bi-cpu');
      if (tagEl) tagEl.textContent = node.tag || 'Component';
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
      if (titleEl) titleEl.textContent = node.name;
      if (descEl) descEl.textContent = node.detail;
    }

    setFilter(clusterId) {
      this.activeCluster = clusterId;
      if (clusterId !== 'all') {
        const clusterNode = this.nodeMap.get('c-' + clusterId);
        if (clusterNode) this.pinNode(clusterNode);
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
      if (hub) this.pinNode(hub);
    }
  }

  // Auto-initialize when DOM is ready
  document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('tech-graph-canvas')) {
      window.techGraphEngine = new TechGraphEngine('tech-graph-canvas', 'tech-graph-canvas-wrap');
    }
  });

})();
