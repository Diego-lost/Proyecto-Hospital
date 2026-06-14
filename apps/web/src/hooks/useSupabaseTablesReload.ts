import { useEffect, useRef } from 'react';
import { getSupabase } from '../lib/supabaseClient';

/**
 * Cuando los datos vienen de Supabase y la réplica en tiempo real está habilitada para estas tablas,
 * vuelve a cargar el catálogo al insertar/actualizar/borrar desde otro cliente (p. ej. panel que escriba en Postgres).
 */
export function useSupabaseTablesReload(
  tables: readonly string[],
  onReload: () => void,
  options?: { debounceMs?: number },
): void {
  const debounceMs = options?.debounceMs ?? 400;
  const onReloadRef = useRef(onReload);
  onReloadRef.current = onReload;
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const tablesKey = JSON.stringify([...tables].sort());

  useEffect(() => {
    const sb = getSupabase();
    const tableList = JSON.parse(tablesKey) as string[];
    if (!sb || tableList.length === 0) {
      return undefined;
    }

    const schedule = () => {
      if (timerRef.current) {
        clearTimeout(timerRef.current);
      }
      timerRef.current = setTimeout(() => {
        timerRef.current = null;
        onReloadRef.current();
      }, debounceMs);
    };

    const channel = sb.channel(`catalog-sync-${tablesKey}-${Math.random().toString(36).slice(2, 9)}`);
    for (const table of tableList) {
      channel.on('postgres_changes', { event: '*', schema: 'public', table }, schedule);
    }
    channel.subscribe();

    return () => {
      if (timerRef.current) {
        clearTimeout(timerRef.current);
      }
      void sb.removeChannel(channel);
    };
  }, [debounceMs, tablesKey]);
}
