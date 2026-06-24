import type { EspecialidadRow } from '../types/catalogRows';
import { specialtyImageUrl } from './specialtyImages';

export function isHttpUrl(s: unknown): boolean {
  return typeof s === 'string' && /^https?:\/\//i.test(s.trim());
}

export function dedupeEspecialidades(list: EspecialidadRow[]): EspecialidadRow[] {
  const seen = new Set<string>();
  const out: EspecialidadRow[] = [];

  for (const row of [...list].sort((a, b) => a.id - b.id)) {
    const key = row.nombre
      .normalize('NFD')
      .replace(/[\u0300-\u036f]/g, '')
      .toLowerCase()
      .trim();
    if (seen.has(key)) {
      continue;
    }
    seen.add(key);
    out.push(row);
  }

  return out.sort((a, b) => a.nombre.localeCompare(b.nombre, 'es'));
}

export function especialidadCardImage(row: EspecialidadRow): string {
  if (isHttpUrl(row.imagen)) {
    return row.imagen!.trim();
  }
  return specialtyImageUrl(row.nombre);
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
