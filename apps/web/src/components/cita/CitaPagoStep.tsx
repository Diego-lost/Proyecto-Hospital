import { useCallback, useEffect, useMemo, useState } from 'react';
import {
  Building2,
  Check,
  CreditCard,
  Lock,
  ShoppingBag,
  Smartphone,
  Stethoscope,
} from 'lucide-react';
import { isViteApiBaseConfigured } from '../../api';
import { excerpt, formatMoney } from '../../lib/catalogUtils';
import { fetchPagoConfig, type PagoConfig } from '../../lib/payments';
import { fetchServiciosForPago } from '../../lib/remoteCatalog';
import type { ServicioRow } from '../../types/catalogRows';

export type CitaMetodoPago = 'tarjeta' | 'yape' | 'transferencia';

const METODOS: {
  id: CitaMetodoPago;
  title: string;
  subtitle: string;
  icon: typeof CreditCard;
}[] = [
  {
    id: 'tarjeta',
    title: 'Tarjeta de crédito/débito',
    subtitle: 'Visa, Mastercard, American Express.',
    icon: CreditCard,
  },
  {
    id: 'yape',
    title: 'Yape / Plin',
    subtitle: 'Pago desde tu celular.',
    icon: Smartphone,
  },
  {
    id: 'transferencia',
    title: 'Transferencia bancaria',
    subtitle: 'Depósito o transferencia interbancaria.',
    icon: Building2,
  },
];

export type CitaPagoRequest = {
  servicioId: number;
  metodo: CitaMetodoPago;
  referencia?: string;
};

type Props = {
  nombreCompleto: string;
  email: string;
  telefono: string;
  medicoId: number | '';
  busy: boolean;
  onError: (message: string | null) => void;
  onPay: (request: CitaPagoRequest) => Promise<void>;
};

