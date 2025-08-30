/* ======= Mobile nav only ======= */
const BREAKPOINT = 991; // must match CSS
const menu = document.querySelector("#menu-icon");
const navbar = document.querySelector(".navbar");

if (menu && navbar) {
  const toggleMobileNav = () => {
    if (window.innerWidth <= BREAKPOINT) {
      menu.classList.toggle("bx-x");
      navbar.classList.toggle("active");
    }
  };
  const closeMobileNav = () => {
    menu.classList.remove("bx-x");
    navbar.classList.remove("active");
  };

  menu.addEventListener("click", toggleMobileNav);
  window.addEventListener("scroll", closeMobileNav);
  window.addEventListener(
    "resize",
    () => {
      if (window.innerWidth > BREAKPOINT) closeMobileNav(); // ensure hidden on desktop after resize
    },
    { passive: true }
  );
}

/* ============== Reveal-on-scroll ============== */
let revealObserver = null;

function ensureRevealObserver() {
  if (!("IntersectionObserver" in window)) {
    document
      .querySelectorAll(".reveal")
      .forEach((el) => el.classList.add("visible"));
    return null;
  }
  if (!revealObserver) {
    revealObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((e) => {
          if (e.isIntersecting) {
            e.target.classList.add("visible");
            revealObserver.unobserve(e.target);
          }
        });
      },
      { threshold: 0.18 }
    );
  }
  return revealObserver;
}

function watchReveals(scope = document) {
  const obs = ensureRevealObserver();
  const items = scope.querySelectorAll(".reveal:not(.visible)");
  if (!obs) {
    items.forEach((el) => el.classList.add("visible"));
    return;
  }
  items.forEach((el) => obs.observe(el));
}

// Watch anything already in DOM
watchReveals(document);

/* ============== Education (PHP-rendered HTML) ============== */
async function loadEducationHTML() {
  const mount = document.getElementById("edu-list");
  if (!mount) return;
  try {
    const res = await fetch(
      "/portfolio/admin/education_render.php?ts=" + Date.now(),
      { cache: "no-store" }
    );
    if (!res.ok) throw new Error(`HTTP ${res.status} ${res.statusText}`);
    const html = await res.text();
    mount.innerHTML =
      html && html.trim().length
        ? html
        : `<div class="empty">No education added yet.</div>`;
    watchReveals(mount);
  } catch (err) {
    console.error("Education load error:", err);
    mount.innerHTML = `<div class="empty">Could not load education. ${err.message}</div>`;
  }
}

/* ============== Projects (PHP-rendered HTML + pager) ============== */
function setupProjectPager() {
  const grid = document.getElementById("project-list");
  if (!grid) return;

  const cards = Array.from(grid.querySelectorAll(".project-card"));
  const controls = grid.parentElement.querySelector(".project-controls");
  if (!controls) return;

  // If no cards, hide controls and exit
  if (!cards.length) {
    controls.style.display = "none";
    return;
  }

  const prevBtn = controls.querySelector(".prev");
  const nextBtn = controls.querySelector(".next");

  const pageSizeForWidth = () => {
    const w = window.innerWidth || document.documentElement.clientWidth;
    if (w >= 1100) return 3;
    if (w >= 700) return 2;
    return 1;
  };

  let page = 0;

  function render() {
    const pageSize = pageSizeForWidth();
    const totalPages = Math.max(1, Math.ceil(cards.length / pageSize));
    if (page > totalPages - 1) page = totalPages - 1;

    const start = page * pageSize;
    const end = start + pageSize;

    cards.forEach((el, i) => {
      const show = i >= start && i < end;
      el.style.display = show ? "" : "none";
      // NEW: pause videos on hidden cards
      if (!show)
        el.querySelectorAll("video").forEach((v) => {
          try {
            v.pause();
          } catch (_) {}
        });
    });

    prevBtn.disabled = page === 0;
    nextBtn.disabled = page >= totalPages - 1;
    controls.style.display = cards.length > pageSize ? "flex" : "none";
  }

  prevBtn.onclick = () => {
    if (!prevBtn.disabled) {
      page--;
      render();
    }
  };
  nextBtn.onclick = () => {
    if (!nextBtn.disabled) {
      page++;
      render();
    }
  };
  window.addEventListener("resize", render, { passive: true });

  render();
}

async function loadProjectsHTML() {
  const mount = document.getElementById("project-list");
  if (!mount) return;

  try {
    const res = await fetch(
      "/portfolio/admin/project_render.php?ts=" + Date.now(),
      { cache: "no-store" }
    );
    if (!res.ok) throw new Error(`HTTP ${res.status} ${res.statusText}`);
    const html = await res.text();

    mount.innerHTML =
      html && html.trim().length
        ? html
        : `<div class="empty">No projects added yet.</div>`;

    // reveal effect for injected cards
    if (typeof watchReveals === "function") watchReveals(mount);
    else
      mount
        .querySelectorAll(".reveal")
        .forEach((el) => el.classList.add("visible"));

    // now enable paging
    setupProjectPager();
  } catch (err) {
    console.error("Projects load error:", err);
    mount.innerHTML = `<div class="empty">Could not load projects. ${err.message}</div>`;
    // hide controls if request failed
    const controls = mount.parentElement.querySelector(".project-controls");
    if (controls) controls.style.display = "none";
  }
}

/* ============== Boot ============== */
document.addEventListener("DOMContentLoaded", () => {
  loadEducationHTML();
  loadProjectsHTML();
});
// Skills slider arrows functionality (mobile swipe and arrows)
const slider = document.querySelector(".skills-slider");
const leftBtn = document.querySelector(".left-btn");
const rightBtn = document.querySelector(".right-btn");

// Left button scrolls to the left
leftBtn.addEventListener("click", () => {
  slider.scrollBy({ left: -slider.offsetWidth, behavior: "smooth" });
});

// Right button scrolls to the right
rightBtn.addEventListener("click", () => {
  slider.scrollBy({ left: slider.offsetWidth, behavior: "smooth" });
});

// Ensure that when user reaches the start or end of the slider, the buttons are disabled
slider.addEventListener("scroll", function () {
  if (slider.scrollLeft === 0) {
    leftBtn.disabled = true;
  } else {
    leftBtn.disabled = false;
  }

  if (slider.scrollWidth === slider.scrollLeft + slider.clientWidth) {
    rightBtn.disabled = true;
  } else {
    rightBtn.disabled = false;
  }
});

// Services slider arrows functionality (mobile swipe and arrows)
const slider2 = document.querySelector(".services-grid");
const leftBtn2 = document.querySelector(".left-btn");
const rightBtn2 = document.querySelector(".right-btn");

leftBtn.addEventListener("click", () => {
  slider.scrollBy({ left: -slider.offsetWidth, behavior: "smooth" });
});
rightBtn.addEventListener("click", () => {
  slider.scrollBy({ left: slider.offsetWidth, behavior: "smooth" });
});
