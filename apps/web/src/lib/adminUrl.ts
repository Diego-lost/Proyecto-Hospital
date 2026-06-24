import { apiBase } from '../api';

/** URL del panel Blade (/admin) en el mismo Laravel que la API. */
export function adminPanelUrl(): string {
  if (typeof window !== 'undefined') {
    const { origin, pathname } = window.location;

    if (
      pathname.startsWith('/clinica/') ||
      pathname === '/clinica' ||
      pathname.includes('/backend/api/public/clinica')
    ) {
      return `${origin}/admin`;
    }

    if (import.meta.env.DEV) {
      const fromEnv = String(import.meta.env.VITE_API_BASE_URL ?? '').trim();
      if (fromEnv !== '') {
        const base = fromEnv.replace(/\/+$/, '').replace(/\/api\/?$/i, '');
        if (base.startsWith('http')) {
          return `${base}/admin`;
        }
      }
      return 'http://127.0.0.1:8000/admin';
    }
  }

  const base = apiBase();
  return base !== '' ? `${base}/admin` : '/admin';
}
