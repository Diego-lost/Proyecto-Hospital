import { apiBase, apiJson } from '../api';

export type AuthUser = {
  id: number;
  name: string;
  email: string;
  role: 'user' | 'admin';
};

type AuthResponse = {
  user: AuthUser;
  redirect_url: string;
  token?: string;
};

type MeResponse = {
  user: AuthUser | null;
};

type CsrfResponse = {
  token: string;
};

const SPA_TOKEN_KEY = 'novasalud_spa_token';

let csrfToken: string | null = null;

function isCrossOriginApi(): boolean {
  if (typeof window === 'undefined') {
    return false;
  }
  try {
    return new URL(`${apiBase()}/`).origin !== window.location.origin;
  } catch {
    return false;
  }
}

function readSpaToken(): string | null {
  try {
    return localStorage.getItem(SPA_TOKEN_KEY) ?? sessionStorage.getItem(SPA_TOKEN_KEY);
  } catch {
    return null;
  }
}

function writeSpaToken(token: string | null): void {
  try {
    if (token) {
      localStorage.setItem(SPA_TOKEN_KEY, token);
      sessionStorage.setItem(SPA_TOKEN_KEY, token);
    } else {
      localStorage.removeItem(SPA_TOKEN_KEY);
      sessionStorage.removeItem(SPA_TOKEN_KEY);
    }
  } catch {
    // ignore
  }
}

/** Toma el token que envía el panel admin al abrir «Ver sitio web». */
export function consumeSpaTokenFromUrl(): boolean {
  if (typeof window === 'undefined') {
    return false;
  }

  const params = new URLSearchParams(window.location.search);
  const token = params.get('spa_token');
  if (!token) {
    return false;
  }

  writeSpaToken(token);
  params.delete('spa_token');
  const query = params.toString();
  const next = window.location.pathname + (query ? `?${query}` : '') + window.location.hash;
  window.history.replaceState({}, '', next);

  return true;
}

async function ensureCsrfToken(): Promise<string> {
  if (csrfToken) {
    return csrfToken;
  }

  const base = apiBase();
  const res = await fetch(`${base}/auth/csrf`, {
    credentials: 'include',
    headers: { Accept: 'application/json' },
  });

  if (!res.ok) {
    throw new Error('No se pudo obtener el token de seguridad.');
  }

  const data = (await res.json()) as CsrfResponse;
  csrfToken = data.token;
  return csrfToken;
}

async function sessionAuthJson<T>(path: string, init?: RequestInit): Promise<T> {
  const token = await ensureCsrfToken();
  const base = apiBase();
  const method = String(init?.method ?? 'GET').toUpperCase();

  const headers = new Headers(init?.headers);
  headers.set('Accept', 'application/json');
  headers.set('X-CSRF-TOKEN', token);
  if (method !== 'GET' && method !== 'HEAD' && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }

  const res = await fetch(`${base}${path}`, {
    ...init,
    credentials: 'include',
    headers,
  });

  return parseAuthResponse<T>(res, path);
}

async function spaAuthJson<T>(path: string, init?: RequestInit): Promise<T> {
  const method = String(init?.method ?? 'GET').toUpperCase();
  const headers = new Headers(init?.headers);
  headers.set('Accept', 'application/json');
  if (method !== 'GET' && method !== 'HEAD' && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }

  const bearer = readSpaToken();
  if (bearer) {
    headers.set('Authorization', `Bearer ${bearer}`);
  }

  const result = await apiJson<T>(`/api/auth${path}`, {
    ...init,
    headers,
  });

  return result;
}

async function authJson<T>(path: string, init?: RequestInit): Promise<T> {
  if (isCrossOriginApi()) {
    return spaAuthJson<T>(path, init);
  }

  return sessionAuthJson<T>(path, init);
}

