<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 20px; }
        .container { max-width: 620px; margin: 0 auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
        .header { background: #c0392b; color: #fff; padding: 24px 30px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header p  { margin: 6px 0 0; font-size: 13px; opacity: .85; }
        .body { padding: 24px 30px; }
        .body p { color: #444; font-size: 14px; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; font-size: 13px; }
        th { background: #2c3e50; color: #fff; padding: 10px 12px; text-align: left; }
        td { padding: 9px 12px; border-bottom: 1px solid #eee; color: #333; }
        tr:last-child td { border-bottom: none; }
        .badge-danger  { background: #e74c3c; color: #fff; padding: 3px 9px; border-radius: 12px; font-size: 11px; }
        .badge-nodata  { background: #7f8c8d; color: #fff; padding: 3px 9px; border-radius: 12px; font-size: 11px; }
        .footer { padding: 16px 30px; background: #f9f9f9; font-size: 12px; color: #999; text-align: center; border-top: 1px solid #eee; }
    </style>
</head>
<body> 
<div class="container">
    <div class="header">
        <h1>⚠️ Alerta de Sincronización</h1>
        <p>{{ now()->format('d/m/Y H:i') }} — AquaStaff</p>
    </div>
    <div class="body">
        <p>Las siguientes estructuras llevan <strong>más de 24 horas sin recibir información</strong> en <code>transactions_log</code>:</p>

        <table>
            <thead>
                <tr>
                    <th>Estructura</th>
                    <th>Última Recepción</th>
                    <th>Tiempo Transcurrido</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($alertas as $alerta)
                    @php
                        $min = $alerta['minutos_desde'];
                        if ($min === null) {
                            $tiempo = '—';
                        } elseif ($min < 1440) {
                            $tiempo = floor($min / 60) . 'h ' . ($min % 60) . 'min';
                        } else {
                            $tiempo = floor($min / 1440) . 'd ' . floor(($min % 1440) / 60) . 'h';
                        }
                    @endphp
                    <tr>
                        <td><code>{{ $alerta['estructura'] }}</code></td>
                        <td>{{ $alerta['ultima_recepcion'] }}</td>
                        <td>{{ $tiempo }}</td>
                        <td>
                            @if($alerta['minutos_desde'] === null)
                                <span class="badge-nodata">Sin datos</span>
                            @else
                                <span class="badge-danger">+24h</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <p style="margin-top:20px;">Revisa el <a href="{{ config('app.url') }}/administracion/sync-monitor">Monitor de Sincronización</a> para más detalles.</p>
    </div>
    <div class="footer">
        Este correo fue generado automáticamente por AquaStaff. No responder.
    </div>
</div>
</body>
</html>
