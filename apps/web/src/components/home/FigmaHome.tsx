import { useCallback, useEffect, useState } from 'react';
import { Link } from 'react-router-dom';
import {
  AlertCircle,
  ArrowRight,
  Calendar,
  CheckCircle,
  ChevronRight,
  CreditCard,
  Shield,
  Star,
  Users,
} from 'lucide-react';
import StatCounter from '../ui/StatCounter';
import SectionOverlay from './SectionOverlay';
import { HOME_SECTION_IMAGES } from './homeImages';
import { useRefetchWhenTabVisible } from '../../hooks/useRefetchWhenTabVisible';
import { useSupabaseTablesReload } from '../../hooks/useSupabaseTablesReload';
import { especialidadCardImage, formatMoney, initials, isHttpUrl } from '../../lib/catalogUtils';
import { fetchEspecialidades, fetchMedicos, fetchServicios } from '../../lib/remoteCatalog';
import { portalTransparenciaHref } from '../../config/hospitalNav';
import type { EspecialidadRow, MedicoRow, ServicioRow } from '../../types/catalogRows';

const FEATURES = [
  { icon: Calendar, title: 'Cita en línea', desc: 'Agenda y confirmación en segundos' },
  { icon: CreditCard, title: 'Pago seguro', desc: 'Tu pago protegido en todo momento' },
  { icon: Users, title: 'Especialistas', desc: 'Catálogo completo al alcance de tu mano' },
  { icon: AlertCircle, title: 'Emergencia 24/7', desc: 'Disponibles cuando más nos necesitas' },
];

const ARTICLES = [
  {
    tag: 'Corazón',
    title: '5 señales para chequear tu corazón',
    desc: 'Verifica síntomas y cuándo acudir a consulta',
    image: 'https://images.unsplash.com/photo-1628771065518-0d82f1938462?w=600&h=400&fit=crop&auto=format',
    to: '/blog',
  },
  {
    tag: 'Prevención',
    title: 'Vacunas: calendario y recomendaciones',
    desc: 'Guía rápida para mantener el esquema al día',
    image: 'https://images.unsplash.com/photo-1584820927498-cfe5211fd8bf?w=600&h=400&fit=crop&auto=format',
    to: '/blog',
  },
  {
    tag: 'Bienestar',
    title: 'Sueño y salud: hábitos que funcionan',
    desc: 'Rutinas simples para dormir mejor',
    image: 'https://images.unsplash.com/photo-1545205597-3d9d02c29597?w=600&h=400&fit=crop&auto=format',
    to: '/blog',
  },
];

