<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    * { margin: 0; padding: 0; }
    body {
        font-family: DejaVu Sans, sans-serif;
        margin: 6mm;
    }

    /* table-layout:fixed + padding:0 en td garantizan 4 columnas exactas */
    table.grid {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    td.card-cell {
        width: 25%;
        height: 66mm;
        border: 1px dashed #b8b8b8;
        padding: 0;
        vertical-align: top;
        text-align: center;
        overflow: hidden;
    }
    td.card-cell.empty { border: 1px dashed #e0e0e0; }

    .inner {
        padding: 2.5mm 2mm 2mm;
    }

    .proyecto {
        font-size: 6.5pt;
        color: #555;
        margin-bottom: 2mm;
        font-weight: bold;
    }

    .qr-img {
        display: block;
        margin: 0 auto 2mm;
        width:  35mm;
        height: 35mm;
    }

    .code-txt {
        font-size: 4.8pt;
        color: #777;
        word-break: break-all;
        line-height: 1.3;
        margin-bottom: 2mm;
    }

    .details {
        font-size: 6pt;
        color: #333;
        line-height: 1.5;
    }
</style>
</head>
<body>

@php $pages = $items->chunk(16); @endphp

@foreach ($pages as $pageIndex => $page)

    @if ($pageIndex > 0)
        <div style="page-break-before: always;"></div>
    @endif

    <table class="grid">
        @foreach ($page->chunk(4) as $row)
        <tr>
            @foreach ($row as $item)
            <td class="card-cell">
                <div class="inner">
                    <div class="proyecto">{{ $item['proyecto'] }}</div>
                    <img class="qr-img"
                         src="data:image/png;base64,{{ $item['qr_b64'] }}"
                         alt="QR">
                    <div class="code-txt">{{ $item['code'] }}</div>
                    <div class="details">
                        ${{ number_format($item['price'], 2) }}
                        &bull; {{ $item['uses'] }} uso(s)
                        &bull; Exp: {{ $item['expiration'] }}
                    </div>
                </div>
            </td>
            @endforeach

            @if ($row->count() < 4)
                @for ($e = $row->count(); $e < 4; $e++)
                <td class="card-cell empty"></td>
                @endfor
            @endif
        </tr>
        @endforeach
    </table>

@endforeach

</body>
</html>
