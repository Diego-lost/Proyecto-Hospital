type FieldKey =
  | 'nombre'
  | 'apellido'
  | 'dni'
  | 'sexo'
  | 'email'
  | 'telefono'
  | 'estadoCivil'
  | 'fechaNacimiento'
  | 'direccion'
  | 'lugarNacimiento';

export type DatosPacienteValues = {
  nombre: string;
  apellido: string;
  dni: string;
  sexo: string;
  email: string;
  telefono: string;
  estadoCivil: string;
  fechaNacimiento: string;
  direccion: string;
  lugarNacimiento: string;
  autorizaDatos: boolean;
};

type Props = {
  values: DatosPacienteValues;
  showErrors: boolean;
  reniecBusy: boolean;
  onChange: <K extends keyof DatosPacienteValues>(key: K, value: DatosPacienteValues[K]) => void;
  onConsultarDni: () => void;
};

function fieldClass(key: FieldKey, value: string, showErrors: boolean): string {
  const filled = value.trim() !== '';
  const dniOk = key === 'dni' && /^\d{7,8}$/.test(value.replace(/\D/g, ''));
  const ok = key === 'dni' ? dniOk : filled;
  if (showErrors && !ok) {
    return 'paciente-field__control paciente-field__control--invalid';
  }
  if (ok) {
    return 'paciente-field__control paciente-field__control--valid';
  }
  return 'paciente-field__control';
}

function selectClass(value: string, showErrors: boolean): string {
  const ok = value.trim() !== '';
  if (showErrors && !ok) {
    return 'paciente-field__control paciente-field__control--invalid';
  }
  if (ok) {
    return 'paciente-field__control paciente-field__control--valid';
  }
  return 'paciente-field__control';
}

const LUGARES_NACIMIENTO = [
  'Amazonas',
  'Áncash',
  'Apurímac',
  'Arequipa',
  'Ayacucho',
  'Cajamarca',
  'Callao',
  'Cusco',
  'Huancavelica',
  'Huánuco',
  'Ica',
  'Junín',
  'La Libertad',
  'Lambayeque',
  'Lima',
  'Loreto',
  'Madre de Dios',
  'Moquegua',
  'Pasco',
  'Piura',
  'Puno',
  'San Martín',
  'Tacna',
  'Tumbes',
  'Ucayali',
];

