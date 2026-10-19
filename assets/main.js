/**
 * PORTOFOLIO INTERACTIVE SCRIPTS
 * Sleek, lightweight, dependency-free JavaScript
 */

document.addEventListener('DOMContentLoaded', () => {
  initTheme();
  initScrollProgress();
  initStickyHeader();
  initMobileMenu();
  initTypewriter();
  initProjectsFilter();
  initSkillBars();
  initProjectModal();
  initClipboardToast();
  initBackToTop();
  initLiveClock();
  initCardTilt();
  initFormInteractions();
});

/* ----------------------------------------------------
   1. THEME SWITCHER (Dark / Light)
   ---------------------------------------------------- */
function initTheme() {
  const themeToggle = document.getElementById('theme-toggle');
  const storedTheme = localStorage.getItem('portfolio_theme');
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  
  // Set default theme: stored theme, or default to dark (developer preference)
  const currentTheme = storedTheme ? storedTheme : 'dark';
  applyTheme(currentTheme);

  if (themeToggle) {
    themeToggle.addEventListener('click', () => {
      const activeTheme = document.documentElement.getAttribute('data-theme') || 'dark';
      const newTheme = activeTheme === 'dark' ? 'light' : 'dark';
      applyTheme(newTheme);
      localStorage.setItem('portfolio_theme', newTheme);
      showToast(`Tema beralih ke mode ${newTheme === 'dark' ? 'Gelap 🌙' : 'Terang ☀️'}`);
    });
  }
}

function applyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  const themeToggle = document.getElementById('theme-toggle');
  if (themeToggle) {
    themeToggle.setAttribute('aria-label', `Ganti ke mode ${theme === 'dark' ? 'terang' : 'gelap'}`);
    const sunIcon = themeToggle.querySelector('.icon-sun');
    const moonIcon = themeToggle.querySelector('.icon-moon');
    if (sunIcon && moonIcon) {
      if (theme === 'dark') {
        sunIcon.style.display = 'block';
        moonIcon.style.display = 'none';
      } else {
        sunIcon.style.display = 'none';
        moonIcon.style.display = 'block';
      }
    }
  }
}

/* ----------------------------------------------------
   2. SCROLL PROGRESS BAR
   ---------------------------------------------------- */
function initScrollProgress() {
  const bar = document.getElementById('scroll-progress');
  if (!bar) return;

  window.addEventListener('scroll', () => {
    const totalHeight = document.documentElement.scrollHeight - window.innerHeight;
    const progress = totalHeight > 0 ? (window.scrollY / totalHeight) * 100 : 0;
    bar.style.width = `${progress}%`;
  }, { passive: true });
}

/* ----------------------------------------------------
   3. STICKY HEADER & SCROLL SPY
   ---------------------------------------------------- */
function initStickyHeader() {
  const header = document.querySelector('.site-header');
  const navLinks = document.querySelectorAll('.nav-links .nav-link, .mobile-links .mobile-link');
  const sections = document.querySelectorAll('section[id]');

  window.addEventListener('scroll', () => {
    if (header) {
      if (window.scrollY > 30) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    }

    // Scroll spy
    let currentId = '';
    const scrollPos = window.scrollY + 120;

    sections.forEach(sec => {
      const top = sec.offsetTop;
      const height = sec.offsetHeight;
      if (scrollPos >= top && scrollPos < top + height) {
        currentId = sec.getAttribute('id');
      }
    });

    navLinks.forEach(link => {
      const href = link.getAttribute('href');
      if (href === `#${currentId}`) {
        link.classList.add('active');
      } else {
        link.classList.remove('active');
      }
    });
  }, { passive: true });
}

/* ----------------------------------------------------
   4. MOBILE NAVIGATION DRAWER
   ---------------------------------------------------- */
function initMobileMenu() {
  const toggle = document.querySelector('.nav-toggle');
  const drawer = document.querySelector('.mobile-nav-drawer');
  const backdrop = document.querySelector('.mobile-nav-backdrop');
  const links = document.querySelectorAll('.mobile-link');

  if (!toggle || !drawer || !backdrop) return;

  function openMenu() {
    toggle.classList.add('open');
    drawer.classList.add('open');
    backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeMenu() {
    toggle.classList.remove('open');
    drawer.classList.remove('open');
    backdrop.classList.remove('open');
    document.body.style.overflow = '';
  }

  toggle.addEventListener('click', () => {
    if (drawer.classList.contains('open')) {
      closeMenu();
    } else {
      openMenu();
    }
  });

  backdrop.addEventListener('click', closeMenu);

  links.forEach(l => {
    l.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && drawer.classList.contains('open')) {
      closeMenu();
    }
  });
}

/* ----------------------------------------------------
   5. TYPEWRITER / TEXT ROTATION
   ---------------------------------------------------- */
