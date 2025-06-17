<?php
// Incluir el archivo de configuración para la conexión a la BD
require_once "config.php";

// Consulta SQL para seleccionar todos los residentes activos
$sql = "SELECT id_residente, nombre, apellidos, dni, telefono_contacto FROM residentes WHERE estado = 'activo' ORDER BY apellidos, nombre";

$result = $mysqli->query($sql);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Residentes Activos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .actions a {
            margin-right: 8px;
            text-decoration: none;
            color: #007bff;
        }
        .actions a:hover {
            text-decoration: underline;
        }
        .no-records {
            padding: 10px;
            background-color: #fff3cd;
            border: 1px solid #ffeeba;
            color: #856404;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Lista de Residentes Activos</h1>

        <?php if ($result && $result->num_rows > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>DNI</th>
                        <th>Teléfono Contacto</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id_residente']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($row['apellidos']); ?></td>
                            <td><?php echo htmlspecialchars($row['dni']); ?></td>
                            <td><?php echo htmlspecialchars($row['telefono_contacto']); ?></td>
                            <td class="actions">
                                <a href="ver_residente.php?id=<?php echo $row['id_residente']; ?>">Ver</a>
                                <a href="editar_residente.php?id=<?php echo $row['id_residente']; ?>">Editar</a>
                                <a href="baja_residente.php?id=<?php echo $row['id_residente']; ?>" onclick="return confirm('¿Está seguro de que desea dar de baja a este residente?');">Dar de baja</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-records">
                No hay residentes activos registrados en este momento.
            </div>
        <?php endif; ?>
    </div>
    <?php
    // Cerrar la conexión a la base de datos
    $mysqli->close();
    ?>
</body>
</html>