export default function DatosPacienteForm({ values, showErrors, reniecBusy, onChange, onConsultarDni }: Props) {
  const checkboxInvalid = showErrors && !values.autorizaDatos;

  return (
    <div className="paciente-form">
      <div className="paciente-form__grid paciente-form__grid--row1">
        <label className="paciente-field">
          <span className="paciente-field__label">
            Nombre <span className="paciente-field__req">*</span>
          </span>
          <input
            className={fieldClass('nombre', values.nombre, showErrors)}
            value={values.nombre}
            onChange={(e) => onChange('nombre', e.target.value)}
            placeholder="Ingresa tu nombre"
            autoComplete="given-name"
            maxLength={80}
            required
          />
        </label>

        <label className="paciente-field">
          <span className="paciente-field__label">
            Apellido <span className="paciente-field__req">*</span>
          </span>
          <input
            className={fieldClass('apellido', values.apellido, showErrors)}
            value={values.apellido}
            onChange={(e) => onChange('apellido', e.target.value)}
            placeholder="Ingresa tu apellido"
            autoComplete="family-name"
            maxLength={80}
            required
          />
        </label>

        <label className="paciente-field">
          <span className="paciente-field__label">
            Número de Documento <span className="paciente-field__req">*</span>
          </span>
          <input
            className={fieldClass('dni', values.dni, showErrors)}
            value={values.dni}
            onChange={(e) => onChange('dni', e.target.value)}
            onBlur={() => {
              const digits = values.dni.replace(/\D/g, '');
              if (digits.length >= 7 && digits.length <= 8) {
                onConsultarDni();
              }
            }}
            inputMode="numeric"
            autoComplete="off"
            placeholder="DNI"
            maxLength={8}
            required
          />
          {reniecBusy ? (
            <span className="paciente-field__hint">Consultando RENIEC…</span>
          ) : null}
        </label>

        <label className="paciente-field">
          <span className="paciente-field__label">
            Sexo <span className="paciente-field__req">*</span>
          </span>
          <select
            className={selectClass(values.sexo, showErrors)}
            value={values.sexo}
            onChange={(e) => onChange('sexo', e.target.value)}
            required
          >
            <option value="">Selecciona una opción</option>
            <option value="masculino">Masculino</option>
            <option value="femenino">Femenino</option>
            <option value="otro">Otro</option>
          </select>
        </label>
      </div>

      <div className="paciente-form__grid paciente-form__grid--row2">
        <label className="paciente-field paciente-field--wide">
          <span className="paciente-field__label">
            Email <span className="paciente-field__req">*</span>
          </span>
          <input
            className={fieldClass('email', values.email, showErrors)}
            type="email"
            value={values.email}
            onChange={(e) => onChange('email', e.target.value)}
            placeholder="Ingresa tu email"
            autoComplete="email"
            maxLength={160}
            required
          />
        </label>

        <label className="paciente-field">
          <span className="paciente-field__label">
            Teléfono <span className="paciente-field__req">*</span>
          </span>
          <input
            className={fieldClass('telefono', values.telefono, showErrors)}
            type="tel"
            value={values.telefono}
            onChange={(e) => onChange('telefono', e.target.value)}
            placeholder="Ingresa tu número de teléfono"
            autoComplete="tel"
            maxLength={40}
            required
          />
        </label>

        <label className="paciente-field">
          <span className="paciente-field__label">
            Estado Civil <span className="paciente-field__req">*</span>
          </span>
          <select
            className={selectClass(values.estadoCivil, showErrors)}
            value={values.estadoCivil}
            onChange={(e) => onChange('estadoCivil', e.target.value)}
            required
          >
            <option value="">Selecciona una opción</option>
            <option value="soltero">Soltero/a</option>
            <option value="casado">Casado/a</option>
            <option value="divorciado">Divorciado/a</option>
            <option value="viudo">Viudo/a</option>
            <option value="conviviente">Conviviente</option>
          </select>
        </label>
      </div>

      <div className="paciente-form__grid paciente-form__grid--row3">
        <label className="paciente-field">
          <span className="paciente-field__label">
            Fecha de nacimiento <span className="paciente-field__req">*</span>
          </span>
          <input
            className={fieldClass('fechaNacimiento', values.fechaNacimiento, showErrors)}
            type="date"
            value={values.fechaNacimiento}
            onChange={(e) => onChange('fechaNacimiento', e.target.value)}
            max={new Date().toISOString().slice(0, 10)}
            required
          />
        </label>

        <label className="paciente-field paciente-field--wide">
          <span className="paciente-field__label">
            Dirección <span className="paciente-field__req">*</span>
          </span>
          <input
            className={fieldClass('direccion', values.direccion, showErrors)}
            value={values.direccion}
            onChange={(e) => onChange('direccion', e.target.value)}
            placeholder="Dirección"
            autoComplete="street-address"
            maxLength={500}
            required
          />
        </label>

        <label className="paciente-field">
          <span className="paciente-field__label">
            Lugar de Nacimiento <span className="paciente-field__req">*</span>
          </span>
          <select
            className={selectClass(values.lugarNacimiento, showErrors)}
            value={values.lugarNacimiento}
            onChange={(e) => onChange('lugarNacimiento', e.target.value)}
            required
          >
            <option value="">Selecciona una opción</option>
            {LUGARES_NACIMIENTO.map((dep) => (
              <option key={dep} value={dep}>
                {dep}
              </option>
            ))}
          </select>
        </label>
      </div>

      <label
        className={[
          'paciente-consent',
          checkboxInvalid ? 'paciente-consent--invalid' : '',
        ]
          .filter(Boolean)
          .join(' ')}
      >
        <input
          type="checkbox"
          checked={values.autorizaDatos}
          onChange={(e) => onChange('autorizaDatos', e.target.checked)}
          className="paciente-consent__check"
        />
        <span>Autorizo el uso de mis datos personales</span>
      </label>
    </div>
  );
}