function initTypewriter() {
  const el = document.getElementById('typewriter-text');
  if (!el) return;

  const rawWords = el.getAttribute('data-words');
  let words = ['Web Developer', 'Teknik Informatika', 'Full-Stack Explorer', 'Creative Problem Solver'];
  if (rawWords) {
    try {
      words = JSON.parse(rawWords);
    } catch (e) {
      // fallback
    }
  }

  let wordIndex = 0;
  let charIndex = 0;
  let isDeleting = false;
  let typingSpeed = 100;

  function type() {
    const currentWord = words[wordIndex];
    if (isDeleting) {
      el.textContent = currentWord.substring(0, charIndex - 1);
      charIndex--;
      typingSpeed = 50;
    } else {
      el.textContent = currentWord.substring(0, charIndex + 1);
      charIndex++;
      typingSpeed = 110;
    }

    if (!isDeleting && charIndex === currentWord.length) {
      typingSpeed = 2000; // Pause at end of word
      isDeleting = true;
    } else if (isDeleting && charIndex === 0) {
      isDeleting = false;
      wordIndex = (wordIndex + 1) % words.length;
      typingSpeed = 400; // Pause before new word
    }

    setTimeout(type, typingSpeed);
  }

  setTimeout(type, 500);
}

/* ----------------------------------------------------
   6. PROJECTS FILTER & SEARCH
   ---------------------------------------------------- */
function initProjectsFilter() {
  const filterTabs = document.querySelectorAll('.filter-tab');
  const projectCards = document.querySelectorAll('.project-card');
  const searchInput = document.getElementById('project-search-input');
  const emptyNotice = document.getElementById('projects-empty-filter');

  let currentCategory = 'all';
  let searchQuery = '';

  function applyFilters() {
    let visibleCount = 0;

    projectCards.forEach(card => {
      const cardCategory = (card.getAttribute('data-category') || '').toLowerCase();
      const cardTitle = (card.querySelector('.project-title')?.textContent || '').toLowerCase();
      const cardDesc = (card.querySelector('.project-desc')?.textContent || '').toLowerCase();

      const matchesCategory = currentCategory === 'all' || cardCategory.includes(currentCategory.toLowerCase());
      const matchesSearch = searchQuery === '' || cardTitle.includes(searchQuery) || cardDesc.includes(searchQuery);

      if (matchesCategory && matchesSearch) {
        card.style.display = '';
        visibleCount++;
      } else {
        card.style.display = 'none';
      }
    });

    if (emptyNotice) {
      emptyNotice.style.display = visibleCount === 0 ? 'block' : 'none';
    }
  }

  filterTabs.forEach(tab => {
    tab.addEventListener('click', () => {
      filterTabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      currentCategory = tab.getAttribute('data-filter') || 'all';
      applyFilters();
    });
  });

  if (searchInput) {
    searchInput.addEventListener('input', (e) => {
      searchQuery = e.target.value.trim().toLowerCase();
      applyFilters();
    });
  }
}

/* ----------------------------------------------------
   7. SKILL PROGRESS BARS (IntersectionObserver)
   ---------------------------------------------------- */
function initSkillBars() {
  const fills = document.querySelectorAll('.skill-bar-fill');
  if (!fills.length) return;

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const bar = entry.target;
        const targetLevel = bar.getAttribute('data-level') || '75';
        bar.style.width = `${targetLevel}%`;
        obs.unobserve(bar);
      }
    });
  }, { threshold: 0.2 });

  fills.forEach(f => observer.observe(f));
}

/* ----------------------------------------------------
   8. PROJECT DETAIL MODAL
   ---------------------------------------------------- */
function initProjectModal() {
  const backdrop = document.getElementById('project-modal');
  if (!backdrop) return;

  const closeBtn = backdrop.querySelector('.modal-close');
  const detailBtns = document.querySelectorAll('.btn-project-detail');

  const modalTitle = document.getElementById('modal-project-title');
  const modalCategory = document.getElementById('modal-project-category');
  const modalDesc = document.getElementById('modal-project-desc');
  const modalImg = document.getElementById('modal-project-img');
  const modalDemo = document.getElementById('modal-project-demo');
  const modalRepo = document.getElementById('modal-project-repo');

  function openModal(data) {
    if (modalTitle) modalTitle.textContent = data.title;
    if (modalCategory) modalCategory.textContent = data.category;
    if (modalDesc) modalDesc.textContent = data.description;

    if (modalImg) {
      if (data.image) {
        modalImg.src = data.image;
        modalImg.parentElement.style.display = 'block';
      } else {
        modalImg.parentElement.style.display = 'none';
      }
    }

    if (modalDemo) {
      if (data.demo) {
        modalDemo.href = data.demo;
        modalDemo.style.display = 'inline-flex';
      } else {
        modalDemo.style.display = 'none';
      }
    }

    if (modalRepo) {
      if (data.repo) {
        modalRepo.href = data.repo;
        modalRepo.style.display = 'inline-flex';
      } else {
        modalRepo.style.display = 'none';
      }
    }

    backdrop.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    backdrop.classList.remove('open');
    document.body.style.overflow = '';
  }

  detailBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      try {
        const data = JSON.parse(btn.getAttribute('data-project') || '{}');
        openModal(data);
      } catch (err) {
        console.error('Error opening project modal:', err);
      }
    });
  });

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  backdrop.addEventListener('click', (e) => {
    if (e.target === backdrop) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && backdrop.classList.contains('open')) {
      closeModal();
    }
  });
}