async function parseAuthResponse<T>(res: Response, path: string): Promise<T> {
  const bodyText = await res.text();
  let parsed: unknown = null;
  if (bodyText.trim() !== '') {
    try {
      parsed = JSON.parse(bodyText) as unknown;
    } catch {
      throw new Error(`Respuesta inválida (${res.status}) desde ${path}`);
    }
  }

  if (!res.ok) {
    throw new Error(formatAuthError(parsed, res.status));
  }

  return parsed as T;
}

export async function getCurrentUser(): Promise<AuthUser | null> {
  const data = await authJson<MeResponse>('/me');
  return data.user;
}

export async function login(payload: {
  email: string;
  password: string;
  remember?: boolean;
}): Promise<AuthResponse> {
  csrfToken = null;
  const result = await authJson<AuthResponse>('/login', {
    method: 'POST',
    body: JSON.stringify(payload),
  });

  if (result.token) {
    writeSpaToken(result.token);
  }

  return result;
}

export type RegisterPendingResponse = {
  message: string;
  pending_verification: true;
  email: string;
  user?: AuthUser;
  redirect_url?: string;
  token?: string;
};

type RegisterResponse = AuthResponse | RegisterPendingResponse;

export function isRegisterPending(result: RegisterResponse): result is RegisterPendingResponse {
  return 'pending_verification' in result && result.pending_verification === true;
}

export async function register(payload: {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
  role: 'user' | 'admin';
}): Promise<RegisterResponse> {
  csrfToken = null;
  const result = await authJson<RegisterResponse>('/register', {
    method: 'POST',
    body: JSON.stringify(payload),
  });

  if ('token' in result && result.token) {
    writeSpaToken(result.token);
  }

  return result;
}

export async function resendVerification(email: string): Promise<{ message: string }> {
  csrfToken = null;
  return authJson<{ message: string }>('/resend-verification', {
    method: 'POST',
    body: JSON.stringify({ email }),
  });
}

export async function logout(): Promise<{ redirect_url: string }> {
  csrfToken = null;
  const result = await authJson<{ redirect_url: string }>('/logout', {
    method: 'POST',
    body: JSON.stringify({}),
  });
  writeSpaToken(null);
  return result;
}

export async function requestPasswordReset(email: string): Promise<{ message: string }> {
  csrfToken = null;
  return authJson<{ message: string }>('/forgot-password', {
    method: 'POST',
    body: JSON.stringify({ email }),
  });
}

export async function resetPassword(payload: {
  token: string;
  email: string;
  password: string;
  password_confirmation: string;
}): Promise<AuthResponse & { message: string }> {
  csrfToken = null;
  const result = await authJson<AuthResponse & { message: string }>('/reset-password', {
    method: 'POST',
    body: JSON.stringify(payload),
  });

  if (result.token) {
    writeSpaToken(result.token);
  }

  return result;
}

export function followAuthRedirect(redirectUrl: string): void {
  const adminPath = '/admin';
  const spaEnterPath = '/auth/spa-enter';
  if (redirectUrl.includes(adminPath) || redirectUrl.includes(spaEnterPath)) {
    window.location.href = redirectUrl;
    return;
  }

  const url = new URL(redirectUrl, window.location.origin);
  if (url.origin !== window.location.origin) {
    window.location.href = redirectUrl;
    return;
  }

  const hashPath = url.hash || '#/';
  window.location.href = `${window.location.pathname}${hashPath}`;
}

function formatAuthError(parsed: unknown, status: number): string {
  if (typeof parsed === 'object' && parsed !== null) {
    const o = parsed as Record<string, unknown>;
    const errors = o.errors;
    if (typeof errors === 'object' && errors !== null) {
      for (const v of Object.values(errors as Record<string, unknown>)) {
        if (Array.isArray(v)) {
          for (const item of v) {
            if (typeof item === 'string' && item.trim() !== '') {
              return item.trim();
            }
          }
        }
      }
    }
    const message = o.message;
    if (typeof message === 'string' && message.trim() !== '' && message !== 'The given data was invalid.') {
      return message.trim();
    }
  }

  return `HTTP ${status}`;
}
