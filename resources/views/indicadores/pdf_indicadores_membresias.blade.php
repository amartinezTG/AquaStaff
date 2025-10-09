<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    body {
      font-family: sans-serif;
      font-size: 10px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
    }
    th, td {
      border: 1px solid #ccc;
      padding: 4px;
      text-align: center;
    }
    th {
      background-color: #f0f0f0;
    }
    .titulo {
      background-color: #31bec5;
      color: white;
      font-weight: bold;
      padding: 8px;
      text-align: center;
    }
  </style>
</head>
<body>

  <h2 class="titulo">Aqua Car Club Misiones - Uso de Membresías<br>
  Del {{ \Carbon\Carbon::parse($startDate)->isoFormat('D [de] MMMM [de] YYYY') }} al {{ \Carbon\Carbon::parse($endDate)->isoFormat('D [de] MMMM [de] YYYY') }}</h2>

  <p><strong>Total de uso de membresías:</strong> {{ $resultado->Total_Clientes_Con_Membresia }}<br>
  <strong>Uso promedio:</strong> {{ number_format($resultado->Uso_Promedio, 2) }}</p>

  <table>
    <thead>
      <tr>
        <th>Referencia o ID</th>
        <th>Nombre Ligado al ID</th>
        <th>Usos</th>
        <th>Paquete asignado originalmente</th>
        <th>Inscrito desde</th>
        <th>Costo de membresía</th>
        <th>Ticket promedio</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($resultados as $item)
        <tr>
          <td>{{ $item->Referencia }}</td>
          <td>{{ $item->Nombre }} {{ $item->Apellido }}</td>
          <td>{{ $item->usos }}</td>
          <td>{{ $item->Nombre_Paquete }}</td>
          <td>{{ $item->Fecha_PrimerCobro ? \Carbon\Carbon::parse($item->Fecha_PrimerCobro)->format('Y-m-d') : '' }}</td>
          <td>${{ number_format($item->Precio, 2) }}</td>
          <td>${{ number_format($item->ticket_promedio, 2) }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

</body>
</html>