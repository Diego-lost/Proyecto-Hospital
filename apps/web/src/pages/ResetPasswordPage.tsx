import { FormEvent, useMemo, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { followAuthRedirect, resetPassword } from '../lib/authApi';
import { useAuth } from '../contexts/AuthContext';

export default function ResetPasswordPage() {
  const [params] = useSearchParams();
  const { refresh } = useAuth();
  const token = useMemo(() => params.get('token') ?? '', [params]);
  const emailFromLink = useMemo(() => params.get('email') ?? '', [params]);

  const [email, setEmail] = useState(emailFromLink);
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setError('');
    setBusy(true);

    try {
      const result = await resetPassword({
        token,
        email,
        password,
        password_confirmation: passwordConfirmation,
      });
      await refresh();
      followAuthRedirect(result.redirect_url);
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo restablecer la contraseña.');
    } finally {
      setBusy(false);
    }
  }

  if (!token) {
    return (
      <main id="contenido" className="mx-auto max-w-md px-6 py-16">
        <div className="rounded-2xl border border-border bg-white p-8 shadow-sm">
          <h1 className="font-display text-2xl font-bold text-primary">Enlace inválido</h1>
          <p className="mt-2 text-sm text-muted-foreground">
            El enlace de recuperación no es válido o ha caducado. Solicita uno nuevo.
          </p>
          <Link to="/recuperar-contrasena" className="mt-6 inline-block font-semibold text-primary hover:underline">
            Solicitar nuevo enlace
          </Link>
        </div>
      </main>
    );
  }

  return (
    <main id="contenido" className="mx-auto max-w-md px-6 py-16">
      <div className="rounded-2xl border border-border bg-white p-8 shadow-sm">
        <h1 className="font-display text-2xl font-bold text-primary">Nueva contraseña</h1>
        <p className="mt-2 text-sm text-muted-foreground">Elige una contraseña segura para tu cuenta.</p>

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

          <div>
            <label htmlFor="password" className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-muted-foreground">
              Nueva contraseña
            </label>
            <input
              id="password"
              type="password"
              required
              minLength={8}
              autoComplete="new-password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full rounded-lg border border-border px-3 py-2.5 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20"
            />
          </div>

          <div>
            <label htmlFor="password_confirmation" className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-muted-foreground">
              Confirmar contraseña
            </label>
            <input
              id="password_confirmation"
              type="password"
              required
              minLength={8}
              autoComplete="new-password"
              value={passwordConfirmation}
              onChange={(e) => setPasswordConfirmation(e.target.value)}
              className="w-full rounded-lg border border-border px-3 py-2.5 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20"
            />
          </div>

          <button
            type="submit"
            disabled={busy}
            className="w-full rounded-lg bg-accent py-2.5 text-sm font-semibold text-white transition-opacity hover:opacity-90 disabled:opacity-60"
          >
            {busy ? 'Guardando…' : 'Guardar contraseña'}
          </button>
        </form>
      </div>
    </main>
  );
}
