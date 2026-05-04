/**
 * Catálogo público: especialidades, médicos y servicios desde Laravel API.
 * Los datos se actualizan al recargar la página tras cambios en /admin.
 */
(function () {
  /**
   * Base del Laravel `public` (sin /api al final). Orden:
   * 1) window.__API_BASE__
   * 2) <meta name="nova-api-base" content="..."> (URL absoluta o ruta que empiece por /)
   * 3) Ruta si la URL contiene /frontend/…
   * 4) file:// u orígenes raros → http://localhost + ruta del meta o valor por defecto XAMPP
   */
  function getApiBase() {
    if (window.__API_BASE__) {
      return String(window.__API_BASE__).replace(/\/$/, "");
    }

    const meta = document.querySelector('meta[name="nova-api-base"]');
    const metaRaw = meta && meta.content ? meta.content.trim() : "";

    const { protocol, host, pathname } = window.location;
    const isFile = protocol === "file:";
    const hostOk = host && protocol !== "file:";

    if (metaRaw) {
      if (/^https?:\/\//i.test(metaRaw)) {
        return metaRaw.replace(/\/$/, "");
      }
      if (metaRaw.startsWith("/")) {
        if (hostOk) {
          return `${protocol}//${host}${metaRaw.replace(/\/$/, "")}`;
        }
        return `http://localhost${metaRaw.replace(/\/$/, "")}`;
      }
    }

    if (!isFile && hostOk && pathname.includes("/frontend")) {
      const i = pathname.indexOf("/frontend");
      const basePath = (i > 0 ? pathname.slice(0, i) : "").replace(/\/$/, "");
      const root = `${window.location.origin}${basePath}`;
      return `${root}/backend/api/public`.replace(/([^:])\/{2,}/g, "$1/");
    }

    if (!isFile && hostOk) {
      const dir = pathname.replace(/\/[^/]+$/, "").replace(/\/$/, "");
      return `${window.location.origin}${dir}/backend/api/public`.replace(/([^:])\/{2,}/g, "$1/");
    }

    return "http://localhost/ProyectoNuevo/backend/api/public";
  }

  function adminUrl() {
    return `${getApiBase()}/admin`;
  }

  function isHttpUrl(s) {
    return typeof s === "string" && /^https?:\/\//i.test(s.trim());
  }

  function initials(nombre) {
    const parts = String(nombre || "")
      .trim()
      .split(/\s+/)
      .filter(Boolean);
    if (!parts.length) return "?";
    if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
  }

  function excerpt(text, max) {
    const t = String(text || "").trim();
    if (t.length <= max) return t;
    return t.slice(0, max).trim() + "…";
  }

  function formatMoney(n) {
    const x = Number(n);
    if (Number.isNaN(x)) return "—";
    try {
      return new Intl.NumberFormat("es-PE", { style: "currency", currency: "PEN" }).format(x);
    } catch {
      return `S/ ${x.toFixed(2)}`;
    }
  }

  async function fetchJson(path) {
    let res;
    try {
      res = await fetch(`${getApiBase()}/api${path}`, {
        headers: { Accept: "application/json" },
      });
    } catch (e) {
      const msg =
        e && e.message
          ? e.message
          : "No se pudo conectar (¿abres el HTML por file://? Usa http://localhost/…/frontend/ o ajusta la meta nova-api-base).";
      throw new Error(msg);
    }
    if (!res.ok) throw new Error(`Error ${res.status} al leer ${path}`);
    return res.json();
  }

  function renderEspecialidades(el) {
    const run = async () => {
      el.innerHTML = `<div class="catalog-loading"><div class="catalog-skeleton"></div><p class="muted">Cargando especialidades…</p></div>`;
      try {
        const list = await fetchJson("/especialidades");
        if (!Array.isArray(list) || list.length === 0) {
          el.innerHTML = `<div class="catalog-empty card card--soft"><p class="muted" style="margin:0;">No hay especialidades en la base de datos. Créalas en el <a class="card__link" href="${adminUrl()}">panel de administración</a>.</p></div>`;
          return;
        }
        const cards = list
          .map((e) => {
            const img = isHttpUrl(e.imagen)
              ? `<div class="catalog-card__media"><img src="${e.imagen}" alt="" loading="lazy" /></div>`
              : `<div class="catalog-card__media catalog-card__media--placeholder" aria-hidden="true">🏥</div>`;
            return `<article class="card card--catalog">
              ${img}
              <h3 class="card__title">${escapeHtml(e.nombre)}</h3>
              <p class="card__text">Consultas y procedimientos bajo esta especialidad.</p>
              <a class="card__link" href="./contacto.html">Consultar</a>
            </article>`;
          })
          .join("");
        el.innerHTML = `<div class="grid grid--3">${cards}</div>`;
      } catch (err) {
        el.innerHTML = `<div class="catalog-error card"><p style="margin:0 0 8px;font-weight:700;">No se pudieron cargar las especialidades</p><p class="muted" style="margin:0 0 12px;">${escapeHtml(String(err.message || err))}</p><button type="button" class="btn btn--primary catalog-retry">Reintentar</button></div>`;
        el.querySelector(".catalog-retry")?.addEventListener("click", run);
      }
    };
    run();
  }

  function renderMedicos(el) {
    const run = async () => {
      el.innerHTML = `<div class="catalog-loading"><div class="catalog-skeleton"></div><p class="muted">Cargando equipo médico…</p></div>`;
      try {
        const list = await fetchJson("/medicos");
        if (!Array.isArray(list) || list.length === 0) {
          el.innerHTML = `<div class="catalog-empty card card--soft"><p class="muted" style="margin:0;">No hay médicos registrados. Añádelos en el <a class="card__link" href="${adminUrl()}">panel de administración</a>.</p></div>`;
          return;
        }
        const cards = list
          .map((m) => {
            const role = m.especialidad && m.especialidad.nombre ? escapeHtml(m.especialidad.nombre) : "—";
            const av = isHttpUrl(m.foto)
              ? `<div class="avatar avatar--photo"><img src="${m.foto}" alt="" loading="lazy" /></div>`
              : `<div class="avatar" aria-hidden="true">${escapeHtml(initials(m.nombre))}</div>`;
            return `<article class="profile profile--catalog">${av}<h3 class="profile__name">${escapeHtml(m.nombre)}</h3><p class="profile__role">${role}</p></article>`;
          })
          .join("");
        el.innerHTML = `<div class="grid grid--4">${cards}</div>`;
      } catch (err) {
        el.innerHTML = `<div class="catalog-error card"><p style="margin:0 0 8px;font-weight:700;">No se pudo cargar el equipo médico</p><p class="muted" style="margin:0 0 12px;">${escapeHtml(String(err.message || err))}</p><button type="button" class="btn btn--primary catalog-retry">Reintentar</button></div>`;
        el.querySelector(".catalog-retry")?.addEventListener("click", run);
      }
    };
    run();
  }

  function renderServicios(el) {
    const run = async () => {
      el.innerHTML = `<div class="catalog-loading"><div class="catalog-skeleton"></div><p class="muted">Cargando servicios…</p></div>`;
      try {
        const list = await fetchJson("/servicios");
        if (!Array.isArray(list) || list.length === 0) {
          el.innerHTML = `<div class="catalog-empty card card--soft"><p class="muted" style="margin:0;">No hay servicios publicados. Créalos en el <a class="card__link" href="${adminUrl()}">panel de administración</a>.</p></div>`;
          return;
        }
        const cards = list
          .map((s) => {
            const med = s.medico && s.medico.nombre ? escapeHtml(s.medico.nombre) : "—";
            const desc = escapeHtml(excerpt(s.descripcion, 140));
            const price = formatMoney(s.precio);
            return `<article class="card card--catalog card--servicio">
              <p class="catalog-price">${price}</p>
              <h3 class="card__title">${escapeHtml(s.nombre)}</h3>
              <p class="card__text">${desc}</p>
              <p class="catalog-meta muted">Dr(a). ${med}</p>
              <a class="card__link" href="./contacto.html">Solicitar información</a>
            </article>`;
          })
          .join("");
        el.innerHTML = `<div class="grid grid--3">${cards}</div>`;
      } catch (err) {
        el.innerHTML = `<div class="catalog-error card"><p style="margin:0 0 8px;font-weight:700;">No se pudieron cargar los servicios</p><p class="muted" style="margin:0 0 12px;">${escapeHtml(String(err.message || err))}</p><button type="button" class="btn btn--primary catalog-retry">Reintentar</button></div>`;
        el.querySelector(".catalog-retry")?.addEventListener("click", run);
      }
    };
    run();
  }

  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;");
  }

  async function fillSpecialtySelects() {
    const selects = document.querySelectorAll('select[name="specialty"]');
    if (!selects.length) return;
    try {
      const list = await fetchJson("/especialidades");
      if (!Array.isArray(list)) return;
      selects.forEach((sel) => {
        const placeholder = sel.querySelector('option[value=""]') || sel.options[0];
        sel.querySelectorAll("option").forEach((opt) => {
          if (opt === placeholder) return;
          opt.remove();
        });
        list.forEach((e) => {
          const o = document.createElement("option");
          o.value = e.nombre;
          o.textContent = e.nombre;
          sel.appendChild(o);
        });
      });
    } catch {
      /* deja opciones estáticas */
    }
  }

  async function updateLiveStats() {
    const nodes = document.querySelectorAll("[data-live-stat]");
    if (!nodes.length) return;
    try {
      const [esp, med, srv] = await Promise.all([
        fetchJson("/especialidades"),
        fetchJson("/medicos"),
        fetchJson("/servicios"),
      ]);
      const map = {
        especialidades: Array.isArray(esp) ? esp.length : "—",
        medicos: Array.isArray(med) ? med.length : "—",
        servicios: Array.isArray(srv) ? srv.length : "—",
      };
      nodes.forEach((n) => {
        const k = n.getAttribute("data-live-stat");
        if (k && map[k] !== undefined) n.textContent = String(map[k]);
      });
    } catch {
      nodes.forEach((n) => {
        if (n.textContent === "—") return;
      });
    }
  }

  function boot() {
    document.querySelectorAll("[data-catalog-mount]").forEach((el) => {
      const kind = el.getAttribute("data-catalog-mount");
      if (kind === "especialidades") renderEspecialidades(el);
      else if (kind === "medicos") renderMedicos(el);
      else if (kind === "servicios") renderServicios(el);
    });
    fillSpecialtySelects();
    updateLiveStats();
  }

  if (document.readyState === "loading") document.addEventListener("DOMContentLoaded", boot);
  else boot();

  window.Catalog = { getApiBase, fetchJson, boot };
})();
