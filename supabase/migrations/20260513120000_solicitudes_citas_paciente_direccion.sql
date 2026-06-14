-- Dirección del paciente en solicitudes de cita (alineado con Laravel).
alter table public.solicitudes_citas
  add column if not exists paciente_direccion text;
