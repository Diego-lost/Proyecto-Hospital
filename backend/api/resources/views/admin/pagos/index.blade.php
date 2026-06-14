@extends('admin.layout')

@section('title', 'Pagos en línea')

@section('content')
  <div class="row" style="justify-content: space-between; margin-bottom: 12px;">
    <div>
      <h1 style="margin:0;">Pagos</h1>
      <div class="muted">Cobros con tarjeta (Stripe) y solicitudes Yape / transferencia pendientes de validación.</div>
    </div>
  </div>

  @if (session('status'))
    <div class="callout" style="margin-bottom: 12px;">{{ session('status') }}</div>
  @endif

  <div class="callout">
    <strong>Stripe:</strong> configura <code>STRIPE_SECRET</code>, <code>STRIPE_PUBLIC_KEY</code> y <code>STRIPE_WEBHOOK_SECRET</code> en <code>.env</code>.
    Webhook: <code>POST {{ url('/api/stripe/webhook') }}</code> (evento <code>checkout.session.completed</code>).
  </div>

  <div class="card">
    <table class="table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Servicio</th>
          <th>Cliente</th>
          <th>Método</th>
          <th>Monto</th>
          <th>Estado</th>
          <th>Referencia</th>
          <th>Fecha</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($pagos as $p)
          <tr>
            <td>{{ $p->id }}</td>
            <td>{{ $p->servicio?->nombre ?? '—' }}</td>
            <td>
              <div style="font-weight: 800;">{{ $p->cliente_nombre }}</div>
              <div class="muted">{{ $p->cliente_email }}</div>
              @if ($p->cliente_telefono)
                <div class="muted">{{ $p->cliente_telefono }}</div>
              @endif
            </td>
            <td>{{ $p->metodo }}</td>
            <td>S/ {{ number_format((float) $p->monto, 2) }}</td>
            <td><span class="badge">{{ $p->estado }}</span></td>
            <td class="muted" style="font-size: 12px; max-width: 140px; word-break: break-all;">
              {{ $p->referencia_manual ?? ($p->stripe_checkout_session_id ? \Illuminate\Support\Str::limit($p->stripe_checkout_session_id, 24) : '—') }}
            </td>
            <td class="muted">
              {{ $p->created_at?->format('Y-m-d H:i') }}
              @if ($p->paid_at)
                <div>Pagado: {{ $p->paid_at->format('Y-m-d H:i') }}</div>
              @endif
            </td>
            <td>
              @if ($p->estado === \App\Models\Pago::ESTADO_PENDING_MANUAL)
                <form method="post" action="{{ route('admin.pagos.confirmar', $p) }}" style="margin:0;">
                  @csrf
                  @method('PATCH')
                  <button type="submit" class="btn btn--sm">Confirmar</button>
                </form>
              @else
                —
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="muted">Aún no hay pagos registrados.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
@endsection
