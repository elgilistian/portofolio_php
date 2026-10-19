/**
 * SPATIAL 3D PORTFOLIO ENGINE v3.0
 * Real-time 3D Mesh Projection, Tactile Keycaps, Web Audio Synth, CLI & Command Center
 */

let sfxEnabled = true;
let audioCtx = null;

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initSfxSystem();
  initCustomCursor();
  initCyberCanvas();
  initHero3DStage();
  initScrollProgress();
  initStickyHeaderAndDock();
  initMobileMenu();
  initTypewriter();
  initDevTerminal();
  initBlueprintPipeline();
  initHistoryFilter();
  initKeycapsMatrix();
  initCommandPalette();
  initSpotlightAndTilt();
  initMagneticButtons();
  initProjectsFilter();
  initProjectSlider();
  initSkillBars();
  initProfileModal();
  initProjectModal();
  initClipboardToast();
  initBackToTop();
  initLiveClock();
  initFormInteractions();
});

/* ----------------------------------------------------
   0. WEB AUDIO API SYNTHESIZER (Tactile Mechanical & HUD SFX)
   ---------------------------------------------------- */
function initSfxSystem() {
  const sfxBtn = document.getElementById('sfx-toggle');
  if (!sfxBtn) return;

  sfxBtn.addEventListener('click', () => {
    sfxEnabled = !sfxEnabled;
    sfxBtn.classList.toggle('sfx-active', sfxEnabled);
    sfxBtn.style.opacity = sfxEnabled ? '1' : '0.45';
    if (sfxEnabled) playSynthSfx('click');
    showToast(`AUDIO_SYNTH // ${sfxEnabled ? 'ENABLED 🔊' : 'MUTED 🔇'}`);
  });
}

function playSynthSfx(type = 'tick') {
  if (!sfxEnabled) return;
  try {
    if (!audioCtx) {
      const AudioContextClass = window.AudioContext || window.webkitAudioContext;
      if (!AudioContextClass) return;
      audioCtx = new AudioContextClass();
    }
    if (audioCtx.state === 'suspended') {
      audioCtx.resume();
    }

    const osc = audioCtx.createOscillator();
    const gain = audioCtx.createGain();
    const now = audioCtx.currentTime;

    if (type === 'keycap') {
      // Tactile mechanical switch thock
      osc.type = 'triangle';
      osc.frequency.setValueAtTime(190, now);
      osc.frequency.exponentialRampToValueAtTime(55, now + 0.045);
      gain.gain.setValueAtTime(0.08, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.045);
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.start(now);
      osc.stop(now + 0.05);
    } else if (type === 'click') {
      osc.type = 'sine';
      osc.frequency.setValueAtTime(520, now);
      osc.frequency.exponentialRampToValueAtTime(880, now + 0.04);
      gain.gain.setValueAtTime(0.05, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.045);
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.start(now);
      osc.stop(now + 0.05);
    } else {
      // Subtle tick
      osc.type = 'sine';
      osc.frequency.setValueAtTime(640, now);
      gain.gain.setValueAtTime(0.02, now);
      gain.gain.exponentialRampToValueAtTime(0.001, now + 0.02);
      osc.connect(gain);
      gain.connect(audioCtx.destination);
      osc.start(now);
      osc.stop(now + 0.025);
    }
  } catch (_) {}
}

/* ----------------------------------------------------
   1. PRECISION CUSTOM CURSOR & XY TELEMETRY
   ---------------------------------------------------- */
function initCustomCursor() {
  const dot = document.getElementById('cursor-dot');
  const ring = document.getElementById('cursor-ring');
  const coordsEl = document.getElementById('hud-coords');

  let mouseX = window.innerWidth / 2;
  let mouseY = window.innerHeight / 2;
  let ringX = mouseX;
  let ringY = mouseY;

  window.addEventListener('mousemove', (e) => {
    mouseX = e.clientX;
    mouseY = e.clientY;

    if (dot) {
      dot.style.left = `${mouseX}px`;
      dot.style.top = `${mouseY}px`;
    }
    if (coordsEl) {
      coordsEl.textContent = `${String(mouseX).padStart(3, '0')}, ${String(mouseY).padStart(3, '0')}`;
    }
  }, { passive: true });

  if (ring && window.innerWidth >= 992) {
    function renderCursor() {
      ringX += (mouseX - ringX) * 0.18;
      ringY += (mouseY - ringY) * 0.18;
      ring.style.left = `${ringX}px`;
      ring.style.top = `${ringY}px`;
      requestAnimationFrame(renderCursor);
    }
    renderCursor();

    document.querySelectorAll('a, button, .keycap-card, .iso-layer, input, textarea').forEach(el => {
      el.addEventListener('mouseenter', () => ring.classList.add('active'));
      el.addEventListener('mouseleave', () => ring.classList.remove('active'));
    });
  }
}

/* ----------------------------------------------------
   2. REAL-TIME 3D INTERACTIVE WIREFRAME CORE (HERO STAGE)
   ---------------------------------------------------- */
