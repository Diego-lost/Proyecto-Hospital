/** Ruta a un archivo en `public/` (respeta `base` de Vite, p. ej. `./`). */
export function publicAsset(path: string): string {
  const raw = import.meta.env.BASE_URL ?? '/';
  const base = raw.endsWith('/') ? raw : `${raw}/`;
  return `${base}${encodeURI(path.replace(/^\//, ''))}`;
}
