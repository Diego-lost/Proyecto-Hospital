import { FormEvent, useState } from 'react';
import { Link, useNavigate } from 'react-router-dom';
import { CheckCircle2, Mail } from 'lucide-react';
import { useAuth } from '../contexts/AuthContext';
import { followAuthRedirect, isRegisterPending, register, resendVerification } from '../lib/authApi';

export default function RegisterPage() {
  const navigate = useNavigate();
  const { refresh, user } = useAuth();
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [role, setRole] = useState<'user' | 'admin'>('user');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);
  const [pendingEmail, setPendingEmail] = useState('');
  const [redirectUrl, setRedirectUrl] = useState('/');
  const [resendBusy, setResendBusy] = useState(false);
  const [resendMessage, setResendMessage] = useState('');

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    setError('');
    setResendMessage('');
    setBusy(true);

    try {
      const result = await register({
        name,
        email,
        password,
        password_confirmation: passwordConfirmation,
        role,
      });

      if (isRegisterPending(result)) {
        await refresh();
        setPendingEmail(result.email ?? email);
        setRedirectUrl(result.redirect_url ?? '/');
        return;
      }
    } catch (err) {
      setError(err instanceof Error ? err.message : 'No se pudo completar el registro.');
    } finally {
      setBusy(false);
    }
  }

  async function onResend() {
    if (!pendingEmail) return;
    setResendBusy(true);
    setResendMessage('');
    try {
      const result = await resendVerification(pendingEmail);
      setResendMessage(result.message);
    } catch (err) {
      setResendMessage(err instanceof Error ? err.message : 'No se pudo reenviar la notificación.');
    } finally {
      setResendBusy(false);
    }
  }

  function goToPlatform() {
    if (user?.role === 'admin' && redirectUrl.includes('/admin')) {
      followAuthRedirect(redirectUrl);
      return;
    }
    navigate('/');
  }

  if (pendingEmail) {
    return (
      <main id="contenido" className="mx-auto max-w-md px-6 py-16">
        <div className="rounded-2xl border border-border bg-white p-8 text-center shadow-sm">
          <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
            <CheckCircle2 size={28} />
          </div>
          <h1 className="font-display text-2xl font-bold text-primary">¡Cuenta creada!</h1>
          <p className="mt-3 text-sm leading-relaxed text-muted-foreground">
            Enviamos un correo de confirmación a{' '}
            <strong className="text-foreground">{pendingEmail}</strong>. Revisa tu bandeja y haz clic en el
            enlace cuando puedas.
          </p>
          <p className="mt-3 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-900">
            <strong>Ya puedes volver a la plataforma.</strong> Tu sesión quedó iniciada
            {user?.name ? (
              <>
                {' '}
                como <strong>{user.name}</strong>
              </>
            ) : null}
            .
          </p>
          {resendMessage && (
            <p className="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">
              {resendMessage}
            </p>
          )}
          <button
            type="button"
            onClick={goToPlatform}
            className="mt-6 w-full rounded-lg bg-accent py-2.5 text-sm font-semibold text-white transition-opacity hover:opacity-90"
          >
            {user?.role === 'admin' ? 'Ir al panel de gestión' : 'Ir al inicio'}
          </button>
          <button
            type="button"
            onClick={() => void onResend()}
            disabled={resendBusy}
            className="mt-3 flex w-full items-center justify-center gap-2 rounded-lg border border-border py-2.5 text-sm font-semibold text-primary hover:bg-secondary disabled:opacity-60"
          >
            <Mail size={16} aria-hidden="true" />
            {resendBusy ? 'Reenviando…' : 'Reenviar correo de confirmación'}
          </button>
        </div>
      </main>
    );
  }

  return (
    <main id="contenido" className="mx-auto max-w-md px-6 py-16">
      <div className="rounded-2xl border border-border bg-white p-8 shadow-sm">
        <h1 className="font-display text-2xl font-bold text-primary">Registrarse</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          Te enviaremos un correo de confirmación y podrás usar la plataforma de inmediato.
        </p>

        {error && (
          <div className="mt-4 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700" role="alert">
            {error}
          </div>
        )}

        <form className="mt-6 space-y-4" onSubmit={onSubmit}>
          <div>
            <label htmlFor="name" className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-muted-foreground">
              Nombre completo
            </label>
            <input
              id="name"
              type="text"
              required
              autoComplete="name"
              value={name}
              onChange={(e) => setName(e.target.value)}
              className="w-full rounded-lg border border-border px-3 py-2.5 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20"
            />
          </div>

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
            <label htmlFor="role" className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-muted-foreground">
              Tipo de cuenta
            </label>
            <select
              id="role"
              value={role}
              onChange={(e) => setRole(e.target.value as 'user' | 'admin')}
              className="w-full rounded-lg border border-border px-3 py-2.5 text-sm outline-none focus:border-accent focus:ring-2 focus:ring-accent/20"
            >
              <option value="user">Paciente (sitio web)</option>
              <option value="admin">Administrador (panel)</option>
            </select>
          </div>

          <div>
            <label htmlFor="password" className="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-muted-foreground">
              Contraseña
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
            {busy ? 'Creando cuenta…' : 'Registrarse'}
          </button>
        </form>

        <p className="mt-6 text-center text-sm text-muted-foreground">
          ¿Ya tienes cuenta?{' '}
          <Link to="/login" className="font-semibold text-primary hover:underline">
            Iniciar sesión
          </Link>
        </p>
      </div>
    </main>
  );
}
