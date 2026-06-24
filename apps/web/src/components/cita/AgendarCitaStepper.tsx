import { Calendar, Check, CreditCard, Stethoscope, User } from 'lucide-react';

export type CitaWizardStep = 'paciente' | 'especialidad' | 'horario' | 'confirmar' | 'pago';

const STEPS: { id: CitaWizardStep; label: string; icon: typeof User }[] = [
  { id: 'paciente', label: 'Paciente', icon: User },
  { id: 'especialidad', label: 'Especialidad', icon: Stethoscope },
  { id: 'horario', label: 'Horario', icon: Calendar },
  { id: 'confirmar', label: 'Confirmar', icon: Check },
  { id: 'pago', label: 'Pago', icon: CreditCard },
];

export function stepIndex(step: CitaWizardStep): number {
  return STEPS.findIndex((s) => s.id === step);
}

type Props = {
  current: CitaWizardStep;
};

export default function AgendarCitaStepper({ current }: Props) {
  const activeIdx = stepIndex(current);

  return (
    <nav className="cita-stepper" aria-label="Pasos para agendar cita">
      <ol className="cita-stepper__list">
        {STEPS.map((s, i) => {
          const Icon = s.icon;
          const done = i < activeIdx;
          const active = i === activeIdx;
          return (
            <li key={s.id} className="cita-stepper__item">
              <div className="cita-stepper__node">
                <span
                  className={[
                    'cita-stepper__circle',
                    done ? 'cita-stepper__circle--done' : '',
                    active ? 'cita-stepper__circle--active' : '',
                  ]
                    .filter(Boolean)
                    .join(' ')}
                  aria-current={active ? 'step' : undefined}
                >
                  {done ? <Check size={20} strokeWidth={2.5} /> : <Icon size={20} strokeWidth={2} />}
                </span>
                <span
                  className={[
                    'cita-stepper__label',
                    active || done ? 'cita-stepper__label--on' : '',
                  ]
                    .filter(Boolean)
                    .join(' ')}
                >
                  {s.label}
                </span>
              </div>
              {i < STEPS.length - 1 ? (
                <div
                  className={['cita-stepper__line', i < activeIdx ? 'cita-stepper__line--done' : ''].filter(Boolean).join(' ')}
                  aria-hidden="true"
                />
              ) : null}
            </li>
          );
        })}
      </ol>
    </nav>
  );
}

export function stepTitle(step: CitaWizardStep): string {
  switch (step) {
    case 'paciente':
      return 'Datos del Paciente';
    case 'especialidad':
      return 'Especialidad';
    case 'horario':
      return 'Horario preferido';
    case 'confirmar':
      return 'Confirmar solicitud';
    case 'pago':
      return 'Pago de la consulta';
  }
}

export function stepIcon(step: CitaWizardStep) {
  switch (step) {
    case 'paciente':
      return User;
    case 'especialidad':
      return Stethoscope;
    case 'horario':
      return Calendar;
    case 'confirmar':
      return Check;
    case 'pago':
      return CreditCard;
  }
}