function initHero3DStage() {
  const canvas = document.getElementById('hero-3d-canvas');
  const viewport = document.getElementById('stage-3d-viewport');
  const geoBtns = document.querySelectorAll('.geo-btn');
  if (!canvas || !viewport) return;

  const ctx = canvas.getContext('2d');
  let width = 0;
  let height = 0;

  function resize() {
    const rect = viewport.getBoundingClientRect();
    width = canvas.width = rect.width || 420;
    height = canvas.height = rect.height || 310;
  }
  resize();
  window.addEventListener('resize', resize, { passive: true });

  // 3D Geometry Generators
  function createIcosahedron() {
    const t = (1 + Math.sqrt(5)) / 2;
    const rawV = [
      [-1, t, 0], [1, t, 0], [-1, -t, 0], [1, -t, 0],
      [0, -1, t], [0, 1, t], [0, -1, -t], [0, 1, -t],
      [t, 0, -1], [t, 0, 1], [-t, 0, -1], [-t, 0, 1]
    ];
    const vertices = rawV.map(([x, y, z]) => {
      const len = Math.sqrt(x * x + y * y + z * z);
      return { x: (x / len) * 115, y: (y / len) * 115, z: (z / len) * 115 };
    });
    // Add inner core vertices
    rawV.forEach(([x, y, z]) => {
      const len = Math.sqrt(x * x + y * y + z * z);
      vertices.push({ x: (x / len) * 55, y: (y / len) * 55, z: (z / len) * 55 });
    });

    const edges = [];
    for (let i = 0; i < 12; i++) {
      for (let j = i + 1; j < 12; j++) {
        const dx = vertices[i].x - vertices[j].x;
        const dy = vertices[i].y - vertices[j].y;
        const dz = vertices[i].z - vertices[j].z;
        const d = Math.sqrt(dx * dx + dy * dy + dz * dz);
        if (d < 135) edges.push([i, j]);
      }
      // Connect outer to inner core
      edges.push([i, i + 12]);
    }
    return { vertices, edges };
  }

  function createTorusKnot() {
    const vertices = [];
    const edges = [];
    const steps = 96;
    const p = 2, q = 3;
    for (let i = 0; i < steps; i++) {
      const t = (i / steps) * Math.PI * 2;
      const r = 52 * (2 + Math.cos(q * t));
      const x = r * Math.cos(p * t) * 0.72;
      const y = r * Math.sin(p * t) * 0.72;
      const z = -52 * Math.sin(q * t) * 1.1;
      vertices.push({ x, y, z });
      edges.push([i, (i + 1) % steps]);
      if (i % 2 === 0) {
        edges.push([i, (i + 8) % steps]);
      }
    }
    return { vertices, edges };
  }

  function createMatrixSphere() {
    const vertices = [];
    const edges = [];
    const count = 64;
    const phi = Math.PI * (3 - Math.sqrt(5));
    for (let i = 0; i < count; i++) {
      const y = 1 - (i / (count - 1)) * 2;
      const radius = Math.sqrt(1 - y * y);
      const theta = phi * i;
      const x = Math.cos(theta) * radius;
      const z = Math.sin(theta) * radius;
      vertices.push({ x: x * 112, y: y * 112, z: z * 112 });
    }
    for (let i = 0; i < count; i++) {
      for (let j = i + 1; j < count; j++) {
        const dx = vertices[i].x - vertices[j].x;
        const dy = vertices[i].y - vertices[j].y;
        const dz = vertices[i].z - vertices[j].z;
        if (Math.sqrt(dx * dx + dy * dy + dz * dz) < 58) {
          edges.push([i, j]);
        }
      }
    }
    return { vertices, edges };
  }

  const meshes = {
    icosa: createIcosahedron(),
    torus: createTorusKnot(),
    sphere: createMatrixSphere()
  };

  let activeMesh = meshes.icosa;
  let rotX = 0.35;
  let rotY = 0.55;
  let velX = 0.004;
  let velY = 0.007;
  let isDragging = false;
  let lastX = 0;
  let lastY = 0;

  window.switchHeroGeometry = function (type) {
    if (meshes[type]) {
      activeMesh = meshes[type];
      geoBtns.forEach(b => b.classList.toggle('active', b.getAttribute('data-geo') === type));
      playSynthSfx('click');
    }
  };

  geoBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const g = btn.getAttribute('data-geo');
      window.switchHeroGeometry(g);
    });
  });

  viewport.addEventListener('mousedown', (e) => {
    isDragging = true;
    lastX = e.clientX;
    lastY = e.clientY;
  });

  window.addEventListener('mouseup', () => {
    isDragging = false;
  });

  window.addEventListener('mousemove', (e) => {
    if (!isDragging) return;
    const dx = e.clientX - lastX;
    const dy = e.clientY - lastY;
    lastX = e.clientX;
    lastY = e.clientY;
    velY = dx * 0.008;
    velX = dy * 0.008;
    rotY += velY;
    rotX += velX;
  });

  function render3D() {
    ctx.clearRect(0, 0, width, height);

    if (!isDragging) {
      rotX += velX;
      rotY += velY;
      velX += (0.0035 - velX) * 0.03;
      velY += (0.0065 - velY) * 0.03;
    }

    const accentRgb = getComputedStyle(document.documentElement)
      .getPropertyValue('--accent-rgb')
      .trim() || '0, 240, 255';

    const cx = width / 2;
    const cy = height / 2;
    const fov = 340;

    const cosX = Math.cos(rotX), sinX = Math.sin(rotX);
    const cosY = Math.cos(rotY), sinY = Math.sin(rotY);

    const projected = activeMesh.vertices.map(v => {
      // Rotate Y
      let x1 = v.x * cosY - v.z * sinY;
      let z1 = v.z * cosY + v.x * sinY;
      // Rotate X
      let y2 = v.y * cosX - z1 * sinX;
      let z2 = z1 * cosX + v.y * sinX;

      const scale = fov / (fov + z2 + 180);
      return {
        x: cx + x1 * scale,
        y: cy + y2 * scale,
        z: z2,
        scale
      };
    });

    // Draw 3D Wireframe Edges
    for (let i = 0; i < activeMesh.edges.length; i++) {
      const [a, b] = activeMesh.edges[i];
      const p1 = projected[a];
      const p2 = projected[b];
      const depthAlpha = Math.max(0.12, Math.min(0.75, ((p1.z + p2.z) / 2 + 130) / 240));

      ctx.beginPath();
      ctx.moveTo(p1.x, p1.y);
      ctx.lineTo(p2.x, p2.y);
      ctx.strokeStyle = `rgba(${accentRgb}, ${depthAlpha})`;
      ctx.lineWidth = 1.25;
      ctx.stroke();
    }

    // Draw Glowing Vertices
    for (let i = 0; i < projected.length; i++) {
      const p = projected[i];
      const r = Math.max(1.2, p.scale * 3.2);
      ctx.beginPath();
      ctx.arc(p.x, p.y, r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(${accentRgb}, 0.9)`;
      ctx.fill();
    }

    requestAnimationFrame(render3D);
  }

  render3D();
}

/* ----------------------------------------------------
   3. AMBIENT CONSTELLATION BACKGROUND CANVAS
   ---------------------------------------------------- */
function initCyberCanvas() {
  const canvas = document.getElementById('cyber-canvas');
  if (!canvas) return;
  const ctx = canvas.getContext('2d');
  if (!ctx) return;

  let width = (canvas.width = window.innerWidth);
  let height = (canvas.height = window.innerHeight);

  const particles = [];
  const count = window.innerWidth < 768 ? 24 : 45;

  for (let i = 0; i < count; i++) {
    particles.push({
      x: Math.random() * width,
      y: Math.random() * height,
      vx: (Math.random() - 0.5) * 0.4,
      vy: (Math.random() - 0.5) * 0.4,
      r: Math.random() * 1.5 + 0.7
    });
  }

  window.addEventListener('resize', () => {
    width = canvas.width = window.innerWidth;
    height = canvas.height = window.innerHeight;
  }, { passive: true });

  function animate() {
    ctx.clearRect(0, 0, width, height);
    const accentRgb = getComputedStyle(document.documentElement)
      .getPropertyValue('--accent-rgb')
      .trim() || '0, 240, 255';

    for (let i = 0; i < particles.length; i++) {
      const p = particles[i];
      p.x += p.vx;
      p.y += p.vy;
      if (p.x < 0 || p.x > width) p.vx *= -1;
      if (p.y < 0 || p.y > height) p.vy *= -1;

      ctx.beginPath();
      ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
      ctx.fillStyle = `rgba(${accentRgb}, 0.55)`;
      ctx.fill();

      for (let j = i + 1; j < particles.length; j++) {
        const p2 = particles[j];
        const dx = p.x - p2.x;
        const dy = p.y - p2.y;
        const dist = Math.sqrt(dx * dx + dy * dy);
        if (dist < 135) {
          ctx.beginPath();
          ctx.moveTo(p.x, p.y);
          ctx.lineTo(p2.x, p2.y);
          ctx.strokeStyle = `rgba(${accentRgb}, ${(1 - dist / 135) * 0.16})`;
          ctx.stroke();
        }
      }
    }
    requestAnimationFrame(animate);
  }
  animate();
}

/* ----------------------------------------------------
   4. INTERACTIVE ARCHITECTURE PIPELINE & ISOMETRIC 3D STACK
   ---------------------------------------------------- */
function initBlueprintPipeline() {
  const nodes = document.querySelectorAll('.pipe-node');
  const isoLayers = document.querySelectorAll('.iso-layer');
  const inspector = document.getElementById('pipe-inspector');
  if (!nodes.length || !inspector) return;

  function activateStep(idx) {
    const node = nodes[idx];
    if (!node) return;
    nodes.forEach((n, i) => n.classList.toggle('active', i === idx));
    const step = node.getAttribute('data-step') || '01';
    const title = node.getAttribute('data-title') || '';
    const desc = node.getAttribute('data-desc') || '';
    inspector.innerHTML = `<strong>[LAYER_${escapeHtml(step)}] ${escapeHtml(title)}</strong><span>${escapeHtml(desc)}</span>`;
    playSynthSfx('click');
  }

  nodes.forEach((node, idx) => {
    node.addEventListener('click', () => activateStep(idx));
  });

  isoLayers.forEach(layer => {
    layer.addEventListener('click', () => {
      const idx = parseInt(layer.getAttribute('data-pipe-index') || '0', 10);
      activateStep(idx);
    });
  });
}

/* ----------------------------------------------------
   5. 3D TACTILE MECHANICAL KEYCAPS & RADIAL GAUGE
   ---------------------------------------------------- */
function initKeycapsMatrix() {
  const keycaps = document.querySelectorAll('.keycap-card');
  const gaugeCircle = document.getElementById('gauge-circle');
  const gaugePct = document.getElementById('gauge-pct');
  const gaugeTitle = document.getElementById('gauge-title');
  const gaugeCat = document.getElementById('gauge-cat');

  if (!keycaps.length) return;
  const circumference = 2 * Math.PI * 60; // r=60 => ~377

  function updateGauge(card) {
    keycaps.forEach(k => k.classList.remove('active-cap'));
    card.classList.add('active-cap');

    const name = card.getAttribute('data-skill-name') || 'Skill';
    const cat = card.getAttribute('data-skill-cat') || 'Stack';
    const level = parseInt(card.getAttribute('data-skill-level') || '85', 10);

    if (gaugeTitle) gaugeTitle.textContent = name;
    if (gaugeCat) gaugeCat.textContent = `LAYER // ${cat.toUpperCase()}`;
    if (gaugePct) gaugePct.textContent = `${level}%`;
    if (gaugeCircle) {
      const offset = circumference - (level / 100) * circumference;
      gaugeCircle.style.strokeDashoffset = `${offset}`;
    }
  }

  // Initialize first keycap
  updateGauge(keycaps[0]);

  keycaps.forEach(card => {
    card.addEventListener('click', () => {
      playSynthSfx('keycap');
      updateGauge(card);
    });
    card.addEventListener('mouseenter', () => {
      playSynthSfx('tick');
      updateGauge(card);
    });
  });
}

/* ----------------------------------------------------
   6. THEME SWITCHER
   ---------------------------------------------------- */
function initTheme() {
  const themeToggle = document.getElementById('theme-toggle');
  const storedTheme = localStorage.getItem('portfolio_theme') || 'dark';
  applyTheme(storedTheme);

  if (themeToggle) {
    themeToggle.addEventListener('click', toggleThemeMode);
  }
}

function toggleThemeMode() {
  const activeTheme = document.documentElement.getAttribute('data-theme') || 'dark';
  const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
  applyTheme(newTheme);
  localStorage.setItem('portfolio_theme', newTheme);
  playSynthSfx('click');
  showToast(`SYS_THEME // ${newTheme.toUpperCase()} MODE`);
}

function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    const sun = themeToggle.querySelector('.icon-sun');
    const moon = themeToggle.querySelector('.icon-moon');
    if (sun && moon) {
      sun.style.display = theme === 'dark' ? 'block' : 'none';
      moon.style.display = theme === 'dark' ? 'none' : 'block';
    }
  }
}

