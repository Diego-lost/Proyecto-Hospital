import { apiJson, isViteApiBaseConfigured } from '../api';
import type { EspecialidadRow, MedicoRow, ServicioRow } from '../types/catalogRows';
import { getSupabase } from './supabaseClient';

function singleRel<T>(v: T | T[] | null | undefined): T | null {
  if (v == null) {
    return null;
  }
  return Array.isArray(v) ? (v[0] ?? null) : v;
}

type MedicoJoined = {
  id: number;
  nombre: string;
  foto: string | null;
  dni: string | null;
  especialidades: { nombre: string } | { nombre: string }[] | null;
};

type ServicioJoined = {
  id: number;
  nombre: string;
  descripcion: string;
  precio: number | string;
  medicos: { nombre: string } | { nombre: string }[] | null;
};

export async function fetchEspecialidades(): Promise<EspecialidadRow[]> {
  const sb = getSupabase();
  if (sb) {
    const { data, error } = await sb.from('especialidades').select('id, nombre, imagen').order('nombre');
    if (error) {
      throw new Error(error.message);
    }
    return (data ?? []) as EspecialidadRow[];
  }
  return apiJson<EspecialidadRow[]>('/api/especialidades');
}

export async function fetchMedicos(): Promise<MedicoRow[]> {
  const sb = getSupabase();
  if (sb) {
    const { data, error } = await sb
      .from('medicos')
      .select('id, nombre, foto, dni, especialidades ( nombre )')
      .order('nombre');
    if (error) {
      throw new Error(error.message);
    }
    const rows = (data ?? []) as MedicoJoined[];
    return rows.map((r) => {
      const esp = singleRel(r.especialidades);
      return {
        id: r.id,
        nombre: r.nombre,
        foto: r.foto,
        especialidad: esp ? { nombre: esp.nombre } : null,
      };
    });
  }
  return apiJson<MedicoRow[]>('/api/medicos');
}

/** Servicios para la pasarela: siempre desde Laravel (mismo origen que Stripe checkout). */
export async function fetchServiciosForPago(): Promise<ServicioRow[]> {
  if (!isViteApiBaseConfigured()) {
    throw new Error('Configura VITE_API_BASE_URL para cargar servicios a pagar.');
  }
  return apiJson<ServicioRow[]>('/api/servicios');
}

export async function fetchServicios(): Promise<ServicioRow[]> {
  const sb = getSupabase();
  if (sb) {
    const { data, error } = await sb
      .from('servicios')
      .select('id, nombre, descripcion, precio, medicos ( nombre )')
      .order('nombre');
    if (error) {
      throw new Error(error.message);
    }
    const rows = (data ?? []) as ServicioJoined[];
    return rows.map((r) => {
      const med = singleRel(r.medicos);
      return {
        id: r.id,
        nombre: r.nombre,
        descripcion: r.descripcion,
        precio: r.precio,
        medico: med ? { nombre: med.nombre } : null,
      };
    });
  }
  return apiJson<ServicioRow[]>('/api/servicios');
}

export type PacienteDetallePayload = {
  nombre: string;
  apellido: string;
  sexo: string;
  estado_civil: string;
  fecha_nacimiento: string;
  lugar_nacimiento: string;
};

export type SolicitudCitaPayload = {
  nombre: string;
  paciente_dni: string;
  paciente_direccion: string;
  telefono: string;
  email?: string;
  paciente_detalle?: PacienteDetallePayload;
  especialidad?: string;
  medico_id?: number;
  fecha?: string;
  hora?: string;
  motivo?: string;
  triage_riesgo?: 'bajo' | 'medio' | 'alto';
  triage_accion?: 'autocuidado' | 'consulta_24h' | 'urgencias';
  triage_resumen?: Record<string, unknown>;
  origen?: string;
};

export type TriageDolorPayload = {
  motivo: string;
  edad: number;
  sexo?: 'masculino' | 'femenino' | 'otro';
  embarazo?: boolean;
  intensidad_dolor: number;
  duracion_horas: number;
  ubicacion_dolor: string;
  sintomas_asociados?: string[];
  comorbilidades?: string[];
};

