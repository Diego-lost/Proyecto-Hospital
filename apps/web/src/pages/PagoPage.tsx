import { FormEvent, useCallback, useEffect, useMemo, useState } from 'react';
import { Link, useNavigate, useParams, useSearchParams } from 'react-router-dom';
import {
  ArrowLeft,
  Building2,
  Check,
  CreditCard,
  Lock,
  Mail,
  Phone,
  Shield,
  ShoppingBag,
  Smartphone,
  Stethoscope,
} from 'lucide-react';
import PagoHero from '../components/pago/PagoHero';
import { isViteApiBaseConfigured } from '../api';
import { excerpt, formatMoney } from '../lib/catalogUtils';
import {
  createCheckoutSession,
  fetchPagoConfig,
  registrarPagoManual,
  type PagoConfig,
} from '../lib/payments';
import { fetchServiciosForPago } from '../lib/remoteCatalog';
import type { ServicioRow } from '../types/catalogRows';

type MetodoPago = 'tarjeta' | 'yape' | 'transferencia';

const CARD_BRANDS = ['Visa', 'Mastercard', 'Amex'] as const;

const METODOS: {
  id: MetodoPago;
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

export default function PagoPage() {
  const navigate = useNavigate();
  const { servicioId: servicioIdParam } = useParams();
  const [searchParams] = useSearchParams();
  const preselectId = servicioIdParam ?? searchParams.get('servicio');

  const [servicios, setServicios] = useState<ServicioRow[]>([]);
  const [pagoConfig, setPagoConfig] = useState<PagoConfig | null>(null);
  const [loading, setLoading] = useState(true);
  const [stripeOk, setStripeOk] = useState(false);
  const [servicioId, setServicioId] = useState('');
  const [metodo, setMetodo] = useState<MetodoPago>('tarjeta');
  const [cardBrand, setCardBrand] = useState<(typeof CARD_BRANDS)[number]>('Visa');
  const [cardNumber, setCardNumber] = useState('');
  const [cardExpiry, setCardExpiry] = useState('');
  const [cardCvv, setCardCvv] = useState('');
  const [nombre, setNombre] = useState('');
  const [email, setEmail] = useState('');
  const [telefono, setTelefono] = useState('');
  const [referencia, setReferencia] = useState('');
  const [busy, setBusy] = useState(false);
  const [err, setErr] = useState<string | null>(null);

  const adminFee = pagoConfig?.admin_fee ?? 0;
  const manualInfo = pagoConfig?.manual;

  const load = useCallback(async () => {
    setLoading(true);
    setErr(null);
    try {
      if (!isViteApiBaseConfigured()) {
        setErr('El pago en línea no está disponible en este momento. Inténtalo más tarde o paga en recepción.');
        return;
      }
      const [list, cfg] = await Promise.all([fetchServiciosForPago(), fetchPagoConfig()]);
      setServicios(Array.isArray(list) ? list : []);
      setPagoConfig(cfg);
      setStripeOk(cfg.stripe_configured);
    } catch (e) {
      setErr(e instanceof Error ? e.message : 'No se pudo cargar el catálogo.');
    } finally {
      setLoading(false);
    }
  }, []);

  useEffect(() => {
    void load();
  }, [load]);

  useEffect(() => {
    if (preselectId && servicios.length > 0) {
      const found = servicios.some((s) => String(s.id) === preselectId);
      if (found) {
        setServicioId(preselectId);
      }
    }
  }, [preselectId, servicios]);

  const selected = useMemo(
    () => servicios.find((s) => String(s.id) === servicioId) ?? null,
    [servicios, servicioId],
  );

  const subtotal = selected ? Number(selected.precio) : 0;
  const total = subtotal + adminFee;
  const payable = selected !== null && subtotal > 0;
  const datosOk = nombre.trim().length > 0 && email.trim().includes('@');
  const canPayTarjeta = metodo === 'tarjeta' && stripeOk && payable && datosOk;
  const canPayManual = (metodo === 'yape' || metodo === 'transferencia') && payable && datosOk;
  const canSubmit = canPayTarjeta || canPayManual;

  function formatCardNumber(raw: string) {
    const digits = raw.replace(/\D/g, '').slice(0, 16);
    return digits.replace(/(\d{4})(?=\d)/g, '$1 ').trim();
  }

  function formatExpiry(raw: string) {
    const digits = raw.replace(/\D/g, '').slice(0, 4);
    if (digits.length <= 2) return digits;
    return `${digits.slice(0, 2)}/${digits.slice(2)}`;
  }

  async function onSubmit(e: FormEvent) {
    e.preventDefault();
    if (!payable || !servicioId) {
      setErr('Selecciona un servicio con precio mayor a cero.');
      return;
    }
    if (!datosOk) {
      setErr('Indica nombre y correo válidos.');
      return;
    }

    const payload = {
      servicio_id: Number(servicioId),
      cliente_nombre: nombre.trim(),
      cliente_email: email.trim(),
      cliente_telefono: telefono.trim() || undefined,
    };

    setBusy(true);
    setErr(null);

    try {
      if (metodo === 'tarjeta') {
        if (!stripeOk) {
          setErr('Los pagos con tarjeta no están activos (STRIPE_SECRET en el servidor).');
          return;
        }
        const res = await createCheckoutSession(payload);
        if (res.checkout_url) {
          window.location.href = res.checkout_url;
          return;
        }
        setErr('No se recibió la URL de pago de Stripe.');
        return;
      }

      const res = await registrarPagoManual({
        ...payload,
        metodo,
        referencia_manual: referencia.trim() || undefined,
      });
      navigate(`/pago/registrado?pago_id=${res.pago_id}`);
    } catch (ex) {
      setErr(ex instanceof Error ? ex.message : 'Error al procesar el pago.');
    } finally {
      setBusy(false);
    }
  }

  return (
    <main id="contenido" className="pago-page">
      <PagoHero />

      <section className="pago-page__body">
        <div className="mx-auto max-w-7xl px-4 py-8 md:px-6 md:py-10">
          {loading ? (
            <p className="text-center text-muted-foreground">Cargando servicios…</p>
          ) : (
            <form className="pago-grid" onSubmit={(ev) => void onSubmit(ev)}>
              <div className="pago-grid__main">
                {metodo === 'tarjeta' && !stripeOk && (
                  <p className="pago-alert pago-alert--err" role="alert">
                    Los pagos con tarjeta no están activos. Configura STRIPE_SECRET en el servidor o elige Yape /
                    transferencia.
                  </p>
                )}

                <div className="pago-card">
                  <h2 className="pago-card__title">Método de pago</h2>
                  <p className="pago-card__subtitle">Elige cómo deseas pagar.</p>

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
                      <div className="pago-brands" role="group" aria-label="Red de tarjeta">
                        {CARD_BRANDS.map((brand) => (
                          <button
                            key={brand}
                            type="button"
                            className={`pago-brand-chip${cardBrand === brand ? ' pago-brand-chip--active' : ''}`}
                            onClick={() => setCardBrand(brand)}
                            disabled={busy || !stripeOk}
                          >
                            {brand}
                          </button>
                        ))}
                      </div>

                      <div className="field">
                        <label htmlFor="pago-card">Número de tarjeta</label>
                        <input
                          id="pago-card"
                          inputMode="numeric"
                          value={cardNumber}
                          onChange={(ev) => setCardNumber(formatCardNumber(ev.target.value))}
                          placeholder="1234 5678 9012 3456"
                          maxLength={19}
                          disabled={busy || !stripeOk}
                          autoComplete="cc-number"
                          aria-describedby="pago-card-hint"
                        />
                      </div>

                      <div className="field">
                        <label htmlFor="pago-nombre">Nombre del titular</label>
                        <input
                          id="pago-nombre"
                          value={nombre}
                          onChange={(ev) => setNombre(ev.target.value)}
                          placeholder="COMO APARECE EN LA TARJETA"
                          required={canPayTarjeta}
                          maxLength={120}
                          disabled={busy || !stripeOk}
                          autoComplete="cc-name"
                        />
                      </div>

                      <div className="pago-field-row">
                        <div className="field">
                          <label htmlFor="pago-expiry">Expiración (MM/AA)</label>
                          <input
                            id="pago-expiry"
                            inputMode="numeric"
                            value={cardExpiry}
                            onChange={(ev) => setCardExpiry(formatExpiry(ev.target.value))}
                            placeholder="MM/AA"
                            maxLength={5}
                            disabled={busy || !stripeOk}
                            autoComplete="cc-exp"
                          />
                        </div>
                        <div className="field">
                          <label htmlFor="pago-cvv">CVV</label>
                          <input
                            id="pago-cvv"
                            inputMode="numeric"
                            type="password"
                            value={cardCvv}
                            onChange={(ev) => setCardCvv(ev.target.value.replace(/\D/g, '').slice(0, 4))}
                            placeholder="•••"
                            maxLength={4}
                            disabled={busy || !stripeOk}
                            autoComplete="cc-csc"
                          />
                        </div>
                      </div>

                      <p id="pago-card-hint" className="pago-card-hint">
                        Al continuar, completarás el pago en la pasarela segura de Stripe (no guardamos datos de
                        tarjeta).
                      </p>

                      <div className="pago-field-row">
                        <div className="field">
                          <label htmlFor="pago-email">Correo electrónico</label>
                          <input
                            id="pago-email"
                            type="email"
                            value={email}
                            onChange={(ev) => setEmail(ev.target.value)}
                            placeholder="correo@ejemplo.com"
                            required={canPayTarjeta}
                            maxLength={160}
                            disabled={busy || !stripeOk}
                            autoComplete="email"
                          />
                        </div>
                        <div className="field">
                          <label htmlFor="pago-tel">Teléfono (opcional)</label>
                          <input
                            id="pago-tel"
                            type="tel"
                            value={telefono}
                            onChange={(ev) => setTelefono(ev.target.value)}
                            placeholder="999 999 999"
                            maxLength={40}
                            disabled={busy || !stripeOk}
                            autoComplete="tel"
                          />
                        </div>
                      </div>

                      <div className="pago-secure-box">
                        <Lock size={18} className="shrink-0 text-accent" aria-hidden="true" />
                        <p>
                          Tu información está protegida con encriptación de 256 bits. Los datos de tarjeta se
                          ingresan en la pasarela segura de Stripe; no los almacenamos.
                        </p>
                      </div>
                    </div>
                  )}

                  {(metodo === 'yape' || metodo === 'transferencia') && (
                    <div className="pago-manual-datos">
                      <div className="field">
                        <label htmlFor="pago-nombre-manual">Nombre completo</label>
                        <input
                          id="pago-nombre-manual"
                          value={nombre}
                          onChange={(ev) => setNombre(ev.target.value)}
                          required={canPayManual}
                          maxLength={120}
                          disabled={busy}
                          autoComplete="name"
                        />
                      </div>
                      <div className="pago-field-row">
                        <div className="field">
                          <label htmlFor="pago-email-manual">Correo electrónico</label>
                          <input
                            id="pago-email-manual"
                            type="email"
                            value={email}
                            onChange={(ev) => setEmail(ev.target.value)}
                            required={canPayManual}
                            maxLength={160}
                            disabled={busy}
                            autoComplete="email"
                          />
                        </div>
                        <div className="field">
                          <label htmlFor="pago-tel-manual">Teléfono</label>
                          <input
                            id="pago-tel-manual"
                            type="tel"
                            value={telefono}
                            onChange={(ev) => setTelefono(ev.target.value)}
                            maxLength={40}
                            disabled={busy}
                            autoComplete="tel"
                          />
                        </div>
                      </div>
                      <div className="field">
                        <label htmlFor="pago-ref">Nº de operación / referencia (opcional)</label>
                        <input
                          id="pago-ref"
                          value={referencia}
                          onChange={(ev) => setReferencia(ev.target.value)}
                          placeholder="Ej. 202605261234"
                          maxLength={120}
                          disabled={busy}
                        />
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
                        <li>
                          Pulsa «Registrar solicitud» y, si puedes, escribe a{' '}
                          <strong>{manualInfo?.pagos_email ?? 'pagos@novasalud.pe'}</strong> con tu comprobante.
                        </li>
                      </ul>
                    </div>
                  )}

                  {metodo === 'transferencia' && (
                    <div className="pago-alt-panel">
                      <p className="pago-alt-panel__lead">
                        Transfiere el monto indicado a nuestra cuenta y envía el voucher por correo.
                      </p>
                      <dl className="pago-bank-dl">
                        <div>
                          <dt>Banco</dt>
                          <dd>{manualInfo?.bank?.nombre ?? 'Banco de Crédito del Perú (BCP)'}</dd>
                        </div>
                        <div>
                          <dt>Cuenta soles</dt>
                          <dd>{manualInfo?.bank?.cuenta ?? '191-12345678-0-12'}</dd>
                        </div>
                        <div>
                          <dt>Titular</dt>
                          <dd>{manualInfo?.bank?.titular ?? 'Clínica NovaSalud S.A.C.'}</dd>
                        </div>
                        <div>
                          <dt>CCI</dt>
                          <dd>{manualInfo?.bank?.cci ?? '002-191-001234567812-12'}</dd>
                        </div>
                      </dl>
                      <p className="pago-alt-panel__amount">
                        Monto: <strong>{payable ? formatMoney(total) : '—'}</strong>
                      </p>
                      <p className="pago-alt-panel__note">
                        Asunto del correo: «Pago {selected?.nombre ?? 'servicio'} — [tu nombre]».
                      </p>
                    </div>
                  )}
                </div>
              </div>

              <aside className="pago-grid__aside">
                <div className="pago-card pago-resumen">
                  <h2 className="pago-card__title">Resumen de pago</h2>

                  <div className="field" style={{ marginBottom: 16 }}>
                    <label htmlFor="pago-servicio">Servicio</label>
                    <select
                      id="pago-servicio"
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
                  </div>

                  {selected ? (
                    <div className="pago-servicio-box">
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
                  ) : (
                    <div className="pago-servicio-box pago-servicio-box--empty">
                      <Stethoscope size={20} className="text-muted-foreground" aria-hidden="true" />
                      <p className="pago-servicio-box__meta">Selecciona un servicio para ver el detalle.</p>
                    </div>
                  )}

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

                  {err && (
                    <p className="pago-alert pago-alert--err" role="alert">
                      {err}
                    </p>
                  )}

                  <button type="submit" className="pago-btn-pay" disabled={busy || !canSubmit}>
                    <ShoppingBag size={18} aria-hidden="true" />
                    {busy
                      ? metodo === 'tarjeta'
                        ? 'Redirigiendo a Stripe…'
                        : 'Registrando…'
                      : metodo === 'tarjeta'
                        ? payable
                          ? `Pagar ${formatMoney(total)}`
                          : 'Pagar'
                        : payable
                          ? `Registrar solicitud — ${formatMoney(total)}`
                          : 'Registrar solicitud'}
                  </button>

                  <Link to="/cita" className="pago-btn-back">
                    <ArrowLeft size={16} aria-hidden="true" />
                    Volver
                  </Link>

                  <div className="pago-trust">
                    <span>
                      <Shield size={14} aria-hidden="true" />
                      Pago seguro
                    </span>
                    <span>
                      <Lock size={14} aria-hidden="true" />
                      SSL 256-bit
                    </span>
                  </div>
                </div>

                <div className="pago-card pago-ayuda">
                  <h3 className="pago-ayuda__title">¿Necesitas ayuda?</h3>
                  <p className="pago-ayuda__text">Contáctanos si tienes problemas con el pago.</p>
                  <a href="tel:+51011234567" className="pago-ayuda__link">
                    <Phone size={16} aria-hidden="true" />
                    (01) 123-4567
                  </a>
                  <a
                    href={`mailto:${manualInfo?.pagos_email ?? 'pagos@novasalud.pe'}`}
                    className="pago-ayuda__link"
                  >
                    <Mail size={16} aria-hidden="true" />
                    {manualInfo?.pagos_email ?? 'pagos@novasalud.pe'}
                  </a>
                </div>
              </aside>
            </form>
          )}
        </div>
      </section>
    </main>
  );
}