/* ----------------------------------------------------
   7. SCROLL PROGRESS, STICKY HEADER & SPATIAL DOCK
   ---------------------------------------------------- */
function initScrollProgress() {
  const bar = document.getElementById('scroll-progress');
  if (!bar) return;

  window.addEventListener('scroll', () => {
    const total = document.documentElement.scrollHeight - window.innerHeight;
    bar.style.width = `${total > 0 ? (window.scrollY / total) * 100 : 0}%`;
  }, { passive: true });
}

function initStickyHeaderAndDock() {
  const header = document.querySelector('.site-header');
  const dockLinks = document.querySelectorAll('.spatial-dock .dock-item, .mobile-links .mobile-link');
  const sections = document.querySelectorAll('section[id]');

  window.addEventListener('scroll', () => {
    if (header) header.classList.toggle('scrolled', window.scrollY > 30);

    let currentId = 'home';
    const scrollPos = window.scrollY + 180;
    sections.forEach(sec => {
      if (scrollPos >= sec.offsetTop && scrollPos < sec.offsetTop + sec.offsetHeight) {
        currentId = sec.getAttribute('id');
      }
    });

    dockLinks.forEach(link => {
      link.classList.toggle('active', link.getAttribute('href') === `#${currentId}`);
    });
  }, { passive: true });
}