export default function CitaPagoStep({
  nombreCompleto,
  email,
  telefono: _telefono,
  medicoId,
  busy,
  onError,
  onPay,
}: Props) {
  const [servicios, setServicios] = useState<ServicioRow[]>([]);
  const [pagoConfig, setPagoConfig] = useState<PagoConfig | null>(null);
  const [loading, setLoading] = useState(true);
  const [stripeOk, setStripeOk] = useState(false);
  const [servicioId, setServicioId] = useState('');
  const [metodo, setMetodo] = useState<CitaMetodoPago>('tarjeta');
  const [referencia, setReferencia] = useState('');

  const adminFee = pagoConfig?.admin_fee ?? 0;
  const manualInfo = pagoConfig?.manual;

  const load = useCallback(async () => {
    setLoading(true);
    onError(null);
    try {
      if (!isViteApiBaseConfigured()) {
        onError('El pago en línea no está disponible en este momento. Inténtalo más tarde o paga en recepción.');
        return;
      }
      const medicoFilter = medicoId === '' ? undefined : medicoId;
      const [list, cfg] = await Promise.all([
        fetchServiciosForPago(medicoFilter),
        fetchPagoConfig(),
      ]);
      const rows = Array.isArray(list) ? list : [];
      setServicios(rows);
      setPagoConfig(cfg);
      setStripeOk(cfg.stripe_configured);
      if (rows.length === 1) {
        setServicioId(String(rows[0]!.id));
      }
    } catch (e) {
      onError(e instanceof Error ? e.message : 'No se pudo cargar los servicios.');
    } finally {
      setLoading(false);
    }
  }, [medicoId, onError]);

  useEffect(() => {
    void load();
  }, [load]);

  const selected = useMemo(
    () => servicios.find((s) => String(s.id) === servicioId) ?? null,
    [servicios, servicioId],
  );

  const subtotal = selected ? Number(selected.precio) : 0;
  const total = subtotal + adminFee;
  const payable = selected !== null && subtotal > 0;
  const datosOk = nombreCompleto.trim().length > 0 && email.trim().includes('@');
  const canPayTarjeta = metodo === 'tarjeta' && stripeOk && payable && datosOk;
  const canPayManual = (metodo === 'yape' || metodo === 'transferencia') && payable && datosOk;
  const canSubmit = canPayTarjeta || canPayManual;

  async function handlePayClick() {
    if (!payable || !servicioId) {
      onError('Selecciona un servicio con precio mayor a cero.');
      return;
    }
    if (!datosOk) {
      onError('Indica nombre y correo válidos en los datos del paciente.');
      return;
    }
    if (metodo === 'tarjeta' && !stripeOk) {
      onError('Los pagos con tarjeta no están activos. Elige Yape o transferencia.');
      return;
    }

    onError(null);
    await onPay({
      servicioId: Number(servicioId),
      metodo,
      referencia: referencia.trim() || undefined,
    });
  }

  if (loading) {
    return <p className="text-center text-sm text-muted-foreground">Cargando servicios y métodos de pago…</p>;
  }

  return (
    <div className="grid gap-6">
      <p className="m-0 text-sm text-muted-foreground">
        Elige el servicio a pagar y el método de pago. Al confirmar, tu solicitud de cita quedará registrada.
      </p>

      {metodo === 'tarjeta' && !stripeOk && (
        <p className="pago-alert pago-alert--err" role="alert">
          Los pagos con tarjeta no están activos. Configura STRIPE_SECRET en el servidor o elige Yape /
          transferencia.
        </p>
      )}

      <div className="pago-card">
        <h3 className="pago-card__title">Servicio</h3>
        <label className="field">
          <span className="field__label">Consulta o procedimiento</span>
          <select
            className="field__input"
            value={servicioId}
            onChange={(ev) => setServicioId(ev.target.value)}
            required
            disabled={busy}
          >
            <option value="">— Selecciona —</option>
            {servicios.map((s) => (
              <option key={s.id} value={s.id}>
                {s.nombre} — {formatMoney(s.precio)}
              </option>
            ))}
          </select>
        </label>

        {selected ? (
          <div className="pago-servicio-box mt-4">
            <Stethoscope size={20} className="text-accent" aria-hidden="true" />
            <div>
              <p className="pago-servicio-box__name">{selected.nombre}</p>
              {selected.medico?.nombre && (
                <p className="pago-servicio-box__meta">Dr(a). {selected.medico.nombre}</p>
              )}
              {selected.descripcion && (
                <p className="pago-servicio-box__meta">{excerpt(selected.descripcion, 80)}</p>
              )}
            </div>
          </div>
        ) : null}
      </div>

      <div className="pago-card">
        <h3 className="pago-card__title">Método de pago</h3>
        <div className="pago-metodos" role="radiogroup" aria-label="Método de pago">
          {METODOS.map(({ id, title, subtitle, icon: Icon }) => {
            const active = metodo === id;
            return (
              <button
                key={id}
                type="button"
                role="radio"
                aria-checked={active}
                className={`pago-metodo${active ? ' pago-metodo--active' : ''}`}
                onClick={() => setMetodo(id)}
                disabled={busy}
              >
                <span className="pago-metodo__icon" aria-hidden="true">
                  <Icon size={22} />
                </span>
                <span className="pago-metodo__text">
                  <span className="pago-metodo__title">{title}</span>
                  <span className="pago-metodo__desc">{subtitle}</span>
                </span>
                {active && (
                  <span className="pago-metodo__check" aria-hidden="true">
                    <Check size={18} strokeWidth={3} />
                  </span>
                )}
              </button>
            );
          })}
        </div>

        {metodo === 'tarjeta' && (
          <div className="pago-tarjeta-panel">
            <p className="pago-card-hint">
              Al continuar, completarás el pago en la pasarela segura de Stripe con el correo{' '}
              <strong>{email || 'indicado en tus datos'}</strong>.
            </p>
            <div className="pago-secure-box">
              <Lock size={18} className="shrink-0 text-accent" aria-hidden="true" />
              <p>No guardamos datos de tarjeta. El cobro se realiza en Stripe.</p>
            </div>
          </div>
        )}

        {metodo === 'yape' && (
          <div className="pago-alt-panel">
            <p className="pago-alt-panel__lead">
              Realiza el pago por Yape o Plin y registra tu solicitud para que la clínica la valide.
            </p>
            <p className="pago-alt-panel__amount">
              Monto a pagar: <strong>{payable ? formatMoney(total) : '—'}</strong>
            </p>
            <ul className="pago-alt-panel__steps">
              <li>Abre Yape o Plin en tu celular.</li>
              <li>
                Envía el monto exacto al número{' '}
                <strong>{manualInfo?.yape_phone ?? '(01) 123-4567'}</strong>.
              </li>
              <li>Indica el número de operación abajo (opcional).</li>
            </ul>
          </div>
        )}

        {metodo === 'transferencia' && (
          <div className="pago-alt-panel">
            <p className="pago-alt-panel__lead">
              Realiza la transferencia y registra tu solicitud con los datos de la operación.
            </p>
            <p className="pago-alt-panel__amount">
              Monto a pagar: <strong>{payable ? formatMoney(total) : '—'}</strong>
            </p>
            {manualInfo?.bank ? (
              <dl className="pago-bank-dl">
                <dt>Banco</dt>
                <dd>{manualInfo.bank.nombre}</dd>
                <dt>Cuenta</dt>
                <dd>{manualInfo.bank.cuenta}</dd>
                <dt>Titular</dt>
                <dd>{manualInfo.bank.titular}</dd>
                <dt>CCI</dt>
                <dd>{manualInfo.bank.cci}</dd>
              </dl>
            ) : null}
          </div>
        )}

        {(metodo === 'yape' || metodo === 'transferencia') && (
          <div className="field mt-4">
            <label htmlFor="cita-pago-ref">Nº de operación / referencia (opcional)</label>
            <input
              id="cita-pago-ref"
              className="field__input"
              value={referencia}
              onChange={(ev) => setReferencia(ev.target.value)}
              placeholder="Ej. 202605261234"
              maxLength={120}
              disabled={busy}
            />
          </div>
        )}
      </div>

      <dl className="pago-totales">
        <div className="pago-totales__row">
          <dt>Subtotal</dt>
          <dd>{payable ? formatMoney(subtotal) : '—'}</dd>
        </div>
        <div className="pago-totales__row">
          <dt>Cargos administrativos</dt>
          <dd>{formatMoney(adminFee)}</dd>
        </div>
        <div className="pago-totales__row pago-totales__row--total">
          <dt>Total</dt>
          <dd>{payable ? formatMoney(total) : '—'}</dd>
        </div>
      </dl>

      <button
        type="button"
        className="pago-btn-pay w-full"
        disabled={busy || !canSubmit}
        onClick={() => void handlePayClick()}
      >
        <ShoppingBag size={18} aria-hidden="true" />
        {busy
          ? metodo === 'tarjeta'
            ? 'Registrando cita y redirigiendo a Stripe…'
            : 'Registrando…'
          : metodo === 'tarjeta'
            ? payable
              ? `Pagar ${formatMoney(total)} y confirmar cita`
              : 'Pagar y confirmar cita'
            : payable
              ? `Registrar pago — ${formatMoney(total)}`
              : 'Registrar pago y confirmar cita'}
      </button>
    </div>
  );
}
