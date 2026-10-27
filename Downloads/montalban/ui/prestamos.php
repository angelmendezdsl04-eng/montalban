<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../bll/BibliotecaBLL.php';
require_once '../bll/LibroBLL.php';

$biblioBLL = new BibliotecaBLL();
$libroBLL = new LibroBLL();
$mensaje = "";
$tipo = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['nuevo_prestamo'])) {
            $biblioBLL->procesarPrestamo($_POST['id_usuario'], $_POST['id_libro']);
            $mensaje = "Préstamo procesado correctamente. El stock físico se descontó de forma automática.";
        } elseif (isset($_POST['devolver_prestamo'])) {
            $resultado = $biblioBLL->procesarDevolucion($_POST['id_prestamo']);
            if ($resultado['multa'] > 0) {
                $mensaje = "Devolución con retraso registrada. Sanción calculada: $" . number_format($resultado['multa'], 2) . " MXN y bloqueo temporal aplicado al usuario.";
                $tipo = "warning";
            } else {
                $mensaje = "Devolución en tiempo registrada sin sanciones. Ejemplar reintegrado al stock disponible.";
            }
        }
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipo = "danger";
    }
}

$prestamos = $biblioBLL->listarPrestamosCalculados();
$usuarios = $biblioBLL->listarUsuarios();
$libros = $libroBLL->listarLibros();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Préstamos y Devoluciones | Montalbán</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="estilos.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container pb-4">
        <div class="pagina-encabezado">
            <div>
                <h1>Préstamos y Sanciones</h1>
                <p>Control de circulación, devoluciones y cálculo de multas por retraso.</p>
            </div>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo; ?> fw-semibold"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <!-- Formulario para asignar préstamo -->
        <div class="panel mb-4">
            <div class="panel-titulo">Asignar nuevo préstamo</div>
            <div class="panel-cuerpo">
                <form method="POST">
                    <input type="hidden" name="nuevo_prestamo" value="1">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label">Usuario</label>
                            <select name="id_usuario" class="form-select" required>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?php echo $u['id_usuario']; ?>">
                                        [<?php echo htmlspecialchars($u['nombre_rol']); ?>] <?php echo htmlspecialchars($u['nombre']); ?>
                                        (activos: <?php echo $u['prestamos_activos']; ?>/<?php echo $u['limite_prestamos']; ?>)
                                        <?php if ($u['sancionado']) echo ' - BLOQUEADO'; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Ejemplar</label>
                            <select name="id_libro" class="form-select" required>
                                <?php foreach ($libros as $l): ?>
                                    <option value="<?php echo $l['id_libro']; ?>">
                                        <?php echo htmlspecialchars($l['titulo']); ?> (disponibles: <?php echo $l['stock_disponible']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-marino">Asignar préstamo</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de préstamos y multas -->
        <div class="panel">
            <div class="table-responsive">
                <table class="table align-middle tabla-datos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario / Rol</th>
                            <th>Ejemplar</th>
                            <th>Fecha préstamo</th>
                            <th>Fecha límite</th>
                            <th class="text-center">Retraso / multa ($15 por día)</th>
                            <th class="text-center">Estado / acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($prestamos as $p): ?>
                        <tr>
                            <td class="text-muted">#<?php echo $p['id_prestamo']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($p['nombre_usuario']); ?></strong><br>
                                <span class="etiqueta neutra"><?php echo htmlspecialchars($p['nombre_rol']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($p['titulo_libro']); ?></td>
                            <td><?php echo $p['fecha_prestamo']; ?></td>
                            <td><?php echo $p['fecha_devolucion_esperada']; ?></td>
                            <td class="text-center">
                                <?php if ($p['multa_estimada'] > 0): ?>
                                    <span class="etiqueta peligro">$<?php echo number_format($p['multa_estimada'], 2); ?> MXN</span>
                                    <?php if ($p['dias_retraso'] > 0): ?>
                                        <div class="small fw-semibold mt-1" style="color: var(--peligro);"><?php echo $p['dias_retraso']; ?> días de retraso</div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">$0.00</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($p['estado'] === 'Activo'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="devolver_prestamo" value="1">
                                        <input type="hidden" name="id_prestamo" value="<?php echo $p['id_prestamo']; ?>">
                                        <button type="submit" class="btn btn-sm btn-ambar">Registrar devolución</button>
                                    </form>
                                <?php else: ?>
                                    <span class="etiqueta ok">Devuelto el <?php echo $p['fecha_devolucion_real']; ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="pie">
        <div class="container">Biblioteca Montalbán &middot; Sistema de gestión bibliotecaria</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