function initMobileMenu() {
  const toggle = document.querySelector('.nav-toggle');
  const drawer = document.querySelector('.mobile-nav-drawer');
  const backdrop = document.querySelector('.mobile-nav-backdrop');
  const links = document.querySelectorAll('.mobile-link');
  if (!toggle || !drawer || !backdrop) return;

  function closeMenu() {
    toggle.classList.remove('open');
    drawer.classList.remove('open');
    backdrop.classList.remove('open');
    document.body.style.overflow = '';
  }

  toggle.addEventListener('click', () => {
    const isOpen = drawer.classList.toggle('open');
    toggle.classList.toggle('open', isOpen);
    backdrop.classList.toggle('open', isOpen);
    document.body.style.overflow = isOpen ? 'hidden' : '';
  });

  backdrop.addEventListener('click', closeMenu);
  links.forEach(l => l.addEventListener('click', closeMenu));
}

/* ----------------------------------------------------
   8. TYPEWRITER & LIVE CLI TERMINAL
   ---------------------------------------------------- */
function initTypewriter() {
  const el = document.getElementById('typewriter-text');
  if (!el) return;

  let words = ['Full-Stack Developer', 'Informatics Engineering', 'System Architect'];
  try {
    const parsed = JSON.parse(el.getAttribute('data-words') || '[]');
    if (Array.isArray(parsed) && parsed.length) words = parsed;
  } catch (_) {}

  let wIdx = 0, cIdx = 0, deleting = false;

  function tick() {
    const full = `"${words[wIdx]}"`;
    cIdx += deleting ? -1 : 1;
    el.textContent = full.substring(0, cIdx);

    let speed = deleting ? 38 : 85;
    if (!deleting && cIdx === full.length) {
      speed = 2000;
      deleting = true;
    } else if (deleting && cIdx === 0) {
      deleting = false;
      wIdx = (wIdx + 1) % words.length;
      speed = 320;
    }
    setTimeout(tick, speed);
  }
  setTimeout(tick, 400);
}

