export type EspecialidadRow = { id: number; nombre: string; imagen?: string | null };
export type MedicoRow = {
  id: number;
  nombre: string;
  foto?: string | null;
  especialidad?: { nombre: string } | null;
};
export type ServicioRow = {
  id: number;
  nombre: string;
  descripcion: string;
  precio: number | string;
  medico?: { nombre: string } | null;
};
