export function isHttpUrl(s: unknown): boolean {
  return typeof s === 'string' && /^https?:\/\//i.test(s.trim());
}

export function initials(nombre: string): string {
  const parts = String(nombre || '')
    .trim()
    .split(/\s+/)
    .filter(Boolean);
  if (!parts.length) return '?';
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase();
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

export function excerpt(text: string, max: number): string {
  const t = String(text || '').trim();
  if (t.length <= max) return t;
  return `${t.slice(0, max).trim()}…`;
}

export function formatMoney(n: unknown): string {
  const x = Number(n);
  if (Number.isNaN(x)) return '—';
  try {
    return new Intl.NumberFormat('es-PE', { style: 'currency', currency: 'PEN' }).format(x);
  } catch {
    return `S/ ${x.toFixed(2)}`;
  }
}