function useLiveCounts() {
  const [esp, setEsp] = useState(0);
  const [med, setMed] = useState(0);
  const [srv, setSrv] = useState(0);

  const load = useCallback(async () => {
    try {
      const [e, m, s] = await Promise.all([fetchEspecialidades(), fetchMedicos(), fetchServicios()]);
      setEsp(Array.isArray(e) ? e.length : 0);
      setMed(Array.isArray(m) ? m.length : 0);
      setSrv(Array.isArray(s) ? s.length : 0);
    } catch {
      /* keep previous */
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  useRefetchWhenTabVisible(() => void load());
  useSupabaseTablesReload(['especialidades', 'medicos', 'servicios'], () => void load());

  return { esp, med, srv };
}

export function HomeHero() {
  return (
    <section id="inicio" className="relative overflow-hidden bg-primary">
      <div
        className="absolute inset-0 opacity-10"
        style={{ backgroundImage: 'radial-gradient(circle at 70% 50%, #2FA89E 0%, transparent 60%)' }}
      />
      <div className="relative z-10 mx-auto grid max-w-7xl items-center gap-12 px-6 py-20 md:grid-cols-2 md:py-28">
        <div>
          <span className="mb-6 inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-medium text-[#6ECFC8]" style={{ background: 'rgba(47,168,158,0.2)' }}>
            <span className="h-1.5 w-1.5 rounded-full bg-current" />
            <a href={portalTransparenciaHref} target="_blank" rel="noopener noreferrer" className="hover:underline">
              Portal Transparencia activo
            </a>
          </span>
          <h1 className="font-display mb-6 text-4xl leading-tight font-bold text-white md:text-5xl lg:text-6xl">
            Especialistas
            <br />
            <span className="text-[#6ECFC8]">integrados</span>
            <br />
            para ti.
          </h1>
          <p className="mb-8 max-w-md text-base text-white/70 md:text-lg">
            Un equipo calificado te acompaña antes, durante y después de tu atención. Cardiología, pediatría,
            traumatología y más.
          </p>
          <div className="flex flex-wrap gap-3">
            <Link
              to="/especialidades"
              className="inline-flex items-center gap-2 rounded-xl bg-accent px-6 py-3 font-semibold text-white shadow-lg transition-all hover:opacity-90"
            >
              Ver especialistas <ArrowRight size={16} />
            </Link>
            <Link
              to="/cita"
              className="inline-flex items-center gap-2 rounded-xl border-[1.5px] border-white/30 px-6 py-3 font-semibold text-white transition-all hover:bg-white/20"
            >
              <Calendar size={16} /> Agendar cita
            </Link>
          </div>
        </div>
        <div className="relative hidden md:block">
          <div className="absolute -inset-4 rounded-3xl bg-accent opacity-20" />
          <img
            src="https://images.unsplash.com/photo-1638202993928-7267aad84c31?w=700&h=800&fit=crop&auto=format"
            alt="Profesional de salud de Clínica NovaSalud"
            className="relative max-h-[460px] w-full rounded-2xl object-cover shadow-2xl"
          />
          <div className="absolute -bottom-4 -left-4 flex items-center gap-3 rounded-2xl bg-white p-4 shadow-xl">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-secondary">
              <Shield size={18} className="text-accent" />
            </div>
            <div>
              <p className="text-xs text-muted-foreground">Atención</p>
              <p className="text-sm font-bold text-primary">Emergencia 24/7</p>
            </div>
          </div>
          <div className="absolute -top-4 -right-4 flex items-center gap-3 rounded-2xl bg-white p-4 shadow-xl">
            <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-secondary">
              <Star size={18} className="text-[#F2994A]" />
            </div>
            <div>
              <p className="text-xs text-muted-foreground">Calificación</p>
              <p className="text-sm font-bold text-primary">4.9 / 5.0</p>
            </div>
          </div>
        </div>
      </div>
      <svg viewBox="0 0 1440 50" className="-mb-px block w-full fill-white" preserveAspectRatio="none" style={{ height: 50 }}>
        <path d="M0,50 L1440,50 L1440,20 Q1080,0 720,30 Q360,50 0,10 Z" />
      </svg>
    </section>
  );
}

export function HomeTrustBar() {
  return (
    <section id="confianza" className="scroll-mt-20 border-b border-border bg-white">
      <div className="mx-auto max-w-7xl px-6 py-12 md:py-14">
        <div className="grid grid-cols-2 gap-8 md:grid-cols-4 md:gap-10">
          {FEATURES.map(({ icon: Icon, title, desc }) => (
            <div key={title} className="flex items-start gap-4">
              <div className="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-secondary md:h-14 md:w-14">
                <Icon size={22} className="text-accent" />
              </div>
              <div>
                <p className="text-base font-semibold text-primary md:text-lg">{title}</p>
                <p className="mt-1 text-sm text-muted-foreground md:text-base">{desc}</p>
              </div>
            </div>
          ))}
        </div>
      </div>
    </section>
  );
}

export function HomeStats() {
  const { esp, med, srv } = useLiveCounts();
  return (
    <SectionOverlay
      image={HOME_SECTION_IMAGES.stats}
      imageAlt="Equipo médico de Clínica NovaSalud"
    >
      <div className="mx-auto mb-10 max-w-7xl px-6 text-center md:mb-12">
        <p className="mb-2 text-sm font-semibold uppercase tracking-widest text-[#6ECFC8] md:text-base">Disponible hoy</p>
        <h2 className="font-display text-3xl font-bold text-white md:text-5xl">Lo que tenemos para ti hoy</h2>
      </div>
      <div className="mx-auto grid max-w-4xl grid-cols-3 gap-10 px-6 md:gap-12">
        <StatCounter value={esp} label="Especialidades" onDark />
        <StatCounter value={med} label="Médicos" onDark />
        <StatCounter value={srv} label="Servicios" onDark />
      </div>
    </SectionOverlay>
  );
}

const ACTION_BANNERS = [
  {
    image: HOME_SECTION_IMAGES.appointmentBanner,
    imageAlt: 'Reserva tu consulta médica en Clínica NovaSalud',
    title: 'Reserva tu consulta',
    cta: 'Agendar ahora',
    to: '/cita',
  },
  {
    image: HOME_SECTION_IMAGES.paymentBanner,
    imageAlt: 'Pago de servicios médicos en línea',
    title: 'Paga tus servicios',
    cta: 'Ir a pagos',
    to: '/pagar',
  },
] as const;

export function HomeActionCards() {
  return (
    <section id="cita" className="grid md:grid-cols-2">
      {ACTION_BANNERS.map(({ image, imageAlt, title, cta, to }) => (
        <Link
          key={to}
          to={to}
          className="group relative flex min-h-[300px] flex-col justify-end overflow-hidden sm:min-h-[360px] md:min-h-[420px]"
        >
          <img
            src={image}
            alt={imageAlt}
            className="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            loading="lazy"
          />
          <div className="absolute inset-0 bg-gradient-to-t from-black/75 via-black/30 to-black/10" />
          <div className="relative z-10 px-6 py-10 text-center md:px-10 md:py-12">
            <h2 className="font-display mb-5 text-2xl font-bold tracking-wide text-white uppercase md:text-3xl lg:text-4xl">
              {title}
            </h2>
            <span className="inline-flex items-center gap-2 rounded-sm bg-[#F5C518] px-5 py-2.5 text-xs font-bold tracking-wide text-primary uppercase transition-colors group-hover:bg-[#ffd84d] md:text-sm">
              {cta}
              <ArrowRight size={14} />
            </span>
          </div>
        </Link>
      ))}
    </section>
  );
}

function CatalogState({
  state,
  err,
  label,
  onRetry,
  onDark = false,
}: {
  state: string;
  err: string;
  label: string;
  onRetry: () => void;
  onDark?: boolean;
}) {
  if (state === 'loading') {
    return <p className={`text-center text-sm ${onDark ? 'text-white/80' : 'text-muted-foreground'}`}>Cargando {label}…</p>;
  }
  if (state === 'empty') {
    return (
      <p className="rounded-2xl border border-border bg-white p-6 text-center text-sm text-muted-foreground">
        No hay {label} publicados por el momento. Vuelve a consultar más tarde o{' '}
        <Link to="/contacto" className="font-semibold text-accent hover:underline">
          contáctanos
        </Link>
        .
      </p>
    );
  }
  if (state === 'err') {
    return (
      <div className="rounded-2xl border border-border bg-white p-6 text-center">
        <p className="mb-2 font-semibold text-primary">No se pudo cargar {label}</p>
        <p className="mb-4 text-sm text-muted-foreground">{err}</p>
        <button type="button" className="btn btn--primary" onClick={onRetry}>
          Reintentar
        </button>
      </div>
    );
  }
  return null;
}

export function HomeSpecialties() {
  const [state, setState] = useState<'loading' | 'ok' | 'empty' | 'err'>('loading');
  const [list, setList] = useState<EspecialidadRow[]>([]);
  const [err, setErr] = useState('');
  const [active, setActive] = useState<number | null>(null);

  const load = useCallback(async () => {
    setState('loading');
    setErr('');
    try {
      const data = await fetchEspecialidades();
      if (!Array.isArray(data) || data.length === 0) {
        setState('empty');
        return;
      }
      setList(data.slice(0, 6));
      setState('ok');
    } catch (e) {
      setErr(e instanceof Error ? e.message : 'Error');
      setState('err');
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  return (
    <SectionOverlay
      id="especialidades"
      image={HOME_SECTION_IMAGES.specialtiesBanner}
      imageAlt="Catálogo de especialidades médicas"
      minHeight="md"
    >
      <div className="mx-auto max-w-7xl px-6">
        <div className="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
          <div>
            <p className="mb-2 text-xs font-semibold uppercase tracking-widest text-[#6ECFC8] md:text-sm">Catálogo clínico</p>
            <h2 className="font-display text-2xl font-bold text-white md:text-4xl">Especialidades</h2>
            <p className="mt-2 text-sm text-white/75 md:text-base">Atención por especialidad con profesionales de nuestra clínica</p>
          </div>
          <Link to="/especialidades" className="inline-flex items-center gap-2 text-sm font-semibold text-[#6ECFC8]">
            Ver todas <ArrowRight size={14} />
          </Link>
        </div>
        <CatalogState state={state} err={err} label="especialidades" onRetry={() => void load()} onDark />
        {state === 'ok' && (
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            {list.map((e) => {
              const selected = active === e.id;
              return (
                <div
                  key={e.id}
                  role="button"
                  tabIndex={0}
                  onClick={() => setActive(selected ? null : e.id)}
                  onKeyDown={(ev) => ev.key === 'Enter' && setActive(selected ? null : e.id)}
                  className={`group cursor-pointer overflow-hidden rounded-2xl border border-border bg-white transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md ${selected ? 'ring-2 ring-accent' : ''}`}
                >
                  <div className="h-40 overflow-hidden">
                    <img
                      src={especialidadCardImage(e)}
                      alt={e.nombre}
                      className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
                      loading="lazy"
                    />
                  </div>
                  <div className="p-5">
                    <h3 className="mb-1 text-base font-bold text-primary">{e.nombre}</h3>
                    <p className="mb-4 text-xs leading-relaxed text-muted-foreground">
                      Consultas y procedimientos bajo cita para especialista.
                    </p>
                    <div className="flex items-center justify-between">
                      <Link to="/cita" className="inline-flex items-center gap-1 text-xs font-semibold text-accent hover:underline" onClick={(ev) => ev.stopPropagation()}>
                        Consultar <ChevronRight size={11} />
                      </Link>
                      <CheckCircle size={14} className={selected ? 'text-accent opacity-100' : 'opacity-0'} />
                    </div>
                  </div>
                </div>
              );
            })}
          </div>
        )}
      </div>
    </SectionOverlay>
  );
}

export function HomeServices() {
  const [state, setState] = useState<'loading' | 'ok' | 'empty' | 'err'>('loading');
  const [list, setList] = useState<ServicioRow[]>([]);
  const [err, setErr] = useState('');

  const load = useCallback(async () => {
    setState('loading');
    setErr('');
    try {
      const data = await fetchServicios();
      if (!Array.isArray(data) || data.length === 0) {
        setState('empty');
        return;
      }
      setList(data);
      setState('ok');
    } catch (e) {
      setErr(e instanceof Error ? e.message : 'Error');
      setState('err');
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  return (
    <section id="servicios" className="bg-white py-16">
      <div className="mx-auto max-w-7xl px-6">
        <p className="mb-2 text-xs font-semibold uppercase tracking-widest text-accent">TARIFAS</p>
        <h2 className="font-display mb-2 text-2xl font-bold text-primary md:text-3xl">Servicios y precios</h2>
        <p className="mb-8 text-sm text-muted-foreground">Tarifas referenciales. Confirma en recepción o al agendar tu cita.</p>
        <CatalogState state={state} err={err} label="servicios" onRetry={() => void load()} />
        {state === 'ok' && (
          <div className="overflow-hidden rounded-2xl border border-border">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-border bg-secondary">
                  <th className="px-6 py-4 text-left text-xs font-semibold uppercase tracking-widest text-primary">Servicio</th>
                  <th className="hidden px-6 py-4 text-left text-xs font-semibold uppercase tracking-widest text-primary md:table-cell">Profesional</th>
                  <th className="px-6 py-4 text-right text-xs font-semibold uppercase tracking-widest text-primary">Precio</th>
                  <th className="hidden px-6 py-4 md:table-cell" />
                </tr>
              </thead>
              <tbody>
                {list.map((s) => (
                  <tr key={s.id} className="border-b border-border last:border-0 transition-colors hover:bg-secondary/50">
                    <td className="px-6 py-4 font-medium text-primary">{s.nombre}</td>
                    <td className="hidden px-6 py-4 text-muted-foreground md:table-cell">{s.medico?.nombre ?? '—'}</td>
                    <td className="px-6 py-4 text-right font-bold text-accent">{formatMoney(s.precio)}</td>
                    <td className="hidden px-6 py-4 text-right md:table-cell">
                      <Link to={`/pagar/${s.id}`} className="rounded-lg px-3 py-1.5 text-xs font-semibold text-accent transition-colors hover:bg-accent/10">
                        Pagar
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}
      </div>
    </section>
  );
}

export function HomeTeam() {
  const [state, setState] = useState<'loading' | 'ok' | 'empty' | 'err'>('loading');
  const [list, setList] = useState<MedicoRow[]>([]);
  const [err, setErr] = useState('');

  const load = useCallback(async () => {
    setState('loading');
    setErr('');
    try {
      const data = await fetchMedicos();
      if (!Array.isArray(data) || data.length === 0) {
        setState('empty');
        return;
      }
      setList(data.slice(0, 8));
      setState('ok');
    } catch (e) {
      setErr(e instanceof Error ? e.message : 'Error');
      setState('err');
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  return (
    <section id="equipo" className="bg-muted py-16">
      <div className="mx-auto max-w-7xl px-6">
        <div className="mb-8 flex flex-col justify-between gap-4 md:flex-row md:items-end">
          <div>
            <p className="mb-2 text-xs font-semibold uppercase tracking-widest text-accent">PROFESIONALES</p>
            <h2 className="font-display text-2xl font-bold text-primary md:text-3xl">Equipo médico</h2>
          </div>
          <Link to="/equipo" className="inline-flex items-center gap-2 text-sm font-semibold text-accent">
            Ver todos <ArrowRight size={14} />
          </Link>
        </div>
        <CatalogState state={state} err={err} label="médicos" onRetry={() => void load()} />
        {state === 'ok' && (
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {list.map((m) => (
              <article key={m.id} className="rounded-2xl border border-border bg-white p-5 text-center transition-shadow hover:shadow-md">
                {isHttpUrl(m.foto) ? (
                  <img src={m.foto!} alt="" className="mx-auto mb-3 h-16 w-16 rounded-full object-cover" loading="lazy" />
                ) : (
                  <div className="mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-secondary text-lg font-bold text-primary">
                    {initials(m.nombre)}
                  </div>
                )}
                <h3 className="text-sm font-bold text-primary">{m.nombre}</h3>
                <p className="mt-1 text-xs text-muted-foreground">{m.especialidad?.nombre ?? '—'}</p>
              </article>
            ))}
          </div>
        )}
      </div>
    </section>
  );
}

export function HomeArticles() {
  return (
    <SectionOverlay
      image={HOME_SECTION_IMAGES.articlesBanner}
      imageAlt="Artículos clínicos y salud"
      minHeight="md"
    >
      <div className="mx-auto max-w-7xl px-6">
        <p className="mb-2 text-xs font-semibold uppercase tracking-widest text-[#6ECFC8] md:text-sm">Salud y bienestar</p>
        <h2 className="font-display mb-8 text-2xl font-bold text-white md:text-4xl">Artículos para ti</h2>
        <div className="grid gap-6 md:grid-cols-3">
          {ARTICLES.map(({ tag, title, desc, image, to }) => (
            <Link key={title} to={to} className="group overflow-hidden rounded-2xl border border-border bg-white transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md">
              <div className="h-48 overflow-hidden">
                <img src={image} alt="" className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy" />
              </div>
              <div className="p-5">
                <span className="mb-3 inline-block rounded-full bg-secondary px-2.5 py-1 text-[10px] font-semibold uppercase tracking-widest text-accent">{tag}</span>
                <h3 className="mb-1.5 text-base leading-snug font-bold text-primary">{title}</h3>
                <p className="mb-4 text-xs leading-relaxed text-muted-foreground">{desc}</p>
                <span className="inline-flex items-center gap-1 text-xs font-semibold text-accent">
                  Leer más <ArrowRight size={11} />
                </span>
              </div>
            </Link>
          ))}
        </div>
      </div>
    </SectionOverlay>
  );
}

export function HomeCta() {
  return (
    <section className="relative overflow-hidden bg-primary py-20">
      <div className="absolute inset-0 opacity-10" style={{ backgroundImage: 'radial-gradient(circle at 30% 50%, #2FA89E 0%, transparent 60%)' }} />
      <div className="relative z-10 mx-auto max-w-4xl px-6 text-center">
        <h2 className="font-display mb-4 text-3xl font-bold text-white md:text-4xl">¿Listo para tu próxima atención?</h2>
        <p className="mb-8 text-base text-white/70">Agenda en minutos o paga tu servicio con tarjeta de forma segura.</p>
        <div className="flex flex-wrap items-center justify-center gap-4">
          <Link to="/cita" className="inline-flex items-center gap-2 rounded-xl bg-accent px-7 py-3.5 font-semibold text-white shadow-lg transition-all hover:opacity-90">
            <Calendar size={16} /> Agendar cita
          </Link>
          <Link
            to="/pagar"
            className="inline-flex items-center gap-2 rounded-xl border-[1.5px] border-white/35 px-7 py-3.5 font-semibold text-white transition-all hover:bg-white/20"
          >
            <CreditCard size={16} /> Pagar en línea
          </Link>
        </div>
      </div>
    </section>
  );
}
