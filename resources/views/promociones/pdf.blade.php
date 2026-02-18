<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; background: #fff; color: #222; }

        .card {
            border: 2px solid #198754;
            border-radius: 12px;
            padding: 30px 35px;
            max-width: 380px;
            margin: 30px auto;
        }
        .logo-area {
            text-align: center;
            margin-bottom: 18px;
            border-bottom: 1px solid #e0e0e0;
            padding-bottom: 14px;
        }
        .logo-area h2 {
            color: #198754;
            font-size: 22px;
            letter-spacing: 1px;
        }
        .logo-area p {
            font-size: 11px;
            color: #888;
        }
        .qr-area {
            text-align: center;
            margin: 20px 0;
        }
        .qr-area img {
            width: 180px;
            height: 180px;
        }
        .code-label {
            text-align: center;
            font-size: 12px;
            color: #555;
            margin-top: 6px;
            word-break: break-all;
        }
        .details {
            margin-top: 20px;
            border-top: 1px solid #e0e0e0;
            padding-top: 14px;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .details table tr td {
            padding: 5px 4px;
        }
        .details table tr td:first-child {
            color: #555;
            width: 45%;
        }
        .details table tr td:last-child {
            font-weight: bold;
            color: #222;
        }
        .badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 10px;
            font-size: 11px;
            color: #fff;
        }
        .badge-deluxe  { background: #ffc107; color: #333; }
        .badge-express { background: #0d6efd; }
        .badge-basico  { background: #6c757d; }
        .badge-ultra   { background: #dc3545; }
        .footer-area {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="logo-area">
            <h2>AQUA CAR CLUB</h2>
            <p>Código de Promoción</p>
        </div>

        <div class="qr-area">
            {{-- DomPDF pusede renderizar imágenes externas con allow_url_fopen --}}
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($code) }}" alt="QR">
            <div class="code-label">{{ $code }}</div>
        </div>

        <div class="details">
            <table>
                <tr>
                    <td>Paquete</td>
                    <td>
                        @php
                            $badgeClass = match($packageName) {
                                'Deluxe'  => 'badge-deluxe',
                                'Express' => 'badge-express',
                                'Básico'  => 'badge-basico',
                                'Ultra'   => 'badge-ultra',
                                default   => 'badge-basico',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $packageName }}</span>
                    </td>
                </tr>
                <tr>
                    <td>Tipo</td>
                    <td>{{ $type }}</td>
                </tr>
                <tr>
                    <td>Precio</td>
                    <td>${{ number_format($price, 2) }}</td>
                </tr>
                <tr>
                    <td>Usos disponibles</td>
                    <td>{{ $uses }}</td>
                </tr>
                <tr>
                    <td>Expira el</td>
                    <td>{{ $expiration }}</td>
                </tr>
            </table>
        </div>

        <div class="footer-area">
            Generado el {{ date('d/m/Y H:i') }} &bull; AquaStaff
        </div>
    </div>
</body>
</html>
