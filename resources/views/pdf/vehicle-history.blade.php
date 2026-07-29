<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Expediente {{ $vehicle->license_plate }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        h2 { font-size: 14px; margin-top: 18px; margin-bottom: 8px; }
        .meta { margin-bottom: 16px; line-height: 1.45; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; }
        .right { text-align: right; }
        .muted { color: #666; font-size: 10px; }
        .empty { margin-top: 16px; color: #666; }
    </style>
</head>
<body>
    <h1>Expediente de unidad</h1>
    <div class="meta">
        <div><strong>Placas:</strong> {{ $vehicle->license_plate }} ({{ $vehicle->license_plate_normalized }})</div>
        <div><strong>Unidad:</strong> {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->year }})</div>
        <div><strong>Tipo:</strong> {{ $vehicle->vehicleType?->name ?? '—' }}</div>
        <div><strong>Cliente:</strong> {{ $customer?->name ?? '—' }}</div>
        <div><strong>Generado:</strong> {{ $generatedAt->format('Y-m-d H:i') }}</div>
        @if (! empty($filters['from']) || ! empty($filters['to']) || ! empty($filters['types']))
            <div class="muted">
                Filtros:
                @if (! empty($filters['from'])) desde {{ $filters['from'] }} @endif
                @if (! empty($filters['to'])) hasta {{ $filters['to'] }} @endif
                @if (! empty($filters['types'])) · tipos: {{ implode(', ', $filters['types']) }} @endif
            </div>
        @endif
    </div>

    <h2>Historial consolidado</h2>

    @if (count($entries) === 0)
        <p class="empty">Sin eventos en el periodo seleccionado.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Referencia</th>
                    <th>Estado</th>
                    <th class="right">Importe</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entries as $entry)
                    <tr>
                        <td>{{ \Illuminate\Support\Carbon::parse($entry['occurred_at'])->format('Y-m-d H:i') }}</td>
                        <td>{{ $entry['type_label'] }}</td>
                        <td>
                            {{ $entry['title'] }}
                            @if (! empty($entry['subtitle']))
                                <div class="muted">{{ \Illuminate\Support\Str::limit($entry['subtitle'], 80) }}</div>
                            @endif
                        </td>
                        <td>{{ $entry['status_label'] ?? '—' }}</td>
                        <td class="right">
                            @if ($entry['amount'] !== null)
                                {{ number_format((float) $entry['amount'], 2) }}
                            @else
                                —
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
