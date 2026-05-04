function qs(sel, root = document) {
  return root.querySelector(sel);
}

function qsa(sel, root = document) {
  return Array.from(root.querySelectorAll(sel));
}

function getApiBaseForMain() {
  if (window.Catalog && typeof window.Catalog.getApiBase === "function") {
    return window.Catalog.getApiBase();
  }
  const meta = document.querySelector('meta[name="nova-api-base"]');
  const raw = meta && meta.content ? meta.content.trim() : "";
  if (/^https?:\/\//i.test(raw)) return raw.replace(/\/$/, "");
  if (raw.startsWith("/") && window.location.host) {
    return `${window.location.protocol}//${window.location.host}${raw}`.replace(/\/$/, "");
  }
  if (raw.startsWith("/")) return `http://localhost${raw}`.replace(/\/$/, "");
  return "http://localhost/ProyectoNuevo/backend/api/public";
}

async function fetchBusquedaPacientePorDni(dni) {
  const clean = String(dni || "").replace(/\s/g, "");
  if (clean.length < 4) return null;
  const res = await fetch(
    `${getApiBaseForMain()}/api/busqueda/paciente?dni=${encodeURIComponent(clean)}`,
    { headers: { Accept: "application/json" } }
  );
  return res.json().catch(() => null);
}

async function postSolicitudCita(payload) {
  const params = new URLSearchParams();
  Object.entries(payload).forEach(([k, v]) => {
    if (v === null || typeof v === "undefined") return;
    params.set(k, String(v));
  });

  const res = await fetch(`${getApiBaseForMain()}/api/solicitudes-citas`, {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8",
      Accept: "application/json",
    },
    body: params.toString(),
  });
  const json = await res.json().catch(() => null);
  if (!res.ok) {
    const details = json && json.errors ? JSON.stringify(json.errors) : "Error desconocido";
    throw new Error(details);
  }
  return json;
}

function safeShowModal(dialog) {
  if (!dialog) return;
  if (typeof dialog.showModal === "function") dialog.showModal();
  else dialog.removeAttribute("hidden");
}

function safeCloseModal(dialog) {
  if (!dialog) return;
  if (typeof dialog.close === "function") dialog.close();
  else dialog.setAttribute("hidden", "");
}

function setYear() {
  const el = qs("[data-year]");
  if (el) el.textContent = String(new Date().getFullYear());
}

function setupStickyHeader() {
  const header = qs("[data-header]");
  if (!header) return;

  const onScroll = () => {
    const y = window.scrollY || 0;
    header.style.boxShadow = y > 8 ? "0 10px 18px rgba(11, 19, 32, 0.08)" : "none";
  };

  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });
}

function setupDrawer() {
  const toggle = qs("[data-menu-toggle]");
  const drawer = qs("[data-drawer]");
  const drawerLinks = qsa("[data-drawer-link]");

  if (!toggle || !drawer) return;

  const open = () => {
    drawer.hidden = false;
    toggle.setAttribute("aria-expanded", "true");
    document.documentElement.style.overflow = "hidden";
  };

  const close = () => {
    drawer.hidden = true;
    toggle.setAttribute("aria-expanded", "false");
    document.documentElement.style.overflow = "";
  };

  toggle.addEventListener("click", () => {
    if (drawer.hidden) open();
    else close();
  });

  drawer.addEventListener("click", (e) => {
    if (e.target.closest("[data-menu-close]")) close();
  });
  drawerLinks.forEach((a) => a.addEventListener("click", close));
  window.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && !drawer.hidden) close();
  });
}

function setupDniBuscarPaciente() {
  qsa('[data-dni-buscar="paciente"]').forEach((btn) => {
    btn.addEventListener("click", async () => {
      const form = btn.closest("form");
      if (!form) return;
      const dniInput = form.querySelector('[name="paciente_dni"]');
      if (!dniInput) return;
      const clean = String(dniInput.value || "").replace(/\s/g, "");
      if (clean.length < 4) {
        alert("Escribe al menos 4 dígitos del DNI.");
        return;
      }

      const prevLabel = btn.textContent;
      btn.disabled = true;
      btn.textContent = "…";

      try {
        const j = await fetchBusquedaPacientePorDni(clean);
        if (!j || !j.encontrado || !j.datos) {
          const detalle = j && j.detalle ? String(j.detalle) : "";
          const porDetalle = {
            sin_token:
              "En el servidor Laravel no está configurado CONSULTASPERU_API_TOKEN (.env). Sin eso no se puede consultar RENIEC; escribe el nombre a mano o pide al administrador que active el servicio.",
            dni_invalido: "Para RENIEC el DNI debe tener exactamente 8 dígitos (solo números).",
            red: "No hubo conexión con el servicio de consulta. Revisa internet o intenta más tarde.",
            no_autorizado:
              "El token de Consultas Perú es inválido o venció. En el servidor, revisa CONSULTASPERU_API_TOKEN en .env.",
            error_http: "El servicio de consulta respondió con error. Intenta más tarde.",
            sin_datos:
              "No hay datos para ese DNI en RENIEC (documento inexistente o sin información) ni solicitudes previas en la clínica. Verifica el número o escribe el nombre manualmente.",
          };
          const msg =
            detalle && porDetalle[detalle]
              ? porDetalle[detalle]
              : clean.length === 8
                ? porDetalle.sin_datos
                : "No hay solicitudes guardadas con ese DNI. Ingresa 8 dígitos para intentar RENIEC, o completa el nombre a mano.";
          alert(msg);
          return;
        }

        const n = form.querySelector('[name="name"]');
        const ph = form.querySelector('[name="phone"]');
        const em = form.querySelector('[name="email"]');

        if (j.datos.nombre && n) n.value = j.datos.nombre;
        if (j.datos.telefono && ph) ph.value = j.datos.telefono;
        if (j.datos.email != null && String(j.datos.email).trim() !== "" && em) {
          em.value = j.datos.email;
        }
      } catch {
        alert("No se pudo consultar el DNI. Revisa la conexión y la URL del API en la página.");
      } finally {
        btn.disabled = false;
        btn.textContent = prevLabel;
      }
    });
  });
}

