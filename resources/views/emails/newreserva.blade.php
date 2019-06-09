<!doctype html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
    <title>Nueva reserva creada</title>
</head>
<body>
    <p>Estos son los datos de la nueva reserva creada a las {{ $reserva->created_at }}:</p>
    <ul>
        <li>Fecha Reserva: {{ $reserva->fecha_reserva }}</li>
        <li>Hora de inicio: {{ $reserva->hora_inicio }}</li>
        <li>Hora de fin: {{ $reserva->hora_fin }}</li>
    </ul>
</body>
</html>