export function splitNombreCompleto(full: string): { nombres: string; apellidos: string } {
  const parts = full.trim().split(/\s+/).filter(Boolean);
  if (parts.length === 0) {
    return { nombres: '', apellidos: '' };
  }
  if (parts.length === 1) {
    return { nombres: parts[0]!, apellidos: '' };
  }
  if (parts.length === 2) {
    return { nombres: parts[0]!, apellidos: parts[1]! };
  }
  return {
    nombres: parts.slice(0, -2).join(' '),
    apellidos: parts.slice(-2).join(' '),
  };
}

export function buildNombreCompleto(nombre: string, apellido: string): string {
  return `${nombre.trim()} ${apellido.trim()}`.trim();
}

export type PacienteDetalle = {
  nombre: string;
  apellido: string;
  sexo: string;
  estado_civil: string;
  fecha_nacimiento: string;
  lugar_nacimiento: string;
};

export function buildPacienteDetalle(values: DatosPacienteValues): PacienteDetalle {
  return {
    nombre: values.nombre.trim(),
    apellido: values.apellido.trim(),
    sexo: values.sexo.trim(),
    estado_civil: values.estadoCivil.trim(),
    fecha_nacimiento: values.fechaNacimiento.trim(),
    lugar_nacimiento: values.lugarNacimiento.trim(),
  };
}

const SEXO_LABELS: Record<string, string> = {
  masculino: 'Masculino',
  femenino: 'Femenino',
  otro: 'Otro',
};

const ESTADO_CIVIL_LABELS: Record<string, string> = {
  soltero: 'Soltero/a',
  casado: 'Casado/a',
  divorciado: 'Divorciado/a',
  viudo: 'Viudo/a',
  conviviente: 'Conviviente',
};

export function labelSexo(value: string): string {
  return SEXO_LABELS[value] ?? (value || '—');
}

export function labelEstadoCivil(value: string): string {
  return ESTADO_CIVIL_LABELS[value] ?? (value || '—');
}

export function formatFechaNacimiento(iso: string): string {
  if (!iso.trim()) {
    return '—';
  }
  const d = new Date(`${iso}T12:00:00`);
  if (Number.isNaN(d.getTime())) {
    return iso;
  }
  return d.toLocaleDateString('es-PE', { day: '2-digit', month: 'long', year: 'numeric' });
}

type ResumenProps = {
  nombreCompleto: string;
  dni: string;
  detalle: PacienteDetalle;
  email: string;
  telefono: string;
  direccion: string;
};

export function ResumenDatosPaciente({
  nombreCompleto,
  dni,
  detalle,
  email,
  telefono,
  direccion,
}: ResumenProps) {
  return (
    <>
      <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
        <dt className="font-semibold text-muted-foreground">Nombre completo</dt>
        <dd className="sm:col-span-2">{nombreCompleto}</dd>
      </div>
      <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
        <dt className="font-semibold text-muted-foreground">DNI</dt>
        <dd className="sm:col-span-2">{dni}</dd>
      </div>
      <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
        <dt className="font-semibold text-muted-foreground">Sexo</dt>
        <dd className="sm:col-span-2">{labelSexo(detalle.sexo)}</dd>
      </div>
      <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
        <dt className="font-semibold text-muted-foreground">Estado civil</dt>
        <dd className="sm:col-span-2">{labelEstadoCivil(detalle.estado_civil)}</dd>
      </div>
      <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
        <dt className="font-semibold text-muted-foreground">Fecha de nacimiento</dt>
        <dd className="sm:col-span-2">{formatFechaNacimiento(detalle.fecha_nacimiento)}</dd>
      </div>
      <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
        <dt className="font-semibold text-muted-foreground">Lugar de nacimiento</dt>
        <dd className="sm:col-span-2">{detalle.lugar_nacimiento || '—'}</dd>
      </div>
      <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
        <dt className="font-semibold text-muted-foreground">Email</dt>
        <dd className="sm:col-span-2">{email}</dd>
      </div>
      <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
        <dt className="font-semibold text-muted-foreground">Teléfono</dt>
        <dd className="sm:col-span-2">{telefono}</dd>
      </div>
      <div className="grid gap-1 px-4 py-3 sm:grid-cols-3">
        <dt className="font-semibold text-muted-foreground">Dirección</dt>
        <dd className="sm:col-span-2">{direccion}</dd>
      </div>
    </>
  );
}
