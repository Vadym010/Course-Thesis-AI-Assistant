(() => {
  const wrapper = document.querySelector(".h5p-sc-set-wrapper");
  const root = document.querySelector(".h5p-sc-set");
  if (!wrapper || !root) {
    console.error("❌ Не знайдено .h5p-sc-set-wrapper або .h5p-sc-set");
    return;
  }

  const STYLE_ID = "active-sg-style";
  const WRAP_FLAG = "has-active-sg";
  const NAV_ID = "sg-nav-wrap";

  const slides = () => Array.from(root.querySelectorAll(".h5p-sc-slide"));
  const clamp = (v, min, max) => Math.max(min, Math.min(v, max));

  // ---------- styles (insert near nav) ----------
  function ensureStyleNear(navEl) {
    if (document.getElementById(STYLE_ID)) return;

    const style = document.createElement("style");
    style.id = STYLE_ID;
    style.textContent = `
      /* Override visibility (ONLY when user navigates away from H5P current) */
      .initialized .h5p-sc-slide.active-sg { display: block !important; }
      .initialized .h5p-sc-set-wrapper.${WRAP_FLAG} .h5p-sc-slide.h5p-sc-current-slide { display: none !important; }

      /* Nav */
      .${NAV_ID} { display:flex; gap:8px; align-items:center; margin:0 0 10px 0; flex-wrap:wrap; }
      .${NAV_ID} .sg-btn { padding:6px 10px; cursor:pointer; }

      /* Pages (scrollable) */
      .${NAV_ID} .sg-pages {
        display:flex; gap:6px; flex-wrap:nowrap;
        overflow-x:auto; max-width:100%;
        padding:2px 0;
        scrollbar-width: thin;
      }
      .${NAV_ID} .sg-pages::-webkit-scrollbar{ height: 6px; }

      .${NAV_ID} .sg-page {
        width:28px; height:28px;
        flex: 0 0 auto;
        display:inline-flex; align-items:center; justify-content:center;
        border:1px solid #999; border-radius:6px;
        cursor:pointer; user-select:none;
        font-size:12px; line-height:1;
        background:#e5e7eb; /* default grey */
      }

      /* Markers */
      .${NAV_ID} .sg-page.is-current { outline:2px solid #000; }
      .${NAV_ID} .sg-page.is-override { box-shadow: inset 0 0 0 2px #000; }

      /* Status colors (based on SELECTED answer) */
      .${NAV_ID} .sg-page.sg-status-gray { background:#e5e7eb; }
      .${NAV_ID} .sg-page.sg-status-green { background:#86efac; } /* correct selected */
      .${NAV_ID} .sg-page.sg-status-red { background:#fca5a5; }   /* wrong selected */
    `;
    navEl.insertAdjacentElement("afterend", style);
  }

  // ---------- indexes ----------
  function getCurrentIndex() {
    const arr = slides();
    const i = arr.findIndex(s => s.classList.contains("h5p-sc-current-slide"));
    return i >= 0 ? i : 0;
  }
  function getOverrideIndex() {
    const arr = slides();
    const i = arr.findIndex(s => s.classList.contains("active-sg"));
    return i >= 0 ? i : null;
  }
  function getVisibleIndex() {
    const ov = getOverrideIndex();
    return ov !== null ? ov : getCurrentIndex();
  }

  // ---------- build nav ----------
  const existing = wrapper.querySelector(`[data-h5p-nav="${NAV_ID}"]`);
  if (existing) {
    console.warn("⚠️ Навігація вже додана");
    return;
  }

  const nav = document.createElement("div");
  nav.className = NAV_ID;
  nav.dataset.h5pNav = NAV_ID;

  const mkBtn = (txt, fn) => {
    const b = document.createElement("button");
    b.type = "button";
    b.className = "sg-btn";
    b.textContent = txt;
    b.addEventListener("click", fn);
    return b;
  };

  const pagesWrap = document.createElement("div");
  pagesWrap.className = "sg-pages";

  nav.append(
    mkBtn("⬅️ Назад", () => applyView(getVisibleIndex() - 1)),
    mkBtn("Вперед ➡️", () => applyView(getVisibleIndex() + 1)),
    pagesWrap
  );

  wrapper.prepend(nav);
  ensureStyleNear(nav);

  // ---------- pager ----------
  function buildPager() {
    const n = slides().length;
    pagesWrap.innerHTML = "";
    for (let i = 0; i < n; i++) {
      const el = document.createElement("div");
      el.className = "sg-page sg-status-gray";
      el.textContent = String(i + 1);
      el.title = `Слайд ${i + 1}`;
      el.addEventListener("click", () => applyView(i));
      pagesWrap.appendChild(el);
    }
  }

  function scrollActivePageIntoView() {
    const target =
      pagesWrap.querySelector(".sg-page.is-override") ||
      pagesWrap.querySelector(".sg-page.is-current");
    if (!target) return;

    target.scrollIntoView({
      behavior: "smooth",
      block: "nearest",
      inline: "center",
    });
  }

  // ---- status detection (BASED ON SELECTED answer) ----
  function slideStatus(slideEl) {
    const selected = slideEl.querySelector(".h5p-sc-alternative.h5p-sc-selected");
    if (!selected) return "gray";

    if (selected.classList.contains("h5p-sc-is-correct")) return "green";
    if (selected.classList.contains("h5p-sc-is-wrong")) return "red";

    // selected exists, but no status class
    return "gray";
  }

  function updateStatuses() {
    const arr = slides();
    const nodes = Array.from(pagesWrap.querySelectorAll(".sg-page"));

    arr.forEach((slideEl, i) => {
      const node = nodes[i];
      if (!node) return;

      const st = slideStatus(slideEl);
      node.classList.remove("sg-status-gray", "sg-status-green", "sg-status-red");
      node.classList.add(`sg-status-${st}`);
    });
  }

  function updatePager() {
    const current = getCurrentIndex();
    const visible = getVisibleIndex();
    const hasOverride = wrapper.classList.contains(WRAP_FLAG);
    const nodes = Array.from(pagesWrap.querySelectorAll(".sg-page"));

    nodes.forEach((node, i) => {
      node.classList.toggle("is-current", i === current);
      node.classList.toggle("is-override", hasOverride && i === visible);
    });

    updateStatuses();
    scrollActivePageIntoView();
  }

  // ---------- view apply (translate by 100%) ----------
  function applyView(index) {
    const arr = slides();
    const max = Math.max(0, arr.length - 1);
    const current = getCurrentIndex();
    const target = clamp(index, 0, max);

    // clear override
    arr.forEach(s => s.classList.remove("active-sg"));
    wrapper.classList.remove(WRAP_FLAG);

    // set override only if differs from current
    if (target !== current) {
      arr[target].classList.add("active-sg");
      wrapper.classList.add(WRAP_FLAG);
    }

    root.style.transition = "transform 0.35s ease";
    root.style.transform = `translateX(${-target * 100}%)`;

    updatePager();
  }

  // ---------- watch: slide count changes ----------
  let lastCount = 0;
  const tickCount = () => {
    const c = slides().length;
    if (c !== lastCount) {
      lastCount = c;
      buildPager();
      updatePager();
    }
  };
  tickCount();

  const moCount = new MutationObserver(tickCount);
  moCount.observe(root, { childList: true, subtree: true });

  // ---------- watch: current slide changes (class mutation) ----------
  let lastCurrent = getCurrentIndex();
  console.log("👀 current-slide watcher старт. current =", lastCurrent);

  const moCurrent = new MutationObserver(() => {
    const now = getCurrentIndex();
    if (now !== lastCurrent) {
      console.log(`✅ h5p-sc-current-slide змінився: ${lastCurrent} -> ${now}`);
      lastCurrent = now;
      applyView(getVisibleIndex() + 1);

      // if no override, follow H5P current
      if (!wrapper.classList.contains(WRAP_FLAG)) {
        root.style.transition = "transform 0.35s ease";
        root.style.transform = `translateX(${-now * 100}%)`;
      }

      updatePager();
    }
  });
  moCurrent.observe(root, { subtree: true, attributes: true, attributeFilter: ["class"] });

  // ---------- watch: selection changes (status updates) ----------
  // selection/answer state changes via class changes on alternatives
  const moSelected = new MutationObserver(() => {
    updateStatuses();
  });
  moSelected.observe(root, { subtree: true, attributes: true, attributeFilter: ["class"] });

  // ---------- init ----------
  applyView(getCurrentIndex());
  console.log("✅ Додано: кнопки + квадратики + автоскрол + current watcher + статуси по selected (green/red/gray)");
})();




