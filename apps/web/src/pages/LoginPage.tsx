import { FormEvent, useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import { followAuthRedirect, login, resendVerification } from '../lib/authApi';
import { useAuth } from '../contexts/AuthContext';

export default function LoginPage() {
  const { refresh } = useAuth();
  const [params] = useSearchParams();
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [busy, setBusy] = useState(false);
  const [resendBusy, setResendBusy] = useState(false);
  const [showResend, setShowResend] = useState(false);

  useEffect(() => {
    if (params.get('verificado') === '1') {
      setSuccess('Correo confirmado. Ya puedes iniciar sesión.');
    }
  }, [params]);

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setError('');
    setSuccess('');
    setShowResend(false);
    setBusy(true);

    try {
      const result = await login({ email, password, remember });
      await refresh();
      followAuthRedirect(result.redirect_url);
    } catch (err) {
      const message = err instanceof Error ? err.message : 'No se pudo iniciar sesión.';
      setError(message);
      if (message.toLowerCase().includes('correo') || message.toLowerCase().includes('notificación')) {
        setShowResend(true);
      }
    } finally {
      setBusy(false);
    }
  }

  async function onResend() {
    if (!email) return;
    setResendBusy(true);
    try {
      const result = await resendVerification(email);
      setSuccess(result.message);
      setError('');
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo reenviar la notificación.');
    } finally {
      setResendBusy(false);
    }
  }

  return (
    <main id="contenido" className="mx-auto max-w-md px-6 py-16">
      <div className="rounded-2xl border border-border bg-white p-8 shadow-sm">
        <h1 className="font-display text-2xl font-bold text-primary">Iniciar sesión</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          Pacientes acceden al sitio web; administradores al panel de gestión.
        </p>

        {success && (
          <div className="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800" role="status">
            {success}
          </div>
        )}
        {error && (
          <div className="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
            {error}
            {showResend && (
              <button
                type="button"
                onClick={() => void onResend()}
                disabled={resendBusy}
                className="mt-2 block font-semibold text-primary underline disabled:opacity-60"
              >
                {resendBusy ? 'Reenviando…' : 'Reenviar notificación al correo'}
              </button>
            )}
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
            <div className="mb-1.5 flex items-center justify-between">
              <label htmlFor="password" className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                Contraseña
              </label>
              <Link to="/recuperar-contrasena" className="text-xs font-medium text-primary hover:underline">
                ¿Olvidaste tu contraseña?
              </Link>
            </div>
            <input
              id="password"
              type="password"
              required
              autoComplete="current-password"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              className="w-full rounded-lg border border-border px-3 py-2.5 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20"
            />
          </div>

          <label className="flex items-center gap-2 text-sm text-muted-foreground">
            <input
              type="checkbox"
              checked={remember}
              onChange={(e) => setRemember(e.target.checked)}
              className="rounded border-border"
            />
            Recordarme en este equipo
          </label>

          <button
            type="submit"
            disabled={busy}
            className="w-full rounded-lg bg-primary py-2.5 text-sm font-semibold text-white transition-opacity hover:opacity-90 disabled:opacity-60"
          >
            {busy ? 'Entrando…' : 'Entrar'}
          </button>
        </form>

        <p className="mt-6 text-center text-sm text-muted-foreground">
          ¿No tienes cuenta?{' '}
          <Link to="/registro" className="font-semibold text-primary hover:underline">
            Registrarse
          </Link>
        </p>
      </div>
    </main>
  );
}
