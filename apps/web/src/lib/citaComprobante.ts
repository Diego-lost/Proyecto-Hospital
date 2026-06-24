import { apiJson } from '../api';
import { formatMoney } from './catalogUtils';

export type CitaComprobante = {
  solicitud_id: number;
  estado_cita: string;
  nombre: string;
  paciente_dni: string;
  paciente_direccion: string;
  telefono: string;
  email: string | null;
  especialidad: string | null;
  medico: { nombre: string; especialidad?: string | null } | null;
  fecha: string | null;
  hora: string | null;
  motivo: string | null;
  paciente_detalle: Record<string, unknown> | null;
  pago: {
    id: number;
    estado: string;
    metodo: string;
    monto: string | number;
    moneda: string;
    referencia_manual?: string | null;
    paid_at?: string | null;
  } | null;
  servicio: {
    nombre: string;
    descripcion: string;
    precio: string | number;
  } | null;
};

export async function fetchCitaComprobante(
  solicitudId: number,
  email?: string,
): Promise<CitaComprobante> {
  const q = new URLSearchParams();
  if (email?.trim()) {
    q.set('email', email.trim());
  }
  const suffix = q.toString() ? `?${q}` : '';
  const res = await apiJson<{ comprobante: CitaComprobante }>(
    `/api/solicitudes-citas/${solicitudId}/comprobante${suffix}`,
  );
  return res.comprobante;
}

function formatFecha(fecha: string | null): string {
  if (!fecha) {
    return '—';
  }
  try {
    return new Date(fecha + 'T12:00:00').toLocaleDateString('es-PE', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    });
  } catch {
    return fecha;
  }
}

function estadoPagoLabel(estado: string): string {
  switch (estado) {
    case 'paid':
      return 'Pagado';
    case 'pending_manual':
      return 'Pago pendiente de validación';
    case 'pending':
      return 'Pago en proceso';
    default:
      return estado;
  }
}

export function buildComprobanteHtml(c: CitaComprobante): string {
  const det = c.paciente_detalle ?? {};
  const lineas: string[] = [
    '<!DOCTYPE html><html lang="es"><head><meta charset="utf-8">',
    '<title>Comprobante de cita — Clínica NovaSalud</title>',
    '<style>body{font-family:Segoe UI,Arial,sans-serif;max-width:720px;margin:2rem auto;padding:0 1rem;color:#1e293b}',
    'h1{color:#1e6bb8;font-size:1.5rem}table{width:100%;border-collapse:collapse;margin:1rem 0}',
    'th,td{text-align:left;padding:8px 10px;border-bottom:1px solid #e2e8f0}th{width:38%;color:#64748b;font-weight:600}',
    '.foot{margin-top:2rem;font-size:.85rem;color:#64748b}</style></head><body>',
    '<h1>Comprobante de solicitud de cita</h1>',
    '<p>Clínica NovaSalud — comprobante generado el ' +
      new Date().toLocaleString('es-PE') +
      '</p>',
    '<table>',
    `<tr><th>Nº solicitud</th><td>#${c.solicitud_id}</td></tr>`,
    `<tr><th>Paciente</th><td>${escapeHtml(c.nombre)}</td></tr>`,
    `<tr><th>DNI</th><td>${escapeHtml(c.paciente_dni)}</td></tr>`,
    `<tr><th>Dirección</th><td>${escapeHtml(c.paciente_direccion)}</td></tr>`,
    `<tr><th>Teléfono</th><td>${escapeHtml(c.telefono)}</td></tr>`,
    `<tr><th>Correo</th><td>${escapeHtml(c.email ?? '—')}</td></tr>`,
  ];

  if (typeof det.sexo === 'string' && det.sexo) {
    lineas.push(`<tr><th>Sexo</th><td>${escapeHtml(det.sexo)}</td></tr>`);
  }
  if (typeof det.estado_civil === 'string' && det.estado_civil) {
    lineas.push(`<tr><th>Estado civil</th><td>${escapeHtml(det.estado_civil)}</td></tr>`);
  }
  if (typeof det.fecha_nacimiento === 'string' && det.fecha_nacimiento) {
    lineas.push(`<tr><th>Fecha nacimiento</th><td>${escapeHtml(det.fecha_nacimiento)}</td></tr>`);
  }
  if (typeof det.lugar_nacimiento === 'string' && det.lugar_nacimiento) {
    lineas.push(`<tr><th>Lugar nacimiento</th><td>${escapeHtml(det.lugar_nacimiento)}</td></tr>`);
  }

  lineas.push(
    `<tr><th>Especialidad</th><td>${escapeHtml(c.especialidad ?? '—')}</td></tr>`,
    `<tr><th>Médico</th><td>${escapeHtml(c.medico?.nombre ?? 'Sin preferencia')}</td></tr>`,
    `<tr><th>Fecha preferida</th><td>${escapeHtml(formatFecha(c.fecha))}</td></tr>`,
    `<tr><th>Hora preferida</th><td>${escapeHtml(c.hora ?? '—')}</td></tr>`,
    `<tr><th>Motivo</th><td>${escapeHtml(c.motivo ?? '—')}</td></tr>`,
    `<tr><th>Estado cita</th><td>${escapeHtml(c.estado_cita)}</td></tr>`,
  );

  if (c.servicio) {
    lineas.push(
      `<tr><th>Servicio</th><td>${escapeHtml(c.servicio.nombre)}</td></tr>`,
      `<tr><th>Precio servicio</th><td>${escapeHtml(formatMoney(c.servicio.precio))}</td></tr>`,
    );
  }

  if (c.pago) {
    lineas.push(
      `<tr><th>Pago #</th><td>${c.pago.id}</td></tr>`,
      `<tr><th>Estado pago</th><td>${escapeHtml(estadoPagoLabel(c.pago.estado))}</td></tr>`,
      `<tr><th>Método</th><td>${escapeHtml(c.pago.metodo)}</td></tr>`,
      `<tr><th>Monto</th><td>${escapeHtml(formatMoney(c.pago.monto))}</td></tr>`,
    );
    if (c.pago.referencia_manual) {
      lineas.push(
        `<tr><th>Referencia</th><td>${escapeHtml(c.pago.referencia_manual)}</td></tr>`,
      );
    }
  }

  lineas.push(
    '</table>',
    '<p class="foot">La clínica confirmará disponibilidad de horario y, si aplica, validará tu pago manual. Conserva este comprobante.</p>',
    '</body></html>',
  );

  return lineas.join('');
}

function escapeHtml(s: string): string {
  return s
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

export function downloadComprobanteHtml(c: CitaComprobante): void {
  const html = buildComprobanteHtml(c);
  const blob = new Blob([html], { type: 'text/html;charset=utf-8' });
  const url = URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = `cita-${c.solicitud_id}-novasalud.html`;
  a.click();
  URL.revokeObjectURL(url);
}