function initDevTerminal() {
  const term = document.getElementById('hero-terminal');
  const output = document.getElementById('terminal-output');
  const form = document.getElementById('terminal-form');
  const input = document.getElementById('terminal-input');
  const chips = document.querySelectorAll('.term-chip');
  if (!term || !output || !form || !input) return;

  let cliData = {};
  try {
    cliData = JSON.parse(term.getAttribute('data-cli') || '{}');
  } catch (_) {}

  function runCommand(raw) {
    const cmd = raw.trim().toLowerCase();
    if (!cmd) return;
    playSynthSfx('keycap');

    if (cmd === 'clear' || cmd === 'cls') {
      output.innerHTML = '';
      return;
    }

    let res = '';
    if (cmd === 'help') {
      res = `Perintah: <span style="color:var(--accent)">whoami</span>, <span style="color:var(--accent)">profile</span>, <span style="color:var(--accent)">skills</span>, <span style="color:var(--accent)">projects</span>, <span style="color:var(--accent)">torus</span>, <span style="color:var(--accent)">sphere</span>, <span style="color:var(--accent)">theme</span>, <span style="color:var(--accent)">clear</span>`;
    } else if (cmd === 'whoami' || cmd === 'profile') {
      if (window.openProfileModal) window.openProfileModal();
      res = `<strong>${escapeHtml(cliData.name || '')}</strong> — ${escapeHtml(cliData.role || '')} (${escapeHtml(cliData.location || '')}) [Membuka Modal Profil...]`;
    } else if (cmd === 'skills' || cmd === 'stack') {
      res = `Active Modules:<br>• ${(cliData.skills || []).map(escapeHtml).join('<br>• ')}`;
    } else if (cmd === 'projects' || cmd === 'ls') {
      res = `Deployed Works:<br>• ${(cliData.projects || []).map(escapeHtml).join('<br>• ')}`;
    } else if (cmd === 'torus' || cmd === '3d:torus') {
      if (window.switchHeroGeometry) window.switchHeroGeometry('torus');
      res = `[3D_ENGINE] Mesh morphed to TORUS_KNOT.`;
    } else if (cmd === 'sphere' || cmd === '3d:sphere') {
      if (window.switchHeroGeometry) window.switchHeroGeometry('sphere');
      res = `[3D_ENGINE] Mesh morphed to MATRIX_ORB.`;
    } else if (cmd === 'theme') {
      toggleThemeMode();
      res = `Theme mode switched.`;
    } else {
      res = `zsh: command not found: <strong>${escapeHtml(cmd)}</strong>. Ketik <span style="color:var(--accent)">help</span>.`;
    }

    const line = document.createElement('div');
    line.className = 'term-line';
    line.innerHTML = `<span class="term-prompt">➜</span><span class="term-cmd">${escapeHtml(raw)}</span>`;
    output.appendChild(line);

    const out = document.createElement('div');
    out.className = 'term-output';
    out.innerHTML = res;
    output.appendChild(out);
    output.scrollTop = output.scrollHeight;
  }

  form.addEventListener('submit', (e) => {
    e.preventDefault();
    const v = input.value;
    input.value = '';
    runCommand(v);
  });

  chips.forEach(c => {
    c.addEventListener('click', () => runCommand(c.getAttribute('data-cmd') || ''));
  });
}

function escapeHtml(str) {
  return String(str).replace(/[&<>"']/g, s => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
  }[s]));
}

/* ----------------------------------------------------
   8B. 3D HOLOGRAPHIC PROFILE MODAL
   ---------------------------------------------------- */
function initProfileModal() {
  const modal = document.getElementById('profile-modal');
  const openBtn = document.getElementById('btn-open-profile');
  const closeBtn = document.getElementById('profile-modal-close');
  const contactBtn = document.getElementById('profile-contact-btn');

  if (!modal) return;

  function openProfile() {
    playSynthSfx('keycap');
    modal.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeProfile() {
    modal.classList.remove('open');
    document.body.style.overflow = '';
  }

  window.openProfileModal = openProfile;

  if (openBtn) {
    openBtn.addEventListener('click', openProfile);
  }
  if (closeBtn) {
    closeBtn.addEventListener('click', closeProfile);
  }
  if (contactBtn) {
    contactBtn.addEventListener('click', () => {
      closeProfile();
    });
  }

  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeProfile();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('open')) {
      closeProfile();
    }
  });
}

/* ----------------------------------------------------
   8C. RIWAYAT TIMELINE CATEGORY FILTER
   ---------------------------------------------------- */
