<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $quotation->folio }} v{{ $quotation->version }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
        .totals { margin-top: 16px; width: 280px; margin-left: auto; }
        .totals td { border: none; padding: 4px 0; }
        .totals .label { text-align: right; padding-right: 12px; }
        .totals .amount { text-align: right; font-weight: bold; }
        .terms { margin-top: 24px; }
    </style>
</head>
<body>
    <h1>Cotización {{ $quotation->folio }}</h1>
    <div class="meta">
        <div>Versión: {{ $quotation->version }}</div>
        <div>Estado: {{ $quotation->status->label() }}</div>
        <div>Cliente: {{ $quotation->customer?->name }}</div>
        <div>Unidad: {{ $quotation->vehicle?->license_plate }} — {{ $quotation->vehicle?->brand }} {{ $quotation->vehicle?->model }}</div>
        <div>Emitida: {{ $quotation->issued_at?->format('Y-m-d H:i') ?? '—' }}</div>
        <div>Vigencia: {{ $quotation->valid_until?->format('Y-m-d') ?? '—' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Código</th>
                <th>Descripción</th>
                <th class="right">Cant.</th>
                <th class="right">P. unit.</th>
                <th class="right">Desc.</th>
                <th class="right">Importe</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($quotation->items as $item)
                <tr>
                    <td>{{ $item->item_type->label() }}</td>
                    <td>{{ $item->code }}</td>
                    <td>{{ $item->description }}</td>
                    <td class="right">{{ number_format((float) $item->quantity, 2) }}</td>
                    <td class="right">{{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="right">{{ number_format((float) $item->discount, 2) }}</td>
                    <td class="right">{{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Subtotal</td>
            <td class="amount" data-amount="subtotal">{{ number_format((float) $quotation->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Descuento</td>
            <td class="amount" data-amount="discount_total">{{ number_format((float) $quotation->discount_total, 2) }}</td>
        </tr>
        <tr>
            <td class="label">IVA ({{ number_format((float) $quotation->tax_rate, 2) }}%)</td>
            <td class="amount" data-amount="tax_total">{{ number_format((float) $quotation->tax_total, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Total</td>
            <td class="amount" data-amount="total">{{ number_format((float) $quotation->total, 2) }}</td>
        </tr>
    </table>

    @if ($quotation->commercial_terms)
        <div class="terms">
            <strong>Condiciones comerciales</strong>
            <p>{{ $quotation->commercial_terms }}</p>
        </div>
    @endif
</body>
</html>
