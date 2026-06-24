import { useEffect, useState } from 'react';
import { useSearchParams } from 'react-router-dom';
import { CheckCircle2, Download, Loader2 } from 'lucide-react';
import { formatMoney } from '../../lib/catalogUtils';
import {
  downloadComprobanteHtml,
  fetchCitaComprobante,
  type CitaComprobante,
} from '../../lib/citaComprobante';
import { verificarPago } from '../../lib/payments';

export default function CitaComprobanteBanner() {
  const [params, setParams] = useSearchParams();
  const citaOk = params.get('cita_ok') === '1';
  const solicitudId = Number(params.get('solicitud_id') ?? '');
  const pagoId = Number(params.get('pago_id') ?? '');
  const sessionId = params.get('session_id') ?? '';

  const [loading, setLoading] = useState(false);
  const [comprobante, setComprobante] = useState<CitaComprobante | null>(null);
  const [err, setErr] = useState<string | null>(null);

  useEffect(() => {
    if (!citaOk) {
      return;
    }

    let cancelled = false;

    (async () => {
      setLoading(true);
      setErr(null);

      try {
        let sid = Number.isFinite(solicitudId) && solicitudId > 0 ? solicitudId : 0;

        if (sessionId) {
          const ver = await verificarPago(sessionId);
          if (cancelled) {
            return;
          }
          if (ver.pago.solicitud_cita_id && sid < 1) {
            sid = ver.pago.solicitud_cita_id;
          }
          if (ver.pago.estado !== 'paid' && ver.pago.estado !== 'pending') {
            setErr('El pago aún no está confirmado. Actualiza en unos segundos.');
          }
        }

        if (sid < 1) {
          setErr('No se encontró el identificador de la cita.');
          return;
        }

        const data = await fetchCitaComprobante(sid);
        if (!cancelled) {
          setComprobante(data);
        }
      } catch (e) {
        if (!cancelled) {
          setErr(e instanceof Error ? e.message : 'No se pudo cargar el comprobante.');
        }
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    })();

    return () => {
      cancelled = true;
    };
  }, [citaOk, solicitudId, sessionId]);

  if (!citaOk) {
    return null;
  }

  function dismiss() {
    params.delete('cita_ok');
    params.delete('solicitud_id');
    params.delete('pago_id');
    params.delete('session_id');
    setParams(params, { replace: true });
  }

  return (
    <section className="border-b border-emerald-200 bg-emerald-50">
      <div className="container mx-auto max-w-4xl px-4 py-6">
        {loading ? (
          <p className="flex items-center gap-2 text-sm text-emerald-900">
            <Loader2 className="animate-spin" size={18} />
            Confirmando tu cita y pago…
          </p>
        ) : err ? (
          <div>
            <p className="text-sm font-semibold text-red-700" role="alert">
              {err}
            </p>
            <button type="button" className="btn btn--ghost mt-3" onClick={dismiss}>
              Cerrar
            </button>
          </div>
        ) : comprobante ? (
          <div className="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
            <div className="flex gap-3">
              <CheckCircle2 className="mt-0.5 shrink-0 text-emerald-600" size={28} aria-hidden="true" />
              <div>
                <h2 className="font-display text-lg font-bold text-emerald-950">
                  ¡Cita registrada correctamente!
                </h2>
                <p className="mt-1 text-sm text-emerald-900">
                  Solicitud <strong>#{comprobante.solicitud_id}</strong>
                  {comprobante.servicio && (
                    <>
                      {' '}
                      — {comprobante.servicio.nombre}
                      {comprobante.pago && <> ({formatMoney(comprobante.pago.monto)})</>}
                    </>
                  )}
                </p>
                <dl className="mt-3 grid gap-1 text-sm text-emerald-950/90 sm:grid-cols-2">
                  <div>
                    <dt className="font-semibold">Paciente</dt>
                    <dd>{comprobante.nombre}</dd>
                  </div>
                  <div>
                    <dt className="font-semibold">DNI</dt>
                    <dd>{comprobante.paciente_dni}</dd>
                  </div>
                  <div>
                    <dt className="font-semibold">Médico</dt>
                    <dd>{comprobante.medico?.nombre ?? 'Sin preferencia'}</dd>
                  </div>
                  <div>
                    <dt className="font-semibold">Horario preferido</dt>
                    <dd>
                      {comprobante.fecha ?? '—'}
                      {comprobante.hora ? ` · ${comprobante.hora}` : ''}
                    </dd>
                  </div>
                </dl>
              </div>
            </div>
            <div className="flex shrink-0 flex-wrap gap-2">
              <button
                type="button"
                className="btn btn--primary inline-flex items-center gap-2"
                onClick={() => downloadComprobanteHtml(comprobante)}
              >
                <Download size={18} aria-hidden="true" />
                Descargar cita
              </button>
              <button type="button" className="btn btn--ghost" onClick={dismiss}>
                Cerrar
              </button>
            </div>
          </div>
        ) : null}
        {!loading && !err && !comprobante && pagoId > 0 ? (
          <p className="text-sm text-emerald-900">Pago #{pagoId} registrado.</p>
        ) : null}
      </div>
    </section>
  );
}
