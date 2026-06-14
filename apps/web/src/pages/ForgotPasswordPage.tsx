import { FormEvent, useState } from 'react';
import { Link } from 'react-router-dom';
import { Mail } from 'lucide-react';
import { requestPasswordReset } from '../lib/authApi';

export default function ForgotPasswordPage() {
  const [email, setEmail] = useState('');
  const [sentTo, setSentTo] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setError('');
    setBusy(true);

    try {
      await requestPasswordReset(email);
      setSentTo(email);
      setEmail('');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo enviar el correo.');
    } finally {
      setBusy(false);
    }
  }

  if (sentTo) {
    return (
      <main id="contenido" className="mx-auto max-w-md px-6 py-16">
        <div className="rounded-2xl border border-border bg-white p-8 text-center shadow-sm">
          <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-accent/10 text-accent">
            <Mail size={28} />
          </div>
          <h1 className="font-display text-2xl font-bold text-primary">Esperando confirmación</h1>
          <p className="mt-3 text-sm leading-relaxed text-muted-foreground">
            Si <strong className="text-foreground">{sentTo}</strong> está registrado, te enviamos un enlace.
            Revisa tu bandeja de entrada y spam, y haz clic para restablecer tu contraseña.
          </p>
          <Link
            to="/login"
            className="mt-6 inline-block w-full rounded-lg bg-primary py-2.5 text-sm font-semibold text-white hover:opacity-90"
          >
            Volver a iniciar sesión
          </Link>
        </div>
      </main>
    );
  }

  return (
    <main id="contenido" className="mx-auto max-w-md px-6 py-16">
      <div className="rounded-2xl border border-border bg-white p-8 shadow-sm">
        <h1 className="font-display text-2xl font-bold text-primary">Recuperar contraseña</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          Te enviaremos un enlace a tu correo si está registrado en el sistema.
        </p>

        {error && (
          <div className="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
            {error}
          </div>
        )}

        <form className="mt-6 space-y-4" onSubmit={onSubmit}>
          <div>
            <label htmlFor="email" className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-muted-foreground">
              Correo electrónico
            </label>
            <input
              id="email"
              type="email"
              required
              autoComplete="email"
              value={email}
              onChange={(e) => setEmail(e.target.value)}
              className="w-full rounded-lg border border-border px-3 py-2.5 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20"
            />
          </div>

          <button
            type="submit"
            disabled={busy}
            className="w-full rounded-lg bg-primary py-2.5 text-sm font-semibold text-white transition-opacity hover:opacity-90 disabled:opacity-60"
          >
            {busy ? 'Enviando…' : 'Enviar enlace'}
          </button>
        </form>

        <p className="mt-6 text-center text-sm text-muted-foreground">
          <Link to="/login" className="font-semibold text-primary hover:underline">
            Volver a iniciar sesión
          </Link>
        </p>
      </div>
    </main>
  );
}
