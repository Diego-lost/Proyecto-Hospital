import { useState, useEffect, useRef } from "react";
import {
  Phone, Mail, MapPin, Menu, X, Calendar, CreditCard,
  Users, AlertCircle, ChevronRight, Heart, Brain,
  Bone, Eye, Baby, Stethoscope, ArrowRight, Clock,
  Shield, Star, Building2, FileText, Activity,
  CheckCircle, ChevronDown
} from "lucide-react";

const NAV_LINKS = [
  { label: "Inicio", href: "#inicio" },
  { label: "Institucional", href: "#institucional" },
  { label: "Organización", href: "#organizacion" },
  { label: "Prensa", href: "#prensa" },
  { label: "Atención al ciudadano", href: "#atencion" },
];

const FEATURES = [
  { icon: Calendar, title: "Cita en línea", desc: "Agenda y confirmación en segundos" },
  { icon: CreditCard, title: "Pago seguro", desc: "Tu pago protegido en todo momento" },
  { icon: Users, title: "Especialistas", desc: "Catálogo completo al alcance de tu mano" },
  { icon: AlertCircle, title: "Emergencia 24/7", desc: "Disponibles cuando más nos necesitas" },
];

const SPECIALTIES = [
  { icon: Heart, name: "Cardiología", desc: "Consultas y procedimientos bajo cita para especialista", color: "text-rose-500", bg: "bg-rose-50" },
  { icon: Brain, name: "Psicología", desc: "Consultas y procedimientos bajo cita para especialista", color: "text-violet-500", bg: "bg-violet-50" },
  { icon: Bone, name: "Traumatología", desc: "Consultas y procedimientos bajo cita para especialista", color: "text-amber-500", bg: "bg-amber-50" },
  { icon: Eye, name: "Oftalmología", desc: "Consultas y procedimientos bajo cita para especialista", color: "text-sky-500", bg: "bg-sky-50" },
  { icon: Baby, name: "Pediatría", desc: "Consultas y procedimientos bajo cita para especialista", color: "text-emerald-500", bg: "bg-emerald-50" },
  { icon: Stethoscope, name: "Medicina General", desc: "Consultas y procedimientos bajo cita para especialista", color: "text-teal-600", bg: "bg-teal-50" },
];

const CATEGORIES = [
  { number: "01", label: "CARTERA CLÍNICA", title: "Especialidades", desc: "Cardiología, pediatría, traumatología y más", icon: Stethoscope, href: "#especialidades" },
  { number: "02", label: "TARIFAS PUBLIQUES", title: "Servicios", desc: "Compara y elige según tus necesidades", icon: FileText, href: "#servicios" },
  { number: "03", label: "PROFESIONALES EN", title: "Equipo médico", desc: "Conoce a nuestros especialistas", icon: Users, href: "#equipo" },
  { number: "04", label: "DÓNDE ATENDEMOS", title: "Sedes", desc: "Horario, dirección y cómo llegar", icon: Building2, href: "#sedes" },
];

const ARTICLES = [
  {
    tag: "Corazón",
    title: "5 señales para chequear tu corazón",
    desc: "Verifica síntomas y cuándo acudir a consulta",
    image: "https://images.unsplash.com/photo-1628771065518-0d82f1938462?w=600&h=400&fit=crop&auto=format",
  },
  {
    tag: "Prevención",
    title: "Vacunas: calendario y recomendaciones",
    desc: "Guía rápida para mantener el esquema al día",
    image: "https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=600&h=400&fit=crop&auto=format",
  },
  {
    tag: "Bienestar",
    title: "Sueño y salud: hábitos que funcionan",
    desc: "Rutinas simples para dormir mejor",
    image: "https://images.unsplash.com/photo-1545205597-3d9d02c29597?w=600&h=400&fit=crop&auto=format",
  },
];

const SERVICES = [
  { name: "Consulta General", price: "S/ 45", duration: "30 min" },
  { name: "Consulta Especialista", price: "S/ 90", duration: "45 min" },
  { name: "Consulta Psicología", price: "S/ 70", duration: "50 min" },
  { name: "Ecografía", price: "S/ 120", duration: "20 min" },
  { name: "Laboratorio (básico)", price: "S/ 35", duration: "—" },
  { name: "Radiografía", price: "S/ 55", duration: "15 min" },
];

