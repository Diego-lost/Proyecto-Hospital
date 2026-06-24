import { apiJson } from '../api';



export type PagoBankInfo = {

  nombre: string;

  cuenta: string;

  titular: string;

  cci: string;

};



export type PagoConfig = {

  stripe_configured: boolean;

  currency?: string;

  public_key?: string | null;

  admin_fee?: number;

  manual?: {

    yape_phone?: string;

    pagos_email?: string;

    bank?: PagoBankInfo;

  };

};



export type CheckoutPayload = {
  servicio_id: number;
  solicitud_cita_id?: number;
  cliente_nombre: string;
  cliente_email: string;
  cliente_telefono?: string;
};



export type ManualPagoPayload = CheckoutPayload & {

  metodo: 'yape' | 'transferencia';

  referencia_manual?: string;

  notas?: string;

};



export type CheckoutResponse = {

  checkout_url?: string;

  session_id?: string;

  pago_id?: number;

};



export type ManualPagoResponse = {

  ok: boolean;

  pago_id: number;

  redirect_url?: string;

  message?: string;

};



export type PagoDetalle = {

  id: number;

  estado: string;

  metodo?: string;

  monto?: string | number;

  moneda?: string;

  cliente_nombre?: string;

  cliente_email?: string;

  referencia_manual?: string | null;

  servicio?: { nombre?: string } | null;
  solicitud_cita_id?: number | null;
  paid_at?: string | null;

};



export type VerificarResponse = {

  pago: PagoDetalle;

};



export async function fetchPagoConfig(): Promise<PagoConfig> {

  return apiJson<PagoConfig>('/api/pagos/config');

}



export async function createCheckoutSession(payload: CheckoutPayload): Promise<CheckoutResponse> {

  return apiJson<CheckoutResponse>('/api/pagos/checkout', {

    method: 'POST',

    body: JSON.stringify(payload),

  });

}



export async function registrarPagoManual(payload: ManualPagoPayload): Promise<ManualPagoResponse> {

  return apiJson<ManualPagoResponse>('/api/pagos/manual', {

    method: 'POST',

    body: JSON.stringify(payload),

  });

}



export async function verificarPago(sessionId: string): Promise<VerificarResponse> {

  const q = new URLSearchParams({ session_id: sessionId });

  return apiJson<VerificarResponse>(`/api/pagos/verificar?${q}`);

}



export async function fetchPago(pagoId: number): Promise<VerificarResponse> {

  return apiJson<VerificarResponse>(`/api/pagos/${pagoId}`);

}


