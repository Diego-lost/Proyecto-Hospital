import { createClient, type SupabaseClient } from '@supabase/supabase-js';

let cached: SupabaseClient | null | undefined;

export function isSupabaseDataEnabled(): boolean {
  const url = String(import.meta.env.VITE_SUPABASE_URL ?? '').trim();
  const key = String(import.meta.env.VITE_SUPABASE_ANON_KEY ?? '').trim();
  const on = String(import.meta.env.VITE_USE_SUPABASE ?? '').trim().toLowerCase();
  return Boolean(url && key && (on === 'true' || on === '1'));
}

/** Cliente Supabase; `null` si no está configurado el modo Supabase. */
export function getSupabase(): SupabaseClient | null {
  if (!isSupabaseDataEnabled()) {
    cached = null;
    return null;
  }
  if (cached === undefined) {
    const url = String(import.meta.env.VITE_SUPABASE_URL).trim();
    const key = String(import.meta.env.VITE_SUPABASE_ANON_KEY).trim();
    cached = createClient(url, key);
  }
  return cached;
}
