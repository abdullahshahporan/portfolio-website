/* ============== Mobile nav ============== */
const menu = document.querySelector('#menu-icon');
const navbar = document.querySelector('.navbar');

if (menu && navbar) {
  menu.addEventListener('click', () => {
    menu.classList.toggle('bx-x');
    navbar.classList.toggle('active');
  });
  const closeMenu = () => {
    menu.classList.remove('bx-x');
    navbar.classList.remove('active');
  };
  window.addEventListener('scroll', closeMenu);
  navbar.addEventListener('click', e => { if (e.target.matches('a')) closeMenu(); });
}

/* ============== Reveal-on-scroll (works for injected elements too) ============== */
let revealObserver = null;

function ensureRevealObserver() {
  if (!('IntersectionObserver' in window)) {
    // Fallback: just show everything
    document.querySelectorAll('.reveal').forEach(el => el.classList.add('visible'));
    return null;
  }
  if (!revealObserver) {
    revealObserver = new IntersectionObserver(entries => {
      entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible');
          revealObserver.unobserve(e.target);
        }
      });
    }, { threshold: 0.18 });
  }
  return revealObserver;
}

function watchReveals(scope = document) {
  const obs = ensureRevealObserver();
  const items = scope.querySelectorAll('.reveal:not(.visible)');
  if (!obs) {
    // Fallback already handled
    items.forEach(el => el.classList.add('visible'));
    return;
  }
  items.forEach(el => obs.observe(el));
}

// Watch any reveals that are already in the DOM
watchReveals(document);

/* ============== Education (PHP-rendered HTML) ============== */
async function loadEducationHTML() {
  const mount = document.getElementById('edu-list');
  if (!mount) return;

  try {
    // Absolute path to avoid relative-path issues
    const res = await fetch('/portfolio/admin/education_render.php?ts=' + Date.now(), { cache: 'no-store' });
    if (!res.ok) throw new Error(`HTTP ${res.status} ${res.statusText}`);
    const html = await res.text();

    mount.innerHTML = html && html.trim().length
      ? html
      : `<div class="empty">No education added yet.</div>`;

    // IMPORTANT: start observing the newly injected .reveal cards
    watchReveals(mount);
  } catch (err) {
    console.error('Education load error:', err);
    mount.innerHTML = `<div class="empty">Could not load education. ${err.message}</div>`;
  }
}

document.addEventListener('DOMContentLoaded', loadEducationHTML);

