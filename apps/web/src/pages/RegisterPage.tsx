import { FormEvent, useState } from 'react';
import { Link } from 'react-router-dom';
import { Mail } from 'lucide-react';
import { isRegisterPending, register, resendVerification } from '../lib/authApi';

export default function RegisterPage() {
  const [name, setName] = useState('');
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [passwordConfirmation, setPasswordConfirmation] = useState('');
  const [role, setRole] = useState<'user' | 'admin'>('user');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);
  const [pendingEmail, setPendingEmail] = useState('');
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
        setPendingEmail(result.email ?? email);
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

  if (pendingEmail) {
    return (
      <main id="contenido" className="mx-auto max-w-md px-6 py-16">
        <div className="rounded-2xl border border-border bg-white p-8 text-center shadow-sm">
          <div className="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-accent/10 text-accent">
            <Mail size={28} />
          </div>
          <h1 className="font-display text-2xl font-bold text-primary">Revisa tu correo</h1>
          <p className="mt-3 text-sm leading-relaxed text-muted-foreground">
            Enviamos una notificación a <strong className="text-foreground">{pendingEmail}</strong>.
            Abre el correo y haz clic en <strong>Ingresar a mi cuenta</strong> para entrar.
          </p>
          {resendMessage && (
            <p className="mt-4 rounded-lg border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-800">{resendMessage}</p>
          )}
          <button
            type="button"
            onClick={() => void onResend()}
            disabled={resendBusy}
            className="mt-6 w-full rounded-lg border border-border py-2.5 text-sm font-semibold text-primary hover:bg-secondary disabled:opacity-60"
          >
            {resendBusy ? 'Reenviando…' : 'Reenviar notificación'}
          </button>
          <p className="mt-6 text-sm text-muted-foreground">
            <Link to="/login" className="font-semibold text-primary hover:underline">
              Ir a iniciar sesión
            </Link>
          </p>
        </div>
      </main>
    );
  }

  return (
    <main id="contenido" className="mx-auto max-w-md px-6 py-16">
      <div className="rounded-2xl border border-border bg-white p-8 shadow-sm">
        <h1 className="font-display text-2xl font-bold text-primary">Registrarse</h1>
        <p className="mt-2 text-sm text-muted-foreground">
          Pon tu correo (@gmail.com, @continental.edu.pe, etc.). Te llegará una notificación para ingresar.
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
            {busy ? 'Enviando notificación…' : 'Registrarse'}
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
