/**
 * Raíz pública de Laravel (…/backend/api/public o el origen con `php artisan serve`), sin `/api` al final.
 * Si en .env quedó `.../public/api`, se normaliza para no duplicar rutas.
 */
export function apiBase(): string {
  let raw = String(import.meta.env.VITE_API_BASE_URL ?? '').trim();
  raw = normalizeApiRootBase(raw);

  const inferred = inferApiBaseFromWindow();
  if (inferred) {
    if (raw === '') {
      return normalizeApiRootBase(inferred);
    }
    if (typeof window !== 'undefined') {
      try {
        const envOrigin = new URL(raw + '/').origin;
        if (envOrigin !== window.location.origin) {
          return normalizeApiRootBase(inferred);
        }
      } catch {
        return normalizeApiRootBase(inferred);
      }
    }
  }

  return raw;
}

function normalizeApiRootBase(s: string): string {
  let x = s.trim();
  x = x.replace(/\/+$/, '');
  x = x.replace(/\/api\/?$/i, '');
  return x;
}

/**
 * Si el HTML del sitio sale de `public/clinica` servido por el mismo Laravel, la API está en el mismo origen.
 * Evita "Failed to fetch" cuando VITE_API_BASE_URL apunta a `localhost` y entras por `127.0.0.1:8000` (u otro host).
 */
function inferApiBaseFromWindow(): string | null {
  if (typeof window === 'undefined') {
    return null;
  }
  const { origin, pathname } = window.location;

  const mark = '/backend/api/public/clinica';
  const i = pathname.indexOf(mark);
  if (i !== -1) {
    return origin + pathname.slice(0, i + '/backend/api/public'.length);
  }

  if (
    pathname.startsWith('/clinica/') ||
    pathname === '/clinica' ||
    /^\/clinica\/index\.html$/i.test(pathname)
  ) {
    return origin;
  }

  return null;
}

/** `true` si el front puede llamar a rutas `/api/...` de Laravel. */
export function isViteApiBaseConfigured(): boolean {
  return apiBase() !== '';
}

export async function apiJson<T>(path: string, init?: RequestInit): Promise<T> {
  const base = apiBase();
  if (base === '') {
    throw new Error(
      'No pudimos conectar con el servicio en este momento. Inténtalo de nuevo en unos minutos.',
    );
  }

  const url = `${base}${path.startsWith('/') ? path : `/${path}`}`;
  const method = String(init?.method ?? 'GET').toUpperCase();
  const headers = new Headers(init?.headers);
  if (!headers.has('Accept')) {
    headers.set('Accept', 'application/json');
  }
  if (method !== 'GET' && method !== 'HEAD' && !headers.has('Content-Type')) {
    headers.set('Content-Type', 'application/json');
  }

  const res = await fetch(url, {
    ...init,
    headers,
  });

  const ct = res.headers.get('content-type') ?? '';
  const bodyText = await res.text();

  if (!ct.includes('application/json')) {
    const hint =
      res.status === 200 && bodyText.trimStart().startsWith('<')
        ? ' Intenta recargar la página. Si el problema continúa, contáctanos.'
        : '';
    throw new Error(`No pudimos obtener una respuesta válida del servicio.${hint}`);
  }

  let parsed: unknown;
  try {
    parsed = JSON.parse(bodyText) as unknown;
  } catch {
    throw new Error('Respuesta inesperada del servicio. Inténtalo de nuevo.');
  }

  if (!res.ok) {
    throw new Error(formatApiJsonError(parsed, bodyText, res.status));
  }

  return parsed as T;
}

function formatApiJsonError(parsed: unknown, bodyText: string, status: number): string {
  if (typeof parsed === 'object' && parsed !== null) {
    const o = parsed as Record<string, unknown>;
    const message = o.message;
    if (typeof message === 'string' && message.trim() !== '') {
      return message.trim();
    }
    const errors = o.errors;
    if (typeof errors === 'object' && errors !== null) {
      const parts: string[] = [];
      for (const v of Object.values(errors as Record<string, unknown>)) {
        if (Array.isArray(v)) {
          for (const item of v) {
            if (typeof item === 'string' && item.trim() !== '') {
              parts.push(item.trim());
            }
          }
        }
      }
      if (parts.length > 0) {
        return parts.join(' ');
      }
    }
  }

  return bodyText.trim() !== '' ? bodyText : 'No se pudo completar la operación. Inténtalo de nuevo.';
}