function initHistoryFilter() {
  const btns = document.querySelectorAll('.history-filter-btn');
  const items = document.querySelectorAll('.history-item');
  if (!btns.length || !items.length) return;

  btns.forEach(btn => {
    btn.addEventListener('click', () => {
      playSynthSfx('click');
      const filter = btn.getAttribute('data-hist-filter') || 'all';
      btns.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');

      items.forEach(item => {
        const type = item.getAttribute('data-hist-type') || '';
        const match = filter === 'all' || type.toLowerCase() === filter.toLowerCase();
        item.classList.toggle('is-hidden', !match);
      });
    });
  });
}

/* ----------------------------------------------------
   9. COMMAND PALETTE MODAL (Ctrl+K)
   ---------------------------------------------------- */
function initCommandPalette() {
  const modal = document.getElementById('cmd-modal');
  const trigger = document.getElementById('cmd-trigger');
  const input = document.getElementById('cmd-input');
  const items = Array.from(document.querySelectorAll('#cmd-list .cmd-item'));
  if (!modal || !input || !items.length) return;

  let selectedIndex = 0;
  const getVisible = () => items.filter(i => i.style.display !== 'none');

  function syncSelection() {
    getVisible().forEach((item, idx) => item.classList.toggle('selected', idx === selectedIndex));
  }

  function openPalette() {
    playSynthSfx('click');
    modal.classList.add('open');
    input.value = '';
    items.forEach(i => (i.style.display = ''));
    selectedIndex = 0;
    syncSelection();
    setTimeout(() => input.focus(), 40);
  }

  function closePalette() {
    modal.classList.remove('open');
  }

  function executeItem(item) {
    if (!item) return;
    const action = item.getAttribute('data-action');
    closePalette();
    playSynthSfx('keycap');

    if (action === 'profile') {
      if (window.openProfileModal) window.openProfileModal();
    } else if (action === 'goto') {
      const target = document.querySelector(item.getAttribute('data-target') || '#home');
      if (target) target.scrollIntoView({ behavior: 'smooth' });
    } else if (action === 'geo-torus') {
      if (window.switchHeroGeometry) window.switchHeroGeometry('torus');
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } else if (action === 'theme') {
      toggleThemeMode();
    } else if (action === 'copy-email') {
      const email = item.getAttribute('data-email') || '';
      if (email && navigator.clipboard) {
        navigator.clipboard.writeText(email);
        showToast(`✓ Email "${email}" disalin!`);
      }
    } else if (action === 'admin') {
      window.location.href = 'admin/login.php';
    }
  }

  if (trigger) trigger.addEventListener('click', openPalette);
  modal.addEventListener('click', (e) => { if (e.target === modal) closePalette(); });

  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
      e.preventDefault();
      modal.classList.contains('open') ? closePalette() : openPalette();
    } else if (modal.classList.contains('open')) {
      const visible = getVisible();
      if (e.key === 'Escape') closePalette();
      else if (e.key === 'ArrowDown') {
        e.preventDefault();
        selectedIndex = (selectedIndex + 1) % Math.max(1, visible.length);
        syncSelection();
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        selectedIndex = (selectedIndex - 1 + Math.max(1, visible.length)) % Math.max(1, visible.length);
        syncSelection();
      } else if (e.key === 'Enter' && visible[selectedIndex]) {
        e.preventDefault();
        executeItem(visible[selectedIndex]);
      }
    }
  });

  input.addEventListener('input', () => {
    const q = input.value.trim().toLowerCase();
    items.forEach(item => {
      item.style.display = item.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
    selectedIndex = 0;
    syncSelection();
  });

  items.forEach(item => item.addEventListener('click', () => executeItem(item)));
}

/* ----------------------------------------------------
   10. 3D TILT, SPOTLIGHT & MAGNETIC BUTTONS
   ---------------------------------------------------- */
function initSpotlightAndTilt() {
  document.querySelectorAll('.spotlight-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const r = card.getBoundingClientRect();
      card.style.setProperty('--mouse-x', `${e.clientX - r.left}px`);
      card.style.setProperty('--mouse-y', `${e.clientY - r.top}px`);
    });
  });

  if (window.innerWidth < 992) return;

  document.querySelectorAll('.tilt-card').forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const r = card.getBoundingClientRect();
      const x = e.clientX - r.left;
      const y = e.clientY - r.top;
      const rotX = ((y - r.height / 2) / (r.height / 2)) * -4.5;
      const rotY = ((x - r.width / 2) / (r.width / 2)) * 4.5;
      card.style.transform = `perspective(1100px) rotateX(${rotX.toFixed(2)}deg) rotateY(${rotY.toFixed(2)}deg)`;
    });
    card.addEventListener('mouseleave', () => {
      card.style.transform = 'perspective(1100px) rotateX(0deg) rotateY(0deg)';
    });
  });
}

function initMagneticButtons() {
  if (window.innerWidth < 992) return;
  document.querySelectorAll('.magnetic-btn').forEach(btn => {
    btn.addEventListener('mousemove', (e) => {
      const r = btn.getBoundingClientRect();
      const x = e.clientX - r.left - r.width / 2;
      const y = e.clientY - r.top - r.height / 2;
      btn.style.transform = `translate(${x * 0.15}px, ${y * 0.15}px)`;
    });
    btn.addEventListener('mouseleave', () => {
      btn.style.transform = '';
    });
  });
}