export type TriageCausa = {
  titulo: string;
  descripcion: string;
  sintomas_coincidentes: string[];
};

export type TriageDolorResult = {
  nivel_riesgo: 'bajo' | 'medio' | 'alto';
  accion_recomendada: 'autocuidado' | 'consulta_24h' | 'urgencias';
  senales_alarma: string[];
  recomendaciones_generales: string[];
  intro?: string;
  posibles_causas?: TriageCausa[];
  especialidad_sugerida?: string | null;
  especialidad_id?: number | null;
  motivo_especialidad?: string;
  disclaimer_peru: string;
};

export async function evaluarDolorConIa(payload: TriageDolorPayload): Promise<TriageDolorResult> {
  if (!isViteApiBaseConfigured()) {
    throw new Error('Configura VITE_API_BASE_URL para usar el triaje IA.');
  }

  const res = await apiJson<{ ok: boolean; triage: TriageDolorResult }>('/api/ai/triage-dolor', {
    method: 'POST',
    body: JSON.stringify(payload),
  });
  return res.triage;
}

export async function consultarAsistenteMedico(mensaje: string): Promise<TriageDolorResult> {
  if (!isViteApiBaseConfigured()) {
    throw new Error('Configura VITE_API_BASE_URL para usar el asistente médico.');
  }

  const res = await apiJson<{ ok: boolean; consulta: TriageDolorResult }>('/api/ai/consulta', {
    method: 'POST',
    body: JSON.stringify({ mensaje }),
  });
  return res.consulta;
}

export async function submitSolicitudCita(payload: SolicitudCitaPayload): Promise<void> {
  // Misma base de datos que el panel: Laravel valida y escribe con la conexión de servidor.
  // El insert directo con anon + RLS en Supabase suele fallar aunque el catálogo sí lea por Supabase.
  if (isViteApiBaseConfigured()) {
    await apiJson<{ ok: boolean }>('/api/solicitudes-citas', {
      method: 'POST',
      body: JSON.stringify({
        nombre: payload.nombre,
        paciente_dni: payload.paciente_dni,
        paciente_direccion: payload.paciente_direccion,
        telefono: payload.telefono,
        email: payload.email || undefined,
        especialidad: payload.especialidad || undefined,
        medico_id: payload.medico_id ?? undefined,
        fecha: payload.fecha || undefined,
        hora: payload.hora || undefined,
        motivo: payload.motivo || undefined,
        triage_riesgo: payload.triage_riesgo ?? undefined,
        triage_accion: payload.triage_accion ?? undefined,
        triage_resumen: mergeTriageResumen(payload),
        origen: payload.origen ?? 'react',
      }),
    });
    return;
  }

  const sb = getSupabase();
  if (sb) {
    const { error } = await sb.from('solicitudes_citas').insert({
      nombre: payload.nombre,
      paciente_dni: payload.paciente_dni,
      paciente_direccion: payload.paciente_direccion,
      telefono: payload.telefono,
      email: payload.email ?? null,
      especialidad: payload.especialidad ?? null,
      medico_id: payload.medico_id ?? null,
      fecha: payload.fecha ?? null,
      hora: payload.hora ?? null,
      motivo: payload.motivo ?? null,
      triage_riesgo: payload.triage_riesgo ?? null,
      triage_accion: payload.triage_accion ?? null,
      triage_resumen: mergeTriageResumen(payload),
      origen: payload.origen ?? 'react',
      estado: 'nueva',
    });
    if (error) {
      throw new Error(error.message);
    }
    return;
  }

  throw new Error(
    'Configura VITE_API_BASE_URL (recomendado) o Supabase (URL + anon + VITE_USE_SUPABASE) para enviar la solicitud.',
  );
}

function mergeTriageResumen(payload: SolicitudCitaPayload): Record<string, unknown> | undefined {
  const base =
    payload.triage_resumen && typeof payload.triage_resumen === 'object'
      ? { ...payload.triage_resumen }
      : {};
  if (payload.paciente_detalle) {
    base.datos_paciente = payload.paciente_detalle;
  }
  return Object.keys(base).length > 0 ? base : undefined;
}
