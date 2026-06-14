import { FormEvent, useCallback, useEffect, useMemo, useRef, useState } from 'react';
import { Calendar, ChevronLeft, ChevronRight, Loader2 } from 'lucide-react';
import { apiJson, isViteApiBaseConfigured } from '../api';
import AgendarCitaHero from '../components/cita/AgendarCitaHero';
import DatosPacienteForm, {
  buildNombreCompleto,
  buildPacienteDetalle,
  ResumenDatosPaciente,
  splitNombreCompleto,
  type DatosPacienteValues,
} from '../components/cita/DatosPacienteForm';
import AgendarCitaStepper, {
  type CitaWizardStep,
  stepIcon,
  stepTitle,
} from '../components/cita/AgendarCitaStepper';
import { useRefetchWhenTabVisible } from '../hooks/useRefetchWhenTabVisible';
import { useSupabaseTablesReload } from '../hooks/useSupabaseTablesReload';
import {
  fetchEspecialidades,
  fetchMedicos,
  submitSolicitudCita,
} from '../lib/remoteCatalog';
import type { EspecialidadRow, MedicoRow } from '../types/catalogRows';

type ReniecResp = {
  ok?: boolean;
  encontrado?: boolean;
  datos?: { nombre?: string; direccion?: string | null };
  mensaje?: string | null;
  detalle?: string;
  message?: string;
};

type PacienteResp = {
  ok?: boolean;
  encontrado?: boolean;
  datos?: { nombre?: string; telefono?: string; email?: string | null; direccion?: string | null };
  mensaje?: string | null;
  detalle?: string;
  message?: string;
};

const RENIEC_DETALLE_MSG: Record<string, string> = {
  sin_token: 'Falta PERU_API_KEY en el servidor (Perú API).',
  dni_invalido: 'El DNI debe tener 7 u 8 dígitos (solo números).',
  red: 'Error de conexión con el servicio de consulta.',
  no_autorizado: 'El servicio rechazó la consulta (clave API o plan).',
  error_http: 'El servicio respondió con error.',
  sin_datos: 'No hay datos para ese DNI.',
};

const STEP_ORDER: CitaWizardStep[] = ['paciente', 'especialidad', 'horario', 'confirmar'];

function prevStep(step: CitaWizardStep): CitaWizardStep | null {
  const i = STEP_ORDER.indexOf(step);
  return i > 0 ? STEP_ORDER[i - 1]! : null;
}

