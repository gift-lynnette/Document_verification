/* ========================================
   ABGTECH - JAVASCRIPT FUNCTIONALITY
   ======================================== */

/* ========================================
   1. UTILITY FUNCTIONS
   ======================================== */

/**
 * Query selector shorthand
 * @param {string} selector - CSS selector
 * @param {HTMLElement} scope - Optional scope to limit search
 * @returns {HTMLElement|null} - First matching element
 */
const qs = (selector, scope = document) => scope.querySelector(selector);

/**
 * Query selector all shorthand - returns array
 * @param {string} selector - CSS selector
 * @param {HTMLElement} scope - Optional scope to limit search
 * @returns {Array} - Array of matching elements
 */
const qsa = (selector, scope = document) => [...scope.querySelectorAll(selector)];

/* ========================================
   2. COUNTER ANIMATION
   ======================================== */

/**
 * Animate numbers from 0 to target value
 * Used for statistics and metrics
 * @param {HTMLElement} el - Element with data-counter attribute
 */
function animateCounter(el) {
  if (el.dataset.done) return;
  el.dataset.done = "true";
  const target = Number(el.dataset.counter || 0);
  const suffix = el.dataset.suffix || "";
  const duration = 1500;
  const start = performance.now();
  const tick = now => {
    const progress = Math.min((now - start) / duration, 1);
    const eased = 1 - Math.pow(1 - progress, 3);
    const value = target % 1 ? (target * eased).toFixed(1) : Math.floor(target * eased);
    el.textContent = value + suffix;
    if (progress < 1) requestAnimationFrame(tick);
  };
  requestAnimationFrame(tick);
}

/* ========================================
   3. SCROLL REVEAL & ANIMATIONS
   ======================================== */

/**
 * Initialize Intersection Observer for scroll reveal animations
 * Triggers animations when elements come into view
 * Includes counter animations and bar chart animations
 */
function initReveal() {
  const observer = new IntersectionObserver(entries => {
    entries.forEach(entry => {
      if (!entry.isIntersecting) return;
      entry.target.classList.add("visible");
      // Animate counters when element comes into view
      qsa("[data-counter]", entry.target).forEach(animateCounter);
      // Animate bar widths for horizontal bars
      qsa("[data-width]", entry.target).forEach(bar => bar.style.width = bar.dataset.width);
      // Animate bar heights for vertical bars
      qsa("[data-height]", entry.target).forEach(bar => bar.style.height = bar.dataset.height);
      observer.unobserve(entry.target);
    });
  }, { threshold: .15 });
  // Observe all reveal animations
  qsa(".reveal, .hero-mock, .mockup-card, .stats-inner").forEach(el => observer.observe(el));
}

/* ========================================
   4. NAVIGATION & MOBILE DRAWER
   ======================================== */

/**
 * Initialize navigation functionality
 * Handles:
 * - Navbar scroll effects
 * - Mobile drawer open/close
 * - Drawer backdrop click to close
 * - Menu link clicks to close drawer
 */
function initNavigation() {
  /* --- Navbar Scroll Effect --- */
  const nav = qs("#siteNav");
  if (nav) {
    const onScroll = () => nav.classList.toggle("is-scrolled", window.scrollY > 60);
    window.addEventListener("scroll", onScroll, { passive: true });
    onScroll();
  }

  /* --- Mobile Drawer Controls --- */
  const open = qs("#openDrawer");      // Hamburger menu button
  const close = qs("#closeDrawer");    // Close button in drawer
  const backdrop = qs("#drawerBackdrop"); // Semi-transparent overlay

  // Open drawer on hamburger click
  if (open) open.addEventListener("click", () => document.body.classList.add("drawer-open"));

  // Close drawer on close button click
  if (close) close.addEventListener("click", () => document.body.classList.remove("drawer-open"));

  // Close drawer on backdrop click (outside menu)
  if (backdrop) backdrop.addEventListener("click", () => document.body.classList.remove("drawer-open"));

  // Close drawer when clicking any menu link
  qsa(".drawer-link").forEach(link => link.addEventListener("click", () => document.body.classList.remove("drawer-open")));
}

function initTabs() {
  qsa("[data-tabs]").forEach(shell => {
    const buttons = qsa(".tab-btn", shell);
    const panels = qsa(".tab-panel", shell);
    const indicator = qs(".tab-indicator", shell);
    const activate = button => {
      buttons.forEach(btn => btn.classList.toggle("active", btn === button));
      panels.forEach(panel => panel.classList.toggle("active", panel.dataset.panel === button.dataset.tab));
      if (indicator) {
        indicator.style.left = `${button.offsetLeft}px`;
        indicator.style.width = `${button.offsetWidth}px`;
      }
    };
    buttons.forEach(button => button.addEventListener("click", () => activate(button)));
    if (buttons[0]) {
      activate(buttons.find(button => button.classList.contains("active")) || buttons[0]);
      window.addEventListener("resize", () => {
        const active = buttons.find(button => button.classList.contains("active"));
        if (active) activate(active);
      }, { passive: true });
    }
  });
}

function initLeadForms() {
  qsa("[data-lead-form]").forEach(form => {
    form.addEventListener("submit", event => {
      event.preventDefault();
      const message = qs("[data-form-message]", form);
      if (message) message.classList.remove("d-none");
      form.reset();
    });
  });
}

function initJourney() {
  const journey = qs("[data-journey]");
  if (!journey) return;
  const detail = qs("[data-journey-detail]");
  qsa("[data-step]", journey).forEach(step => {
    step.addEventListener("click", () => {
      qsa("[data-step]", journey).forEach(item => item.classList.toggle("active", item === step));
      if (detail) detail.innerHTML = `<strong>${step.dataset.title}</strong><p class="section-copy mb-0">${step.dataset.detail}</p>`;
    });
  });
}

function initMethodStepper() {
  qsa("[data-method]").forEach(shell => {
    const steps = qsa(".method-step", shell);
    const detail = qs(".method-detail", shell);
    steps.forEach(step => {
      step.addEventListener("click", () => {
        steps.forEach(item => item.classList.toggle("active", item === step));
        if (detail) {
          detail.innerHTML = `<div class="premium-card p-4 mt-4"><h3 class="h4 fw-bold">${step.dataset.title}</h3><p class="section-copy mb-0">${step.dataset.detail}</p></div>`;
          detail.classList.add("open");
        }
      });
    });
    if (steps[0]) steps[0].click();
  });
}

document.addEventListener("DOMContentLoaded", () => {
  initNavigation();
  initReveal();
  initTabs();
  initLeadForms();
  initJourney();
  initMethodStepper();
  const year = qs("#year");
  if (year) year.textContent = new Date().getFullYear();
});
