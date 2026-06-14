import { useEffect, useState } from 'react';
import { Link, Outlet } from 'react-router-dom';
import { Activity, Clock, Mail, MapPin, Menu, Phone, X } from 'lucide-react';
import { portalTransparenciaHref } from '../config/hospitalNav';
import { useAuth } from '../contexts/AuthContext';
import { PortalNavDesktop, PortalNavDrawer } from './PortalNav';
import AiChatWidget from './AiChatWidget';

export default function SiteLayout() {
  const [menuOpen, setMenuOpen] = useState(false);
  const [scrolled, setScrolled] = useState(false);
  const { user, loading, logout } = useAuth();

  useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 40);
    window.addEventListener('scroll', onScroll);
    return () => window.removeEventListener('scroll', onScroll);
  }, []);

  useEffect(() => {
    document.body.style.overflow = menuOpen ? 'hidden' : '';
    return () => {
      document.body.style.overflow = '';
    };
  }, [menuOpen]);

  const closeMenu = () => setMenuOpen(false);

  return (
    <div className="min-h-screen bg-background">
      <a className="skip-link" href="#contenido">
        Saltar al contenido
      </a>

      <div className="hidden items-center justify-between border-b border-border bg-secondary px-8 py-2 text-xs text-muted-foreground md:flex">
        <div className="flex items-center gap-6">
          <span className="flex items-center gap-1.5">
            <MapPin size={12} /> Av. Principal 123, Lima
          </span>
          <a href="tel:+51011234567" className="flex items-center gap-1.5 transition-colors hover:text-foreground">
            <Phone size={12} /> (01) 123-4567
          </a>
          <a href="mailto:contacto@novasalud.pe" className="flex items-center gap-1.5 transition-colors hover:text-foreground">
            <Mail size={12} /> contacto@novasalud.pe
          </a>
        </div>
        <div className="flex items-center gap-3">
          {!loading && !user && (
            <>
              <Link to="/login" className="transition-colors hover:text-foreground">
                Iniciar sesión
              </Link>
              <span className="text-border">|</span>
              <Link to="/registro" className="font-medium transition-colors hover:text-foreground">
                Registrarse
              </Link>
              <span className="text-border">|</span>
            </>
          )}
          {!loading && user && (
            <>
              <span className="max-w-[160px] truncate" title={user.email}>
                {user.name}
              </span>
              <button
                type="button"
                onClick={() => void logout()}
                className="transition-colors hover:text-foreground"
              >
                Cerrar sesión
              </button>
              <span className="text-border">|</span>
            </>
          )}
          <Link to="/pagar" className="transition-colors hover:text-foreground">
            Pago en línea
          </Link>
          <span className="text-border">|</span>
          <Link to="/cita" className="font-medium transition-colors hover:text-foreground">
            Agendar cita
          </Link>
        </div>
      </div>

      <header
        className={`sticky top-0 z-50 border-b border-border transition-all duration-300 ${
          scrolled ? 'bg-white/95 shadow-sm backdrop-blur-sm' : 'bg-white'
        }`}
      >
        <div className="mx-auto flex h-16 max-w-7xl items-center gap-4 px-6">
          <Link to="/" className="flex shrink-0 items-center gap-2.5" aria-label="Inicio">
            <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-primary">
              <Activity size={16} color="white" />
            </div>
            <div className="leading-tight">
              <span className="font-display text-sm font-bold tracking-tight text-primary">Clínica</span>
              <span className="block text-xs font-semibold text-accent">NovaSalud</span>
            </div>
          </Link>

          <div className="hidden min-w-0 flex-1 justify-center xl:flex">
            <PortalNavDesktop />
          </div>

          <div className="ml-auto hidden shrink-0 xl:block">
            <Link
              to="/cita"
              className="rounded-lg bg-accent px-4 py-2 text-sm font-medium text-white shadow-sm transition-all hover:opacity-90"
            >
              Agendar cita
            </Link>
          </div>

          <button
            type="button"
            className="ml-auto rounded-md p-2 transition-colors hover:bg-secondary xl:hidden"
            aria-label={menuOpen ? 'Cerrar menú' : 'Abrir menú'}
            aria-expanded={menuOpen}
            onClick={() => setMenuOpen((v) => !v)}
          >
            {menuOpen ? <X size={20} /> : <Menu size={20} />}
          </button>
        </div>

        {menuOpen && (
          <div className="border-t border-border bg-white px-6 pb-4 xl:hidden">
            <PortalNavDrawer onNavigate={closeMenu} />
            <div className="flex flex-col gap-3 pt-4">
              {!loading && !user && (
                <div className="flex gap-3">
                  <Link to="/login" className="flex-1 rounded-lg border border-border py-2.5 text-center text-sm font-medium text-primary" onClick={closeMenu}>
                    Iniciar sesión
                  </Link>
                  <Link to="/registro" className="flex-1 rounded-lg border border-accent/30 py-2.5 text-center text-sm font-medium text-accent" onClick={closeMenu}>
                    Registrarse
                  </Link>
                </div>
              )}
              {!loading && user && (
                <button
                  type="button"
                  onClick={() => {
                    closeMenu();
                    void logout();
                  }}
                  className="w-full rounded-lg border border-border py-2.5 text-center text-sm font-medium text-primary"
                >
                  Cerrar sesión ({user.name})
                </button>
              )}
              <div className="flex gap-3">
              <Link to="/pagar" className="flex-1 rounded-lg border border-border py-2.5 text-center text-sm font-medium text-primary" onClick={closeMenu}>
                Pago en línea
              </Link>
              <Link to="/cita" className="flex-1 rounded-lg bg-accent py-2.5 text-center text-sm font-medium text-white" onClick={closeMenu}>
                Agendar cita
              </Link>
              </div>
            </div>
          </div>
        )}
      </header>

      <Outlet />

      <footer className="border-t border-border py-12" style={{ background: '#06253A' }}>
        <div className="mx-auto max-w-7xl px-6">
          <div className="mb-10 grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            <div>
              <div className="mb-4 flex items-center gap-2.5">
                <div className="flex h-8 w-8 items-center justify-center rounded-lg bg-accent">
                  <Activity size={16} color="white" />
                </div>
                <span className="font-display font-bold text-white">Clínica NovaSalud</span>
              </div>
              <p className="text-xs leading-relaxed text-white/50">
                Cuidamos tu salud con profesionalismo, calidez y tecnología de punta.
              </p>
            </div>
            <div>
              <p className="mb-4 text-xs font-semibold uppercase tracking-widest text-accent">Servicios</p>
              {[
                { label: 'Pago en línea', to: '/pagar' },
                { label: 'Especialidades', to: '/especialidades' },
                { label: 'Sedes', to: '/sedes' },
                { label: 'Seguros', to: '/seguros' },
                { label: 'Manual y políticas', to: '/manual-politicas' },
              ].map(({ label, to }) => (
                <Link key={label} to={to} className="mb-2 block text-sm text-white/55 transition-colors hover:text-white">
                  {label}
                </Link>
              ))}
            </div>
            <div>
              <p className="mb-4 text-xs font-semibold uppercase tracking-widest text-accent">Contacto</p>
              <div className="space-y-2.5">
                <a href="tel:+51011234567" className="flex items-center gap-2 text-sm text-white/55 hover:text-white">
                  <Phone size={13} className="text-accent" /> (01) 123-4567
                </a>
                <a href="mailto:contacto@novasalud.pe" className="flex items-center gap-2 text-sm text-white/55 hover:text-white">
                  <Mail size={13} className="text-accent" /> contacto@novasalud.pe
                </a>
                <p className="flex items-start gap-2 text-sm text-white/55">
                  <MapPin size={13} className="mt-0.5 shrink-0 text-accent" /> Av. Principal 123, Lima
                </p>
              </div>
            </div>
            <div>
              <p className="mb-4 text-xs font-semibold uppercase tracking-widest text-accent">Horario</p>
              <div className="flex items-center gap-2">
                <Clock size={13} className="text-accent" />
                <div>
                  <p className="text-sm font-medium text-white">Lun – Sáb</p>
                  <p className="text-xs text-white/50">8:00 – 20:00</p>
                </div>
              </div>
              <div className="mt-3 flex items-center gap-2">
                <div className="h-2 w-2 animate-pulse rounded-full bg-green-400" />
                <p className="text-sm font-medium text-white/80">Emergencia 24/7</p>
              </div>
            </div>
          </div>
          <div className="flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-8 md:flex-row">
            <p className="text-xs text-white/35">© {new Date().getFullYear()} Clínica NovaSalud. Todos los derechos reservados.</p>
            <div className="flex gap-5">
              {['Privacidad', 'Términos', 'Transparencia'].map((l) => (
                <a key={l} href={portalTransparenciaHref} target="_blank" rel="noopener noreferrer" className="text-xs text-white/35 transition-colors hover:text-white">
                  {l}
                </a>
              ))}
            </div>
          </div>
        </div>
      </footer>
      <AiChatWidget />
    </div>
  );
}