export default function SolicitudPage() {
  const [step, setStep] = useState<CitaWizardStep>('paciente');
  const [dni, setDni] = useState('');
  const [direccion, setDireccion] = useState('');
  const [nombre, setNombre] = useState('');
  const [apellido, setApellido] = useState('');
  const [sexo, setSexo] = useState('');
  const [estadoCivil, setEstadoCivil] = useState('');
  const [fechaNacimiento, setFechaNacimiento] = useState('');
  const [lugarNacimiento, setLugarNacimiento] = useState('');
  const [autorizaDatos, setAutorizaDatos] = useState(false);
  const [showPacienteErrors, setShowPacienteErrors] = useState(false);
  const [telefono, setTelefono] = useState('');
  const [email, setEmail] = useState('');
  const [especialidad, setEspecialidad] = useState('');
  const [medicoId, setMedicoId] = useState<number | ''>('');
  const [fecha, setFecha] = useState('');
  const [hora, setHora] = useState('');
  const [motivo, setMotivo] = useState('');
  const [opts, setOpts] = useState<EspecialidadRow[]>([]);
  const [medicos, setMedicos] = useState<MedicoRow[]>([]);
  const [busy, setBusy] = useState(false);
  const [reniecBusy, setReniecBusy] = useState(false);
  const [msg, setMsg] = useState<string | null>(null);
  const [err, setErr] = useState<string | null>(null);
  const [hint, setHint] = useState<string | null>(null);
  const autofillGen = useRef(0);

  const medicoOpts = useMemo(() => {
    if (!especialidad.trim()) {
      return medicos;
    }
    return medicos.filter((m) => m.especialidad?.nombre === especialidad);
  }, [medicos, especialidad]);

  const selectedMedico = useMemo(
    () => (medicoId === '' ? null : medicos.find((m) => m.id === medicoId) ?? null),
    [medicoId, medicos],
  );

  const loadEspecialidades = useCallback(async (opts?: { silent?: boolean }) => {
    const silent = Boolean(opts?.silent);
    try {
      const list = await fetchEspecialidades();
      if (Array.isArray(list)) {
        setOpts(list);
      }
    } catch {
      if (!silent) {
        setOpts([]);
      }
    }
  }, []);

  const loadMedicos = useCallback(async (opts?: { silent?: boolean }) => {
    const silent = Boolean(opts?.silent);
    try {
      const list = await fetchMedicos();
      if (Array.isArray(list)) {
        setMedicos(list);
      }
    } catch {
      if (!silent) {
        setMedicos([]);
      }
    }
  }, []);

  useEffect(() => {
    void loadEspecialidades();
    void loadMedicos();
  }, [loadEspecialidades, loadMedicos]);

  useRefetchWhenTabVisible(() => {
    void loadEspecialidades({ silent: true });
    void loadMedicos({ silent: true });
  });
  useSupabaseTablesReload(['especialidades', 'medicos'], () => {
    void loadEspecialidades({ silent: true });
    void loadMedicos({ silent: true });
  });

  const autocompletarPorDni = useCallback(async (digits: string) => {
    if (!isViteApiBaseConfigured()) {
      setErr('Configura VITE_API_BASE_URL para completar nombre y dirección por DNI.');
      return;
    }

    const gen = ++autofillGen.current;
    setReniecBusy(true);
    setErr(null);
    setHint(null);

    let nombreOk = false;
    let direccionOk = false;

    try {
      const p = await apiJson<PacienteResp>(`/api/busqueda/paciente?dni=${encodeURIComponent(digits)}`, {
        method: 'GET',
      });
      if (gen !== autofillGen.current) {
        return;
      }
      if (p.ok === false && p.message) {
        setErr(p.message);
        return;
      }
      if (p.encontrado && p.datos) {
        const n = p.datos.nombre?.trim();
        if (n) {
          const split = splitNombreCompleto(n);
          setNombre(split.nombres);
          setApellido(split.apellidos);
          nombreOk = true;
        }
        const t = p.datos.telefono?.trim();
        if (t) {
          setTelefono(t);
        }
        const em = p.datos.email;
        if (typeof em === 'string' && em.trim() !== '') {
          setEmail(em.trim());
        }
        const d = p.datos.direccion;
        if (typeof d === 'string' && d.trim() !== '') {
          setDireccion(d.trim());
          direccionOk = true;
        }
      }

      const j = await apiJson<ReniecResp>(`/api/busqueda/reniec?dni=${encodeURIComponent(digits)}`, {
        method: 'GET',
      });
      if (gen !== autofillGen.current) {
        return;
      }
      if (j.ok === false && j.message) {
        if (!nombreOk) {
          setErr(j.message);
        }
        return;
      }
      if (j.encontrado && j.datos?.nombre) {
        const split = splitNombreCompleto(j.datos.nombre.trim());
        setNombre(split.nombres);
        setApellido(split.apellidos);
        nombreOk = true;
        const rd = j.datos.direccion;
        if (typeof rd === 'string' && rd.trim() !== '') {
          setDireccion(rd.trim());
          direccionOk = true;
        }
      } else if (!nombreOk) {
        if (j.mensaje) {
          setErr(j.mensaje);
          return;
        }
        const det = j.detalle ?? '';
        setErr(RENIEC_DETALLE_MSG[det] ?? RENIEC_DETALLE_MSG.sin_datos);
        return;
      }

      if (nombreOk && !direccionOk) {
        setHint(
          'No consta dirección en RENIEC ni en solicitudes anteriores. Complétela en el campo Dirección para poder enviar.',
        );
      } else {
        setHint(null);
      }
    } catch (e) {
      if (gen === autofillGen.current) {
        setErr(e instanceof Error ? e.message : 'No se pudo consultar por DNI.');
      }
    } finally {
      if (gen === autofillGen.current) {
        setReniecBusy(false);
      }
    }
  }, []);

  useEffect(() => {
    const digits = dni.replace(/\D/g, '');
    if (digits.length < 7 || digits.length > 8) {
      return;
    }
    if (!isViteApiBaseConfigured()) {
      return;
    }
    const t = window.setTimeout(() => {
      void autocompletarPorDni(digits);
    }, 480);
    return () => window.clearTimeout(t);
  }, [dni, autocompletarPorDni]);

  const nombreCompleto = useMemo(() => buildNombreCompleto(nombre, apellido), [nombre, apellido]);

  const pacienteValues: DatosPacienteValues = useMemo(
    () => ({
      nombre,
      apellido,
      dni,
      sexo,
      email,
      telefono,
      estadoCivil,
      fechaNacimiento,
      direccion,
      lugarNacimiento,
      autorizaDatos,
    }),
    [
      nombre,
      apellido,
      dni,
      sexo,
      email,
      telefono,
      estadoCivil,
      fechaNacimiento,
      direccion,
      lugarNacimiento,
      autorizaDatos,
    ],
  );

  const pacienteDetalle = useMemo(() => buildPacienteDetalle(pacienteValues), [pacienteValues]);

  function updatePacienteField<K extends keyof DatosPacienteValues>(
    key: K,
    value: DatosPacienteValues[K],
  ): void {
    switch (key) {
      case 'nombre':
        setNombre(String(value));
        break;
      case 'apellido':
        setApellido(String(value));
        break;
      case 'dni':
        setDni(String(value));
        break;
      case 'sexo':
        setSexo(String(value));
        break;
      case 'email':
        setEmail(String(value));
        break;
      case 'telefono':
        setTelefono(String(value));
        break;
      case 'estadoCivil':
        setEstadoCivil(String(value));
        break;
      case 'fechaNacimiento':
        setFechaNacimiento(String(value));
        break;
      case 'direccion':
        setDireccion(String(value));
        break;
      case 'lugarNacimiento':
        setLugarNacimiento(String(value));
        break;
      case 'autorizaDatos':
        setAutorizaDatos(Boolean(value));
        break;
    }
  }

  function validatePaciente(): string | null {
    const digits = dni.replace(/\D/g, '');
    if (digits.length < 7 || digits.length > 8) {
      return 'Indica un DNI de 7 u 8 dígitos (solo números).';
    }
    if (!nombre.trim() || !apellido.trim()) {
      return 'Indica nombre y apellido del paciente.';
    }
    if (!sexo.trim()) {
      return 'Selecciona el sexo.';
    }
    if (!email.trim()) {
      return 'Indica un correo electrónico.';
    }
    if (!telefono.trim()) {
      return 'Indica un teléfono de contacto.';
    }
    if (!estadoCivil.trim()) {
      return 'Selecciona el estado civil.';
    }
    if (!fechaNacimiento.trim()) {
      return 'Indica la fecha de nacimiento.';
    }
    if (!direccion.trim()) {
      return 'Indica la dirección del paciente.';
    }
    if (!lugarNacimiento.trim()) {
      return 'Selecciona el lugar de nacimiento.';
    }
    if (!autorizaDatos) {
      return 'Debes autorizar el uso de tus datos personales.';
    }
    return null;
  }

  function goNext() {
    setErr(null);
    if (step === 'paciente') {
      setShowPacienteErrors(true);
      const v = validatePaciente();
      if (v) {
        setErr(v);
        return;
      }
      setShowPacienteErrors(false);
      setStep('especialidad');
      return;
    }
    if (step === 'especialidad') {
      setStep('horario');
      return;
    }
    if (step === 'horario') {
      setStep('confirmar');
    }
  }

  function goBack() {
    setErr(null);
    const prev = prevStep(step);
    if (prev) {
      setStep(prev);
    }
  }

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    const v = validatePaciente();
    if (v) {
      setErr(v);
      setStep('paciente');
      return;
    }
    const digits = dni.replace(/\D/g, '');
    setBusy(true);
    setMsg(null);
    setErr(null);
    setHint(null);
    try {
      await submitSolicitudCita({
        nombre: nombreCompleto,
        paciente_dni: digits,
        paciente_direccion: direccion.trim(),
        telefono: telefono.trim(),
        email: email.trim() || undefined,
        paciente_detalle: pacienteDetalle,
        especialidad: especialidad || undefined,
        medico_id: medicoId === '' ? undefined : medicoId,
        fecha: fecha || undefined,
        hora: hora || undefined,
        motivo: motivo.trim() || undefined,
        origen: 'react',
      });
      setMsg('Solicitud registrada. Nos pondremos en contacto para confirmar disponibilidad.');
      setStep('paciente');
      setDni('');
      setDireccion('');
      setNombre('');
      setApellido('');
      setSexo('');
      setEstadoCivil('');
      setFechaNacimiento('');
      setLugarNacimiento('');
      setAutorizaDatos(false);
      setShowPacienteErrors(false);
      setTelefono('');
      setEmail('');
      setEspecialidad('');
      setMedicoId('');
      setFecha('');
      setHora('');
      setMotivo('');
    } catch (e) {
      setErr(e instanceof Error ? e.message : 'Error al enviar');
    } finally {
      setBusy(false);
    }
  }

  const StepIcon = stepIcon(step);
  const today = new Date().toISOString().slice(0, 10);

  const nextLabel =
    step === 'especialidad'
        ? 'Siguiente: Elegir horario'
        : step === 'horario'
          ? 'Siguiente: Confirmar'
          : null;

  const pacienteValid = validatePaciente() === null;

  const canGoNext = step !== 'paciente' || pacienteValid;

  return (
    <main id="contenido" className="page-main cita-page">
      <AgendarCitaHero />

      <section className="pb-16 pt-2 md:pt-4">
        <div
          className={[
            'container mx-auto px-4',
            step === 'paciente' ? 'max-w-5xl' : 'max-w-2xl',
          ].join(' ')}
        >
          <AgendarCitaStepper current={step} />

          <form
            className={['cita-form-card', step === 'paciente' ? 'cita-form-card--paciente' : ''].filter(Boolean).join(' ')}
            onSubmit={step === 'confirmar' ? onSubmit : (e) => e.preventDefault()}
          >
            <div className="cita-form-card__head">
              <span className="cita-form-card__head-icon">
                <StepIcon size={22} strokeWidth={2} />
              </span>
              <h2 className="cita-form-card__title">{stepTitle(step)}</h2>
            </div>

            {step === 'paciente' ? (
              <DatosPacienteForm
                values={pacienteValues}
                showErrors={showPacienteErrors}
                reniecBusy={reniecBusy}
                onChange={updatePacienteField}
                onConsultarDni={() => {
                  const digits = dni.replace(/\D/g, '');
                  if (digits.length >= 7 && digits.length <= 8) {
                    void autocompletarPorDni(digits);
                  }
                }}
              />
            ) : null}

            {step === 'especialidad' ? (
              <div className="grid gap-5">
                <p className="m-0 text-sm text-muted-foreground">
                  Elige una especialidad (opcional). Si prefieres un médico concreto, selecciónalo abajo.
                </p>
                <div className="cita-especialidad-grid">
                  {opts.length === 0 ? (
                    <p className="muted text-sm">No hay especialidades cargadas.</p>
                  ) : (
                    opts.map((o) => {
                      const selected = especialidad === o.nombre;
                      return (
                        <button
                          key={o.id}
                          type="button"
                          onClick={() => {
                            setEspecialidad(selected ? '' : o.nombre);
                            setMedicoId('');
                          }}
                          className={[
                            'cita-especialidad-card',
                            selected ? 'cita-especialidad-card--selected' : 'cita-especialidad-card--idle',
                          ].join(' ')}
                        >
                          <span className="block font-semibold text-primary">{o.nombre}</span>
                          <span className="mt-1 block text-xs text-muted-foreground">Consultas y procedimientos</span>
                        </button>
                      );
                    })
                  )}
                </div>
                <label className="field">
                  <span className="field__label">Médico preferido (opcional)</span>
                  <select
                    className="field__input"
                    value={medicoId === '' ? '' : String(medicoId)}
                    onChange={(e) => setMedicoId(e.target.value === '' ? '' : Number(e.target.value))}
                  >
                    <option value="">Sin preferencia</option>
                    {medicoOpts.map((m) => (
                      <option key={m.id} value={m.id}>
                        {m.nombre}
                        {m.especialidad?.nombre ? ` — ${m.especialidad.nombre}` : ''}
                      </option>
                    ))}
                  </select>
                </label>
                <label className="field">
                  <span className="field__label">Motivo / comentarios</span>
                  <textarea
                    className="field__input"
                    value={motivo}
                    onChange={(e) => setMotivo(e.target.value)}
                    rows={3}
                    maxLength={1000}
                    placeholder="Cuéntanos brevemente el motivo de la consulta"
                  />
                </label>
              </div>
            ) : null}

            
            {step === 'horario' ? (
              <div className="grid gap-5">
                <p className="m-0 text-sm text-muted-foreground">
                  Indica tu fecha y hora preferida. El equipo confirmará disponibilidad real al contactarte.
                </p>
                <div className="grid gap-5 sm:grid-cols-2">
                  <label className="field">
                    <span className="field__label">Fecha preferida</span>
                    <div className="relative">
                      <Calendar
                        className="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-muted-foreground"
                        size={18}
                        aria-hidden="true"
                      />
                      <input
                        className="field__input !pl-10"
                        type="date"
                        min={today}
                        value={fecha}
                        onChange={(e) => setFecha(e.target.value)}
                      />
                    </div>
                  </label>
                  <label className="field">
                    <span className="field__label">Hora preferida</span>
                    <input
                      className="field__input"
                      type="time"
                      value={hora}
                      onChange={(e) => setHora(e.target.value)}
                    />
                  </label>
                </div>
              </div>
            ) : null}

            {step === 'confirmar' ? (
              <div className="grid gap-4">
                <p className="m-0 text-sm text-muted-foreground">Revisa los datos antes de enviar la solicitud.</p>
                <dl className="divide-y divide-border rounded-xl border border-border bg-secondary/30 text-sm">
                  <ResumenDatosPaciente
                    nombreCompleto={nombreCompleto}
                    dni={dni.replace(/\D/g, '')}
                    detalle={pacienteDetalle}
                    email={email}
                    telefono={telefono}
                    direccion={direccion}
                  />
                  <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                    <dt className="font-semibold text-muted-foreground">Especialidad</dt>
                    <dd className="sm:col-span-2">{especialidad || '—'}</dd>
                  </div>
                  <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                    <dt className="font-semibold text-muted-foreground">Médico</dt>
                    <dd className="sm:col-span-2">{selectedMedico?.nombre ?? 'Sin preferencia'}</dd>
                  </div>
                  <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                    <dt className="font-semibold text-muted-foreground">Horario</dt>
                    <dd className="sm:col-span-2">
                      {fecha || hora
                        ? `${fecha ? new Date(fecha + 'T12:00:00').toLocaleDateString('es-PE') : '—'}${hora ? ` · ${hora}` : ''}`
                        : '—'}
                    </dd>
                  </div>
                  {motivo.trim() ? (
                    <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
                      <dt className="font-semibold text-muted-foreground">Motivo</dt>
                      <dd className="sm:col-span-2">{motivo}</dd>
                    </div>
                  ) : null}
                </dl>
              </div>
            ) : null}

            {msg ? <p className="alert alert--ok mt-4">{msg}</p> : null}
            {hint ? <p className="alert alert--info mt-4">{hint}</p> : null}
            {err ? <p className="alert alert--err mt-4">{err}</p> : null}

            <div className="cita-wizard-nav">
              {step !== 'paciente' ? (
                <div className="cita-wizard-nav__row">
                  <button type="button" className="btn btn--ghost" onClick={goBack} disabled={busy}>
                    <ChevronLeft size={18} /> Atrás
                  </button>
                </div>
              ) : null}
              {step === 'paciente' ? (
                <button
                  type="button"
                  className="cita-btn-next cita-btn-next--enabled"
                  onClick={goNext}
                  disabled={busy}
                >
                  Guardar Datos del Paciente
                </button>
              ) : null}
              {step !== 'confirmar' && step !== 'paciente' ? (
                <button
                  type="button"
                  className={[
                    'cita-btn-next',
                    canGoNext ? 'cita-btn-next--enabled' : 'cita-btn-next--disabled',
                  ].join(' ')}
                  onClick={goNext}
                  disabled={!canGoNext}
                >
                  {nextLabel}
                  <ChevronRight size={18} />
                </button>
              ) : step === 'confirmar' ? (
                <button
                  className="cita-btn-next cita-btn-next--enabled"
                  type="submit"
                  disabled={busy}
                >
                  {busy ? (
                    <>
                      <Loader2 className="animate-spin" size={18} /> Enviando…
                    </>
                  ) : (
                    'Enviar solicitud'
                  )}
                </button>
              ) : null}
            </div>
          </form>
        </div>
      </section>
    </main>
  );
}
