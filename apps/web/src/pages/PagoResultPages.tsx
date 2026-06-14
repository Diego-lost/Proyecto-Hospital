import { useEffect, useState } from 'react';
import { Link, useSearchParams } from 'react-router-dom';
import PageCover from '../components/PageCover';
import { formatMoney } from '../lib/catalogUtils';
import { fetchPago, verificarPago } from '../lib/payments';

export function PagoExitoPage() {
  const [params] = useSearchParams();
  const sessionId = params.get('session_id') ?? '';
  const [estado, setEstado] = useState<'loading' | 'paid' | 'pending' | 'error'>('loading');
  const [detalle, setDetalle] = useState<{
    servicio?: string;
    monto?: string | number;
    nombre?: string;
  }>({});

  useEffect(() => {
    if (!sessionId) {
      setEstado('error');
      return;
    }
    let cancelled = false;
    (async () => {
      try {
        const res = await verificarPago(sessionId);
        if (cancelled) {
          return;
        }
        setDetalle({
          servicio: res.pago.servicio?.nombre,
          monto: res.pago.monto,
          nombre: res.pago.cliente_nombre,
        });
        setEstado(res.pago.estado === 'paid' ? 'paid' : 'pending');
      } catch {
        if (!cancelled) {
          setEstado('error');
        }
      }
    })();
    return () => {
      cancelled = true;
    };
  }, [sessionId]);

  return (
    <main id="contenido" className="page-main">
      <PageCover title="Pago recibido" subtitle="Gracias por tu pago en línea." />
      <section className="section section--after-cover">
        <div className="container" style={{ maxWidth: 560 }}>
          <div className="card">
            {estado === 'loading' && <p className="muted">Confirmando pago con el servidor…</p>}
            {estado === 'paid' && (
              <>
                <p style={{ fontWeight: 800, marginTop: 0 }}>
                  Tu pago fue registrado correctamente.
                </p>
                {detalle.servicio && (
                  <p className="muted">
                    Servicio: <strong>{detalle.servicio}</strong>
                    {detalle.monto != null && <> — {formatMoney(detalle.monto)}</>}
                  </p>
                )}
                {detalle.nombre && (
                  <p className="muted" style={{ marginBottom: 0 }}>
                    Titular: {detalle.nombre}
                  </p>
                )}
              </>
            )}
            {estado === 'pending' && (
              <p className="muted" style={{ margin: 0 }}>
                El pago está en proceso. Si acabas de pagar, actualiza esta página en unos segundos o
                revisa tu correo de confirmación de Stripe.
              </p>
            )}
            {estado === 'error' && (
              <p className="form-msg form-msg--err" role="alert">
                No pudimos verificar el pago. Si ya pagaste, contacta a la clínica con tu comprobante
                de Stripe.
              </p>
            )}
            <div className="row" style={{ gap: 12, marginTop: 20 }}>
              <Link className="btn btn--primary" to="/cita">
                Agendar cita
              </Link>
              <Link className="btn btn--ghost" to="/">
                Inicio
              </Link>
            </div>
          </div>
        </div>
      </section>
    </main>
  );
}

export function PagoRegistradoPage() {
  const [params] = useSearchParams();
  const pagoId = Number(params.get('pago_id') ?? '');
  const [estado, setEstado] = useState<'loading' | 'ok' | 'error'>('loading');
  const [detalle, setDetalle] = useState<{
    servicio?: string;
    monto?: string | number;
    nombre?: string;
    metodo?: string;
    referencia?: string | null;
  }>({});

  useEffect(() => {
    if (!Number.isFinite(pagoId) || pagoId < 1) {
      setEstado('error');
      return;
    }
    let cancelled = false;
    (async () => {
      try {
        const res = await fetchPago(pagoId);
        if (cancelled) {
          return;
        }
        setDetalle({
          servicio: res.pago.servicio?.nombre,
          monto: res.pago.monto,
          nombre: res.pago.cliente_nombre,
          metodo: res.pago.metodo,
          referencia: res.pago.referencia_manual,
        });
        setEstado('ok');
      } catch {
        if (!cancelled) {
          setEstado('error');
        }
      }
    })();
    return () => {
      cancelled = true;
    };
  }, [pagoId]);

  return (
    <main id="contenido" className="page-main">
      <PageCover
        title="Solicitud registrada"
        subtitle="Hemos recibido tu aviso de pago. La clínica lo validará pronto."
      />
      <section className="section section--after-cover">
        <div className="container" style={{ maxWidth: 560 }}>
          <div className="card">
            {estado === 'loading' && <p className="muted">Cargando detalle…</p>}
            {estado === 'ok' && (
              <>
                <p style={{ fontWeight: 800, marginTop: 0 }}>
                  Tu solicitud de pago quedó registrada con el número #{pagoId}.
                </p>
                {detalle.servicio && (
                  <p className="muted">
                    Servicio: <strong>{detalle.servicio}</strong>
                    {detalle.monto != null && <> — {formatMoney(detalle.monto)}</>}
                  </p>
                )}
                {detalle.metodo && (
                  <p className="muted">
                    Método: <strong>{detalle.metodo}</strong>
                  </p>
                )}
                {detalle.referencia && (
                  <p className="muted">
                    Referencia: <strong>{detalle.referencia}</strong>
                  </p>
                )}
                <p className="muted" style={{ marginBottom: 0 }}>
                  Te contactaremos por correo cuando confirmemos el pago.
                </p>
              </>
            )}
            {estado === 'error' && (
              <p className="form-msg form-msg--err" role="alert">
                No pudimos cargar el detalle. Si ya enviaste el comprobante, la clínica lo revisará igualmente.
              </p>
            )}
            <div className="row" style={{ gap: 12, marginTop: 20 }}>
              <Link className="btn btn--primary" to="/pagar">
                Volver a pagos
              </Link>
              <Link className="btn btn--ghost" to="/">
                Inicio
              </Link>
            </div>
          </div>
        </div>
      </section>
    </main>
  );
}

export function PagoCanceladoPage() {
  return (
    <main id="contenido" className="page-main">
      <PageCover title="Pago cancelado" subtitle="No se realizó ningún cargo." />
      <section className="section section--after-cover">
        <div className="container" style={{ maxWidth: 560 }}>
          <div className="card">
            <p className="muted" style={{ marginTop: 0 }}>
              Puedes intentar de nuevo cuando quieras.
            </p>
            <div className="row" style={{ gap: 12, marginTop: 16 }}>
              <Link className="btn btn--primary" to="/pagar">
                Volver a pagar
              </Link>
              <Link className="btn btn--ghost" to="/">
                Inicio
              </Link>
            </div>
          </div>
        </div>
      </section>
    </main>
  );
}