/* ----------------------------------------------------
   11. PROJECTS FILTER, SEARCH & DUAL-MODE 3D DECK
   ---------------------------------------------------- */
function initProjectsFilter() {
  const tabs = document.querySelectorAll('.filter-tab');
  const cards = document.querySelectorAll('.project-card');
  const search = document.getElementById('project-search-input');
  const empty = document.getElementById('projects-empty-filter');
  const railBtn = document.getElementById('view-rail-btn');
  const matrixBtn = document.getElementById('view-matrix-btn');
  const viewport = document.getElementById('projects-slider-viewport');
  const footer = document.getElementById('slider-footer');

  let currentCat = 'all';
  let query = '';

  function filter() {
    let count = 0;
    cards.forEach(c => {
      const cat = (c.getAttribute('data-category') || '').toLowerCase();
      const title = (c.querySelector('.project-title')?.textContent || '').toLowerCase();
      const desc = (c.querySelector('.project-desc')?.textContent || '').toLowerCase();
      const show = (currentCat === 'all' || cat.includes(currentCat.toLowerCase())) &&
                   (query === '' || title.includes(query) || desc.includes(query));
      c.style.display = show ? '' : 'none';
      if (show) count++;
    });
    if (empty) empty.style.display = count === 0 ? 'block' : 'none';
    if (window.reloadProjectSlider) window.reloadProjectSlider();
  }

  tabs.forEach(t => {
    t.addEventListener('click', () => {
      playSynthSfx('click');
      tabs.forEach(x => x.classList.remove('active'));
      t.classList.add('active');
      currentCat = t.getAttribute('data-filter') || 'all';
      filter();
    });
  });

  if (search) {
    search.addEventListener('input', (e) => {
      query = e.target.value.trim().toLowerCase();
      filter();
    });
  }

  if (railBtn && matrixBtn && viewport) {
    railBtn.addEventListener('click', () => {
      playSynthSfx('click');
      railBtn.classList.add('active');
      matrixBtn.classList.remove('active');
      viewport.classList.remove('matrix-mode');
      if (footer) footer.style.display = 'flex';
    });
    matrixBtn.addEventListener('click', () => {
      playSynthSfx('click');
      matrixBtn.classList.add('active');
      railBtn.classList.remove('active');
      viewport.classList.add('matrix-mode');
      if (footer) footer.style.display = 'none';
    });
  }
}

function initProjectSlider() {
  const viewport = document.getElementById('projects-slider-viewport');
  const track = document.getElementById('projects-slider-track');
  const prevBtn = document.getElementById('slider-prev');
  const nextBtn = document.getElementById('slider-next');
  const dotsContainer = document.getElementById('slider-dots');
  const autoplayBadge = document.getElementById('slider-autoplay-indicator');
  if (!viewport || !track) return;

  let isAutoPlay = true;
  let timer = null;
  let resumeTimer = null;
  let isDown = false, startX = 0, scrollLeftVal = 0;

  viewport.addEventListener('mousedown', (e) => {
    if (viewport.classList.contains('matrix-mode') || e.target.closest('a, button, input')) return;
    isDown = true;
    viewport.classList.add('is-dragging');
    startX = e.pageX - viewport.offsetLeft;
    scrollLeftVal = viewport.scrollLeft;
    pauseAuto();
  });

  window.addEventListener('mouseup', () => {
    if (isDown) {
      isDown = false;
      viewport.classList.remove('is-dragging');
      resumeAutoDelayed();
    }
  });

  viewport.addEventListener('mousemove', (e) => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - viewport.offsetLeft;
    viewport.scrollLeft = scrollLeftVal - (x - startX) * 1.35;
  });

  const getVisible = () => Array.from(track.querySelectorAll('.project-card')).filter(c => c.style.display !== 'none');
  const getStep = () => {
    const v = getVisible();
    return v.length ? v[0].offsetWidth + 26 : 400;
  };
  const getIndex = () => Math.round(viewport.scrollLeft / getStep());

  function scrollToIdx(idx, smooth = true) {
    const v = getVisible();
    if (!v.length) return;
    const target = Math.max(0, Math.min(idx, v.length - 1));
    viewport.scrollTo({ left: target * getStep(), behavior: smooth ? 'smooth' : 'auto' });
    if (dotsContainer) {
      dotsContainer.querySelectorAll('.slider-dot').forEach((d, i) => d.classList.toggle('active', i === target));
    }
  }

  function renderDots() {
    if (!dotsContainer) return;
    dotsContainer.innerHTML = '';
    const v = getVisible();
    v.forEach((_, i) => {
      const dot = document.createElement('button');
      dot.className = 'slider-dot' + (i === 0 ? ' active' : '');
      dot.addEventListener('click', () => {
        pauseAuto();
        scrollToIdx(i);
        resumeAutoDelayed();
      });
      dotsContainer.appendChild(dot);
    });
  }

  if (prevBtn) {
    prevBtn.addEventListener('click', () => {
      playSynthSfx('click');
      pauseAuto();
      const v = getVisible();
      const cur = getIndex();
      scrollToIdx(cur <= 0 ? v.length - 1 : cur - 1);
      resumeAutoDelayed();
    });
  }

  if (nextBtn) {
    nextBtn.addEventListener('click', () => {
      playSynthSfx('click');
      pauseAuto();
      const v = getVisible();
      const cur = getIndex();
      scrollToIdx(cur >= v.length - 1 ? 0 : cur + 1);
      resumeAutoDelayed();
    });
  }

  function startAuto() {
    clearInterval(timer);
    timer = setInterval(() => {
      if (!isAutoPlay || viewport.classList.contains('matrix-mode')) return;
      const v = getVisible();
      if (v.length <= 1) return;
      const cur = getIndex();
      scrollToIdx(cur >= v.length - 1 ? 0 : cur + 1);
    }, 3800);
    if (autoplayBadge) autoplayBadge.classList.remove('paused');
  }

  function pauseAuto() {
    isAutoPlay = false;
    clearInterval(timer);
    if (autoplayBadge) autoplayBadge.classList.add('paused');
    clearTimeout(resumeTimer);
  }

  function resumeAutoDelayed() {
    clearTimeout(resumeTimer);
    resumeTimer = setTimeout(() => {
      isAutoPlay = true;
      startAuto();
    }, 3600);
  }

  if (autoplayBadge) {
    autoplayBadge.addEventListener('click', () => {
      if (isAutoPlay) {
        pauseAuto();
        showToast('AUTO_DECK // PAUSED');
      } else {
        isAutoPlay = true;
        startAuto();
        showToast('AUTO_DECK // ACTIVE');
      }
    });
  }

  window.reloadProjectSlider = () => {
    renderDots();
    scrollToIdx(0, false);
  };

  renderDots();
  startAuto();
}

