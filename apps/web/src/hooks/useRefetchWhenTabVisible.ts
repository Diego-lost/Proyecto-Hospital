import { useEffect, useRef } from 'react';

/**
 * Ejecuta `refetch` cuando la pestaña pasa de oculta a visible (p. ej. tras usar el panel admin en otra pestaña).
 */
export function useRefetchWhenTabVisible(refetch: () => void, options?: { debounceMs?: number }): void {
  const debounceMs = options?.debounceMs ?? 450;
  const refetchRef = useRef(refetch);
  refetchRef.current = refetch;
  const timerRef = useRef<ReturnType<typeof setTimeout> | null>(null);
  const sawHiddenRef = useRef(false);

  useEffect(() => {
    const schedule = () => {
      if (timerRef.current) {
        clearTimeout(timerRef.current);
      }
      timerRef.current = setTimeout(() => {
        timerRef.current = null;
        refetchRef.current();
      }, debounceMs);
    };

    const onVis = () => {
      if (document.visibilityState === 'hidden') {
        sawHiddenRef.current = true;
        return;
      }
      if (!sawHiddenRef.current) {
        return;
      }
      schedule();
    };

    document.addEventListener('visibilitychange', onVis);
    return () => {
      document.removeEventListener('visibilitychange', onVis);
      if (timerRef.current) {
        clearTimeout(timerRef.current);
      }
    };
  }, [debounceMs]);
}
