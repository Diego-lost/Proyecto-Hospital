import { Link } from 'react-router-dom';
import { ChevronRight } from 'lucide-react';

export default function PagoHero() {
  return (
    <div className="pago-hero relative overflow-hidden bg-primary text-white">
      <div
        className="pointer-events-none absolute inset-0 opacity-[0.07]"
        style={{
          backgroundImage:
            'radial-gradient(circle at 20% 50%, #2fa89e 0%, transparent 50%), radial-gradient(circle at 80% 20%, #4fc3b8 0%, transparent 40%)',
        }}
        aria-hidden="true"
      />
      <div className="relative mx-auto max-w-7xl px-4 py-10 md:px-6 md:py-12">
        <nav className="mb-4 flex flex-wrap items-center gap-1 text-sm text-white/70" aria-label="Ruta">
          <Link to="/" className="transition-colors hover:text-white">
            Inicio
          </Link>
          <ChevronRight size={14} className="opacity-60" aria-hidden="true" />
          <Link to="/cita" className="transition-colors hover:text-white">
            Citas
          </Link>
          <ChevronRight size={14} className="opacity-60" aria-hidden="true" />
          <span className="font-medium text-white">Pago</span>
        </nav>
        <h1 className="font-display text-3xl font-bold tracking-tight md:text-4xl lg:text-[2.5rem]">
          Realizar pago
        </h1>
        <p className="mt-3 max-w-2xl text-base leading-relaxed text-white/80">
          Elige cómo deseas pagar tu consulta o procedimiento. Los pagos con tarjeta se procesan de forma segura.
        </p>
      </div>
      <div className="pago-hero__wave" aria-hidden="true">
        <svg viewBox="0 0 1440 56" preserveAspectRatio="none" className="block h-10 w-full md:h-14">
          <path fill="#f4f7f9" d="M0,40 C240,8 480,48 720,28 C960,8 1200,44 1440,24 L1440,56 L0,56 Z" />
        </svg>
      </div>
    </div>
  );
}