/* ----------------------------------------------------
   12. SKILL BARS, PROJECT MODAL, CLIPBOARD & CLOCK
   ---------------------------------------------------- */
function initSkillBars() {
  const fills = document.querySelectorAll('.skill-bar-fill');
  if (!fills.length) return;
  const obs = new IntersectionObserver((entries, o) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.width = `${e.target.getAttribute('data-level') || '80'}%`;
        o.unobserve(e.target);
      }
    });
  }, { threshold: 0.15 });
  fills.forEach(f => obs.observe(f));
}

function initProjectModal() {
  const backdrop = document.getElementById('project-modal');
  if (!backdrop) return;
  const closeBtn = backdrop.querySelector('.modal-close');

  function closeModal() {
    backdrop.classList.remove('open');
    document.body.style.overflow = '';
  }

  document.querySelectorAll('.btn-project-detail').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      playSynthSfx('click');
      try {
        const data = JSON.parse(btn.getAttribute('data-project') || '{}');
        document.getElementById('modal-project-title').textContent = data.title || '';
        document.getElementById('modal-project-category').textContent = data.category || '';
        document.getElementById('modal-project-desc').textContent = data.description || '';
        const img = document.getElementById('modal-project-img');
        if (img) {
          img.src = data.image || '';
          img.parentElement.style.display = data.image ? 'block' : 'none';
        }
        const demo = document.getElementById('modal-project-demo');
        if (demo) {
          demo.href = data.demo || '#';
          demo.style.display = data.demo ? 'inline-flex' : 'none';
        }
        const repo = document.getElementById('modal-project-repo');
        if (repo) {
          repo.href = data.repo || '#';
          repo.style.display = data.repo ? 'inline-flex' : 'none';
        }
        backdrop.classList.add('open');
        document.body.style.overflow = 'hidden';
      } catch (_) {}
    });
  });

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  backdrop.addEventListener('click', (e) => { if (e.target === backdrop) closeModal(); });
}

function initClipboardToast() {
  document.querySelectorAll('.btn-copy-email').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const email = btn.getAttribute('data-email') || '';
      if (!email) return;
      playSynthSfx('keycap');
      if (navigator.clipboard) {
        navigator.clipboard.writeText(email).then(() => {
          showToast(`CLIPBOARD // "${email}" COPIED`);
        });
      }
    });
  });
}

function showToast(message, duration = 2800) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.textContent = message;
  container.appendChild(toast);
  setTimeout(() => {
    toast.classList.add('toast-out');
    setTimeout(() => toast.remove(), 220);
  }, duration);
}

function initBackToTop() {
  const btn = document.getElementById('back-to-top');
  if (!btn) return;
  window.addEventListener('scroll', () => {
    btn.classList.toggle('visible', window.scrollY > 400);
  }, { passive: true });
  btn.addEventListener('click', () => {
    playSynthSfx('click');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

function initLiveClock() {
  const clockEl = document.getElementById('live-clock');
  if (!clockEl) return;
  function update() {
    try {
      clockEl.textContent = new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Jakarta',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      }).format(new Date()) + ' WIB';
    } catch (_) {}
  }
  update();
  setInterval(update, 1000);
}

function initFormInteractions() {
  const textarea = document.querySelector('.contact-form textarea[name="message"]');
  const countEl = document.getElementById('message-char-count');
  if (textarea && countEl) {
    const sync = () => { countEl.textContent = `${textarea.value.length} chars`; };
    textarea.addEventListener('input', sync);
    sync();
  }
}