function useCountUp(target: number, duration = 1600, active = false) {
  const [count, setCount] = useState(0);
  useEffect(() => {
    if (!active) return;
    let start = 0;
    const step = Math.ceil(target / (duration / 16));
    const timer = setInterval(() => {
      start += step;
      if (start >= target) { setCount(target); clearInterval(timer); }
      else setCount(start);
    }, 16);
    return () => clearInterval(timer);
  }, [target, duration, active]);
  return count;
}

function StatCounter({ value, label }: { value: number; label: string }) {
  const ref = useRef<HTMLDivElement>(null);
  const [active, setActive] = useState(false);
  const count = useCountUp(value, 1400, active);

  useEffect(() => {
    const el = ref.current;
    if (!el) return;
    const obs = new IntersectionObserver(([e]) => { if (e.isIntersecting) setActive(true); }, { threshold: 0.5 });
    obs.observe(el);
    return () => obs.disconnect();
  }, []);

  return (
    <div ref={ref} className="text-center">
      <p className="text-5xl font-bold" style={{ fontFamily: "'Fraunces', serif", color: "var(--accent)" }}>
        {count}
      </p>
      <p className="mt-1 text-sm font-medium text-muted-foreground uppercase tracking-widest">{label}</p>
    </div>
  );
}

export default function App() {
  const [menuOpen, setMenuOpen] = useState(false);
  const [activeSpec, setActiveSpec] = useState<string | null>(null);
  const [scrolled, setScrolled] = useState(false);

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 40);
    window.addEventListener("scroll", onScroll);
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  return (
    <div className="min-h-screen bg-background" style={{ fontFamily: "'DM Sans', sans-serif" }}>

      {/* ── Top bar ── */}
      <div className="hidden md:flex items-center justify-between px-8 py-2 text-xs text-muted-foreground border-b border-border bg-secondary">
        <div className="flex items-center gap-6">
          <span className="flex items-center gap-1.5"><MapPin size={12} />Av. Principal 123, Lima</span>
          <span className="flex items-center gap-1.5"><Phone size={12} />555 123-4567</span>
          <span className="flex items-center gap-1.5"><Mail size={12} />contacto@novasalud.pe</span>
        </div>
        <div className="flex items-center gap-3">
          <a href="#pago" className="hover:text-foreground transition-colors">Pago en línea</a>
          <span className="text-border">|</span>
          <a href="#cita" className="hover:text-foreground transition-colors font-medium">Agendar cita</a>
        </div>
      </div>

      {/* ── Navbar ── */}
      <header
        className={`sticky top-0 z-50 transition-all duration-300 ${
          scrolled ? "bg-white/95 backdrop-blur-sm shadow-sm" : "bg-white"
        } border-b border-border`}
      >
        <div className="max-w-7xl mx-auto px-6 h-16 flex items-center justify-between">
          {/* Logo */}
          <a href="#" className="flex items-center gap-2.5 flex-shrink-0">
            <div className="w-8 h-8 rounded-lg flex items-center justify-center" style={{ background: "var(--primary)" }}>
              <Activity size={16} color="white" />
            </div>
            <div className="leading-tight">
              <span className="font-bold text-sm tracking-tight" style={{ color: "var(--primary)", fontFamily: "'Fraunces', serif" }}>
                Clínica
              </span>
              <span className="block text-xs font-semibold" style={{ color: "var(--accent)" }}>
                NovaSalud
              </span>
            </div>
          </a>

          {/* Desktop nav */}
          <nav className="hidden lg:flex items-center gap-1">
            {NAV_LINKS.map((l) => (
              <a
                key={l.label}
                href={l.href}
                className="px-3 py-2 text-sm rounded-md transition-colors hover:bg-secondary text-muted-foreground hover:text-foreground"
              >
                {l.label}
              </a>
            ))}
          </nav>

          {/* CTAs */}
          <div className="hidden md:flex items-center gap-3">
            <a
              href="#pago"
              className="px-4 py-2 text-sm font-medium rounded-lg border border-border hover:bg-secondary transition-colors"
              style={{ color: "var(--primary)" }}
            >
              Pago en línea
            </a>
            <a
              href="#cita"
              className="px-4 py-2 text-sm font-medium rounded-lg text-white transition-all hover:opacity-90 shadow-sm"
              style={{ background: "var(--accent)" }}
            >
              Agendar cita
            </a>
          </div>

          {/* Mobile toggle */}
          <button
            className="lg:hidden p-2 rounded-md hover:bg-secondary transition-colors"
            onClick={() => setMenuOpen(!menuOpen)}
          >
            {menuOpen ? <X size={20} /> : <Menu size={20} />}
          </button>
        </div>

        {/* Mobile menu */}
        {menuOpen && (
          <div className="lg:hidden border-t border-border bg-white px-6 pb-4">
            {NAV_LINKS.map((l) => (
              <a key={l.label} href={l.href} className="block py-3 text-sm border-b border-border last:border-0 text-muted-foreground hover:text-foreground transition-colors">
                {l.label}
              </a>
            ))}
            <div className="flex gap-3 pt-4">
              <a href="#pago" className="flex-1 text-center py-2.5 text-sm font-medium rounded-lg border border-border" style={{ color: "var(--primary)" }}>
                Pago en línea
              </a>
              <a href="#cita" className="flex-1 text-center py-2.5 text-sm font-medium rounded-lg text-white" style={{ background: "var(--accent)" }}>
                Agendar cita
              </a>
            </div>
          </div>
        )}
      </header>

      {/* ── Hero ── */}
      <section id="inicio" className="relative overflow-hidden" style={{ background: "var(--primary)" }}>
        <div className="absolute inset-0 opacity-10"
          style={{ backgroundImage: "radial-gradient(circle at 70% 50%, #2FA89E 0%, transparent 60%)" }}
        />
        <div className="max-w-7xl mx-auto px-6 py-20 md:py-28 grid md:grid-cols-2 gap-12 items-center relative z-10">
          {/* Text */}
          <div>
            <span className="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium mb-6"
              style={{ background: "rgba(47,168,158,0.2)", color: "#6ECFC8" }}>
              <span className="w-1.5 h-1.5 rounded-full bg-current" />
              Portal Transparencia activo
            </span>
            <h1
              className="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight mb-6"
              style={{ fontFamily: "'Fraunces', serif", fontWeight: 700 }}
            >
              Especialistas
              <br />
              <span style={{ color: "#6ECFC8" }}>integrados</span>
              <br />
              para ti.
            </h1>
            <p className="text-base md:text-lg mb-8 max-w-md" style={{ color: "rgba(255,255,255,0.7)" }}>
              Un equipo calificado te acompaña antes, durante y después de tu atención.
              Cardiología, pediatría, traumatología y más.
            </p>
            <div className="flex flex-wrap gap-3">
              <a
                href="#especialidades"
                className="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-white transition-all hover:opacity-90 shadow-lg"
                style={{ background: "var(--accent)" }}
              >
                Ver especialistas <ArrowRight size={16} />
              </a>
              <a
                href="#cita"
                className="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold transition-all hover:bg-white/20"
                style={{ color: "white", border: "1.5px solid rgba(255,255,255,0.3)" }}
              >
                <Calendar size={16} /> Agendar cita
              </a>
            </div>
          </div>

          {/* Image */}
          <div className="relative hidden md:block">
            <div className="absolute -inset-4 rounded-3xl opacity-20" style={{ background: "var(--accent)" }} />
            <img
              src="https://images.unsplash.com/photo-1638202993928-7267aad84c31?w=700&h=800&fit=crop&auto=format"
              alt="Doctora de Clínica NovaSalud"
              className="relative rounded-2xl w-full object-cover shadow-2xl"
              style={{ maxHeight: 460 }}
            />
            {/* Floating badge */}
            <div className="absolute -bottom-4 -left-4 bg-white rounded-2xl shadow-xl p-4 flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl flex items-center justify-center" style={{ background: "var(--secondary)" }}>
                <Shield size={18} style={{ color: "var(--accent)" }} />
              </div>
              <div>
                <p className="text-xs text-muted-foreground">Atención</p>
                <p className="text-sm font-bold" style={{ color: "var(--primary)" }}>Emergencia 24/7</p>
              </div>
            </div>
            <div className="absolute -top-4 -right-4 bg-white rounded-2xl shadow-xl p-4 flex items-center gap-3">
              <div className="w-10 h-10 rounded-xl flex items-center justify-center" style={{ background: "var(--secondary)" }}>
                <Star size={18} style={{ color: "#F2994A" }} />
              </div>
              <div>
                <p className="text-xs text-muted-foreground">Calificación</p>
                <p className="text-sm font-bold" style={{ color: "var(--primary)" }}>4.9 / 5.0</p>
              </div>
            </div>
          </div>
        </div>

        {/* Wave divider */}
        <svg viewBox="0 0 1440 50" className="w-full -mb-1 block" fill="white" preserveAspectRatio="none" style={{ height: 50 }}>
          <path d="M0,50 L1440,50 L1440,20 Q1080,0 720,30 Q360,50 0,10 Z" />
        </svg>
      </section>

      {/* ── Trust features ── */}
      <section className="bg-white border-b border-border">
        <div className="max-w-7xl mx-auto px-6 py-8">
          <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
            {FEATURES.map(({ icon: Icon, title, desc }) => (
              <div key={title} className="flex items-start gap-3">
                <div className="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0" style={{ background: "var(--secondary)" }}>
                  <Icon size={18} style={{ color: "var(--accent)" }} />
                </div>
                <div>
                  <p className="text-sm font-semibold" style={{ color: "var(--primary)" }}>{title}</p>
                  <p className="text-xs text-muted-foreground mt-0.5">{desc}</p>
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── Stats ── */}
      <section className="py-16 bg-white">
        <div className="max-w-7xl mx-auto px-6 text-center mb-10">
          <p className="text-xs font-semibold uppercase tracking-widest mb-2" style={{ color: "var(--accent)" }}>
            EN VIVO DESDE EL SISTEMA
          </p>
          <h2 className="text-3xl md:text-4xl font-bold" style={{ fontFamily: "'Fraunces', serif", color: "var(--primary)" }}>
            Lo que tenemos para ti hoy
          </h2>
        </div>
        <div className="max-w-3xl mx-auto px-6 grid grid-cols-3 gap-8">
          <StatCounter value={12} label="Especialidades" />
          <StatCounter value={38} label="Médicos" />
          <StatCounter value={4} label="Sedes" />
        </div>
      </section>

      {/* ── Action cards ── */}
      <section id="cita" className="py-16" style={{ background: "var(--secondary)" }}>
        <div className="max-w-7xl mx-auto px-6">
          <p className="text-xs font-semibold uppercase tracking-widest mb-2" style={{ color: "var(--accent)" }}>
            ACCESO RÁPIDO
          </p>
          <h2 className="text-2xl md:text-3xl font-bold mb-8" style={{ fontFamily: "'Fraunces', serif", color: "var(--primary)" }}>
            Citas y pagos, sin complicaciones
          </h2>
          <div className="grid md:grid-cols-2 gap-6">
            {/* Book */}
            <div className="rounded-2xl p-8 text-white relative overflow-hidden group" style={{ background: "var(--primary)" }}>
              <div className="absolute inset-0 opacity-0 group-hover:opacity-10 transition-opacity"
                style={{ background: "radial-gradient(circle at 80% 20%, white, transparent)" }}
              />
              <span className="inline-block px-3 py-1 rounded-full text-xs font-medium mb-4"
                style={{ background: "rgba(255,255,255,0.15)" }}>AGENDA MÉDICA</span>
              <h3 className="text-2xl font-bold mb-2" style={{ fontFamily: "'Fraunces', serif" }}>
                Reserva tu consulta hoy
              </h3>
              <p className="text-sm mb-6" style={{ color: "rgba(255,255,255,0.7)" }}>
                Completa tus datos y te contactamos para confirmar horario.
              </p>
              <a href="#especialidades"
                className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
                style={{ background: "var(--accent)" }}>
                Agendar ahora <ArrowRight size={14} />
              </a>
            </div>

            {/* Pay */}
            <div className="rounded-2xl p-8 text-white relative overflow-hidden group" style={{ background: "var(--accent)" }}>
              <div className="absolute inset-0 opacity-0 group-hover:opacity-10 transition-opacity"
                style={{ background: "radial-gradient(circle at 80% 20%, white, transparent)" }}
              />
              <span className="inline-block px-3 py-1 rounded-full text-xs font-medium mb-4"
                style={{ background: "rgba(255,255,255,0.2)" }}>FACTURACIÓN EN LÍNEA</span>
              <h3 className="text-2xl font-bold mb-2" style={{ fontFamily: "'Fraunces', serif" }}>
                Paga tu servicio en línea
              </h3>
              <p className="text-sm mb-6" style={{ color: "rgba(255,255,255,0.8)" }}>
                Consultas y procedimientos con tarjeta de forma segura.
              </p>
              <a href="#pago"
                className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold transition-all hover:opacity-90"
                style={{ background: "rgba(255,255,255,0.2)", color: "white" }}>
                Ir a pagos <ArrowRight size={14} />
              </a>
            </div>
          </div>
        </div>
      </section>

      {/* ── Browse categories ── */}
      <section id="organizacion" className="py-16 bg-white">
        <div className="max-w-7xl mx-auto px-6">
          <p className="text-xs font-semibold uppercase tracking-widest mb-2" style={{ color: "var(--accent)" }}>
            EXPLORA EL SISTEMA
          </p>
          <h2 className="text-2xl md:text-3xl font-bold mb-8" style={{ fontFamily: "'Fraunces', serif", color: "var(--primary)" }}>
            Encuentra tu próxima atención
          </h2>
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {CATEGORIES.map(({ number, label, title, desc, icon: Icon, href }) => (
              <a
                key={title}
                href={href}
                className="group relative rounded-2xl p-6 border border-border transition-all duration-200 hover:shadow-md hover:-translate-y-1 bg-card"
              >
                <div className="flex items-center justify-between mb-4">
                  <span className="text-xs font-mono font-bold text-muted-foreground">{number}</span>
                  <div className="w-9 h-9 rounded-xl flex items-center justify-center transition-colors group-hover:bg-accent/10"
                    style={{ background: "var(--secondary)" }}>
                    <Icon size={16} style={{ color: "var(--accent)" }} />
                  </div>
                </div>
                <p className="text-[10px] font-semibold uppercase tracking-widest text-muted-foreground mb-1">{label}</p>
                <h3 className="font-bold text-base mb-1" style={{ color: "var(--primary)" }}>{title}</h3>
                <p className="text-xs text-muted-foreground leading-relaxed mb-4">{desc}</p>
                <span className="inline-flex items-center gap-1 text-xs font-semibold transition-colors group-hover:gap-2"
                  style={{ color: "var(--accent)" }}>
                  Explorar <ChevronRight size={12} />
                </span>
              </a>
            ))}
          </div>
        </div>
      </section>

      {/* ── Specialties catalog ── */}
      <section id="especialidades" className="py-16" style={{ background: "var(--muted)" }}>
        <div className="max-w-7xl mx-auto px-6">
          <div className="flex flex-col md:flex-row md:items-end justify-between mb-8 gap-4">
            <div>
              <p className="text-xs font-semibold uppercase tracking-widest mb-2" style={{ color: "var(--accent)" }}>
                CATÁLOGO CLÍNICO
              </p>
              <h2 className="text-2xl md:text-3xl font-bold" style={{ fontFamily: "'Fraunces', serif", color: "var(--primary)" }}>
                Especialidades
              </h2>
              <p className="text-sm text-muted-foreground mt-1">Datos en vivo desde el sistema administrativo</p>
            </div>
            <a href="#cita" className="inline-flex items-center gap-2 text-sm font-semibold" style={{ color: "var(--accent)" }}>
              Ver todas <ArrowRight size={14} />
            </a>
          </div>

          <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {SPECIALTIES.map(({ icon: Icon, name, desc, color, bg }) => (
              <div
                key={name}
                className={`group rounded-2xl bg-white border border-border p-6 transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 cursor-pointer ${activeSpec === name ? "ring-2" : ""}`}
                style={activeSpec === name ? { ringColor: "var(--accent)" } : {}}
                onClick={() => setActiveSpec(activeSpec === name ? null : name)}
              >
                <div className={`w-11 h-11 rounded-xl flex items-center justify-center mb-4 ${bg}`}>
                  <Icon size={20} className={color} />
                </div>
                <h3 className="font-bold text-base mb-1" style={{ color: "var(--primary)" }}>{name}</h3>
                <p className="text-xs text-muted-foreground leading-relaxed mb-4">{desc}</p>
                <div className="flex items-center justify-between">
                  <a href="#cita" className="inline-flex items-center gap-1 text-xs font-semibold transition-colors hover:underline"
                    style={{ color: "var(--accent)" }}>
                    Consultar <ChevronRight size={11} />
                  </a>
                  <CheckCircle size={14} className={activeSpec === name ? "opacity-100" : "opacity-0"} style={{ color: "var(--accent)" }} />
                </div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {/* ── Services & pricing ── */}
      <section id="servicios" className="py-16 bg-white">
        <div className="max-w-7xl mx-auto px-6">
          <p className="text-xs font-semibold uppercase tracking-widest mb-2" style={{ color: "var(--accent)" }}>
            TARIFAS
          </p>
          <h2 className="text-2xl md:text-3xl font-bold mb-2" style={{ fontFamily: "'Fraunces', serif", color: "var(--primary)" }}>
            Servicios y precios
          </h2>
          <p className="text-sm text-muted-foreground mb-8">Tarifas referenciales. Confirma en recepción o al agendar tu cita.</p>

          <div className="rounded-2xl border border-border overflow-hidden">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-border" style={{ background: "var(--secondary)" }}>
                  <th className="text-left px-6 py-4 font-semibold text-xs uppercase tracking-widest" style={{ color: "var(--primary)" }}>
                    Servicio
                  </th>
                  <th className="text-left px-6 py-4 font-semibold text-xs uppercase tracking-widest hidden md:table-cell" style={{ color: "var(--primary)" }}>
                    Duración
                  </th>
                  <th className="text-right px-6 py-4 font-semibold text-xs uppercase tracking-widest" style={{ color: "var(--primary)" }}>
                    Precio
                  </th>
                  <th className="px-6 py-4 hidden md:table-cell" />
                </tr>
              </thead>
              <tbody>
                {SERVICES.map(({ name, price, duration }, i) => (
                  <tr key={name} className={`border-b border-border last:border-0 transition-colors hover:bg-secondary/50 ${i % 2 === 0 ? "" : ""}`}>
                    <td className="px-6 py-4 font-medium" style={{ color: "var(--primary)" }}>{name}</td>
                    <td className="px-6 py-4 text-muted-foreground hidden md:table-cell">{duration}</td>
                    <td className="px-6 py-4 text-right font-bold" style={{ color: "var(--accent)" }}>{price}</td>
                    <td className="px-6 py-4 hidden md:table-cell text-right">
                      <a href="#cita" className="text-xs font-semibold px-3 py-1.5 rounded-lg transition-colors hover:bg-accent/10"
                        style={{ color: "var(--accent)" }}>
                        Agendar
                      </a>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </div>
      </section>

      {/* ── Articles ── */}
      <section className="py-16" style={{ background: "var(--muted)" }}>
        <div className="max-w-7xl mx-auto px-6">
          <p className="text-xs font-semibold uppercase tracking-widest mb-2" style={{ color: "var(--accent)" }}>
            SALUD Y BIENESTAR
          </p>
          <h2 className="text-2xl md:text-3xl font-bold mb-8" style={{ fontFamily: "'Fraunces', serif", color: "var(--primary)" }}>
            Artículos para ti
          </h2>
          <div className="grid md:grid-cols-3 gap-6">
            {ARTICLES.map(({ tag, title, desc, image }) => (
              <article key={title} className="group rounded-2xl bg-white border border-border overflow-hidden transition-all duration-200 hover:shadow-md hover:-translate-y-0.5 cursor-pointer">
                <div className="overflow-hidden h-48">
                  <img
                    src={image}
                    alt={title}
                    className="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                  />
                </div>
                <div className="p-5">
                  <span className="inline-block px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-widest mb-3"
                    style={{ background: "var(--secondary)", color: "var(--accent)" }}>
                    {tag}
                  </span>
                  <h3 className="font-bold text-base mb-1.5 leading-snug" style={{ color: "var(--primary)" }}>{title}</h3>
                  <p className="text-xs text-muted-foreground leading-relaxed mb-4">{desc}</p>
                  <a href="#" className="inline-flex items-center gap-1 text-xs font-semibold transition-colors"
                    style={{ color: "var(--accent)" }}>
                    Leer más <ArrowRight size={11} />
                  </a>
                </div>
              </article>
            ))}
          </div>
        </div>
      </section>

      {/* ── CTA banner ── */}
      <section className="py-20 relative overflow-hidden" style={{ background: "var(--primary)" }}>
        <div className="absolute inset-0 opacity-10"
          style={{ backgroundImage: "radial-gradient(circle at 30% 50%, #2FA89E 0%, transparent 60%)" }} />
        <div className="max-w-4xl mx-auto px-6 text-center relative z-10">
          <h2 className="text-3xl md:text-4xl font-bold text-white mb-4" style={{ fontFamily: "'Fraunces', serif" }}>
            ¿Listo para tu próxima atención?
          </h2>
          <p className="text-base mb-8" style={{ color: "rgba(255,255,255,0.7)" }}>
            Agenda en minutos o paga tu servicio con tarjeta de forma segura.
          </p>
          <div className="flex flex-wrap items-center justify-center gap-4">
            <a href="#cita"
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl font-semibold text-white shadow-lg transition-all hover:opacity-90"
              style={{ background: "var(--accent)" }}>
              <Calendar size={16} /> Agendar cita
            </a>
            <a href="#pago"
              className="inline-flex items-center gap-2 px-7 py-3.5 rounded-xl font-semibold transition-all hover:bg-white/20"
              style={{ color: "white", border: "1.5px solid rgba(255,255,255,0.35)" }}>
              <CreditCard size={16} /> Pagar en línea
            </a>
          </div>
        </div>
      </section>

      {/* ── Footer ── */}
      <footer className="py-12 border-t border-border" style={{ background: "#06253A" }}>
        <div className="max-w-7xl mx-auto px-6">
          <div className="grid sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-10">
            {/* Brand */}
            <div className="lg:col-span-1">
              <div className="flex items-center gap-2.5 mb-4">
                <div className="w-8 h-8 rounded-lg flex items-center justify-center" style={{ background: "var(--accent)" }}>
                  <Activity size={16} color="white" />
                </div>
                <span className="font-bold text-white" style={{ fontFamily: "'Fraunces', serif" }}>Clínica NovaSalud</span>
              </div>
              <p className="text-xs leading-relaxed" style={{ color: "rgba(255,255,255,0.5)" }}>
                Cuidamos tu salud con profesionalismo, calidez y tecnología de punta.
              </p>
            </div>

            {/* Services */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-widest mb-4" style={{ color: "var(--accent)" }}>Servicios</p>
              {["Pago en línea", "Especialidades", "Sedes", "Seguros", "Manual y políticas"].map((s) => (
                <a key={s} href="#" className="block text-sm mb-2 transition-colors hover:text-white" style={{ color: "rgba(255,255,255,0.55)" }}>
                  {s}
                </a>
              ))}
            </div>

            {/* Contact */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-widest mb-4" style={{ color: "var(--accent)" }}>Contacto</p>
              <div className="space-y-2.5">
                <p className="flex items-center gap-2 text-sm" style={{ color: "rgba(255,255,255,0.55)" }}>
                  <Phone size={13} style={{ color: "var(--accent)" }} /> 555 123-4567
                </p>
                <p className="flex items-center gap-2 text-sm" style={{ color: "rgba(255,255,255,0.55)" }}>
                  <Mail size={13} style={{ color: "var(--accent)" }} /> contacto@novasalud.pe
                </p>
                <p className="flex items-start gap-2 text-sm" style={{ color: "rgba(255,255,255,0.55)" }}>
                  <MapPin size={13} className="mt-0.5 flex-shrink-0" style={{ color: "var(--accent)" }} /> Av. Principal 123, Lima
                </p>
              </div>
            </div>

            {/* Hours */}
            <div>
              <p className="text-xs font-semibold uppercase tracking-widest mb-4" style={{ color: "var(--accent)" }}>Horario</p>
              <div className="space-y-2">
                <div className="flex items-center gap-2">
                  <Clock size={13} style={{ color: "var(--accent)" }} />
                  <div>
                    <p className="text-sm font-medium text-white">Lun – Sab</p>
                    <p className="text-xs" style={{ color: "rgba(255,255,255,0.5)" }}>9:00 – 21:00</p>
                  </div>
                </div>
                <div className="flex items-center gap-2 mt-3">
                  <div className="w-2 h-2 rounded-full animate-pulse" style={{ background: "#4ade80" }} />
                  <p className="text-sm font-medium" style={{ color: "rgba(255,255,255,0.8)" }}>Emergencia 24/7</p>
                </div>
              </div>
            </div>
          </div>

          <div className="pt-8 border-t flex flex-col md:flex-row items-center justify-between gap-3"
            style={{ borderColor: "rgba(255,255,255,0.08)" }}>
            <p className="text-xs" style={{ color: "rgba(255,255,255,0.35)" }}>
              © 2026 Clínica NovaSalud. Todos los derechos reservados.
            </p>
            <div className="flex gap-5">
              {["Privacidad", "Términos", "Transparencia"].map((l) => (
                <a key={l} href="#" className="text-xs transition-colors hover:text-white" style={{ color: "rgba(255,255,255,0.35)" }}>
                  {l}
                </a>
              ))}
            </div>
          </div>
        </div>
      </footer>
    </div>
  );
}
