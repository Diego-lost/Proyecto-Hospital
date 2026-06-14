/// <reference types="vite/client" />

interface ImportMetaEnv {
  readonly VITE_API_BASE_URL?: string;
  /** URL del portal de transparencia (externo). */
  readonly VITE_PORTAL_TRANSPARENCIA_URL?: string;
  /** URL del proyecto Supabase (Settings → API). */
  readonly VITE_SUPABASE_URL?: string;
  /** Clave anónima (pública) de Supabase. */
  readonly VITE_SUPABASE_ANON_KEY?: string;
  /** `true` para leer/escribir catálogo y solicitudes contra Supabase en lugar de Laravel. */
  readonly VITE_USE_SUPABASE?: string;
}

interface ImportMeta {
  readonly env: ImportMetaEnv;
}