function setupAppointmentModal() {
  const openBtns = qsa("[data-open-appointment]");
  const dialog = qs("[data-appointment-modal]");
  const form = qs("[data-appointment-form]");
  if (!dialog) return;

  openBtns.forEach((b) =>
    b.addEventListener("click", () => {
      safeShowModal(dialog);
    })
  );

  if (form) {
    form.addEventListener("submit", async (e) => {
      const submitter = e.submitter;
      if (!submitter || submitter.value !== "confirm") return;

      e.preventDefault();
      const data = new FormData(form);

      const payload = {
        nombre: String(data.get("name") || "").trim(),
        paciente_dni: String(data.get("paciente_dni") || "").replace(/\s/g, "") || null,
        telefono: String(data.get("phone") || "").trim(),
        email: String(data.get("email") || "").trim() || null,
        especialidad: String(data.get("specialty") || "").trim() || null,
        fecha: String(data.get("date") || "").trim() || null,
        hora: null,
        motivo: String(data.get("reason") || "").trim() || null,
        origen: "web",
      };

      try {
        await postSolicitudCita(payload);

        safeCloseModal(dialog);
        form.reset();
        window.setTimeout(() => {
          alert("Solicitud registrada. Te contactaremos para confirmar disponibilidad.");
        }, 50);
      } catch (err) {
        alert(
          "No pude registrar la solicitud en el backend. " +
            "Verifica que Laravel esté corriendo y que la URL sea accesible.\n\n" +
            String(err && err.message ? err.message : err)
        );
      }
    });
  }
}

function setupSearchModal() {
  const openBtn = qs("[data-open-search]");
  const dialog = qs("[data-search-modal]");
  const form = qs("[data-search-form]");
  if (!openBtn || !dialog) return;

  openBtn.addEventListener("click", () => safeShowModal(dialog));

  if (form) {
    form.addEventListener("submit", (e) => {
      const submitter = e.submitter;
      if (submitter && submitter.value === "confirm") {
        e.preventDefault();
        const q = String(new FormData(form).get("q") || "").trim().toLowerCase();
        safeCloseModal(dialog);
        if (!q) return;

        const links = qsa(".menu__link");
        const match = links.find((a) => a.textContent.toLowerCase().includes(q));
        if (match) {
          match.click();
          return;
        }
        if (
          /manual|pol[ií]tic|norma|normativ|iso|transparen|comunicado|mof|rof|ris|deber|derecho/.test(q)
        ) {
          window.location.href = "./manual-politicas.html";
          return;
        }
        alert(
          "No encontré una sección con ese texto. Prueba: especialidades, manual y políticas, sedes, seguros."
        );
      }
    });
  }
}

function setupContactForm() {
  const form = qs("[data-contact-form]");
  if (!form) return;

  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const data = new FormData(form);
    const name = String(data.get("name") || "").trim();

    const payload = {
      nombre: name,
      telefono: String(data.get("phone") || "").trim(),
      email: null,
      especialidad: String(data.get("specialty") || "").trim() || null,
      fecha: null,
      hora: null,
      motivo: String(data.get("message") || "").trim() || null,
      origen: "web-contacto",
    };

    try {
      await postSolicitudCita(payload);
      form.reset();
      alert(`Gracias${name ? `, ${name}` : ""}. Registramos tu solicitud y te contactaremos.`);
    } catch (err) {
      alert(
        "No pude registrar tu mensaje en el backend.\n\n" +
          String(err && err.message ? err.message : err)
      );
    }
  });
}

setYear();
setupStickyHeader();
setupDrawer();
setupDniBuscarPaciente();
setupAppointmentModal();
setupSearchModal();
setupContactForm();