/* ----------------------------------------------------
   9. EMAIL COPY & TOAST NOTIFICATION
   ---------------------------------------------------- */
function initClipboardToast() {
  const copyBtns = document.querySelectorAll('.btn-copy-email');

  copyBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const email = btn.getAttribute('data-email') || '';
      if (!email) return;

      navigator.clipboard.writeText(email).then(() => {
        showToast(`✓ Email "${email}" berhasil disalin!`);
      }).catch(() => {
        // Fallback for older browsers
        const textarea = document.createElement('textarea');
        textarea.value = email;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        showToast(`✓ Email "${email}" berhasil disalin!`);
      });
    });
  });
}

function showToast(message, duration = 3000) {
  let container = document.getElementById('toast-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = message;
  container.appendChild(toast);

  setTimeout(() => {
    toast.classList.add('toast-out');
    setTimeout(() => {
      if (toast.parentElement) toast.parentElement.removeChild(toast);
    }, 300);
  }, duration);
}

/* ----------------------------------------------------
   10. BACK TO TOP BUTTON
   ---------------------------------------------------- */
function initBackToTop() {
  const btn = document.getElementById('back-to-top');
  if (!btn) return;

  window.addEventListener('scroll', () => {
    if (window.scrollY > 400) {
      btn.classList.add('visible');
    } else {
      btn.classList.remove('visible');
    }
  }, { passive: true });

  btn.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });
}

/* ----------------------------------------------------
   11. LIVE LOCAL CLOCK (WIB / Indonesia)
   ---------------------------------------------------- */
function initLiveClock() {
  const clockEl = document.getElementById('live-clock');
  if (!clockEl) return;

  function updateClock() {
    try {
      const now = new Date();
      // Format to WIB (UTC+7)
      const options = {
        timeZone: 'Asia/Jakarta',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      };
      const timeStr = new Intl.DateTimeFormat('id-ID', options).format(now);
      clockEl.textContent = `${timeStr} WIB`;
    } catch (e) {
      const now = new Date();
      clockEl.textContent = now.toTimeString().substring(0, 8);
    }
  }

  updateClock();
  setInterval(updateClock, 1000);
}

/* ----------------------------------------------------
   12. 3D CARD TILT EFFECT (Subtle & Smooth)
   ---------------------------------------------------- */
function initCardTilt() {
  const cards = document.querySelectorAll('.tilt-card');
  if (window.innerWidth < 992) return; // Disable on touch/small devices

  cards.forEach(card => {
    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left;
      const y = e.clientY - rect.top;
      const centerX = rect.width / 2;
      const centerY = rect.height / 2;

      const rotateX = ((y - centerY) / centerY) * -6; // Max 6 deg
      const rotateY = ((x - centerX) / centerX) * 6;

      card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.02, 1.02, 1.02)`;
    });

    card.addEventListener('mouseleave', () => {
      card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
      card.style.transition = 'transform 0.5s ease';
    });

    card.addEventListener('mouseenter', () => {
      card.style.transition = 'transform 0.1s ease';
    });
  });
}

/* ----------------------------------------------------
   13. FORM VALIDATION & FEEDBACK
   ---------------------------------------------------- */
function initFormInteractions() {
  const textarea = document.querySelector('.contact-form textarea[name="message"]');
  const countEl = document.getElementById('message-char-count');
  const form = document.querySelector('.contact-form');
  const submitBtn = form?.querySelector('button[type="submit"]');

  if (textarea && countEl) {
    textarea.addEventListener('input', () => {
      countEl.textContent = `${textarea.value.length} karakter`;
    });
  }

  if (form && submitBtn) {
    form.addEventListener('submit', () => {
      submitBtn.innerHTML = `
        <svg class="spinner" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite; margin-right:8px;">
          <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
          <path d="M12 2a10 10 0 0 1 10 10" stroke-linecap="round"></path>
        </svg>
        Mengirim pesan...
      `;
      submitBtn.disabled = true;
      submitBtn.style.opacity = '0.85';
    });
  }
}

