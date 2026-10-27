<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../bll/BibliotecaBLL.php';

$bll = new BibliotecaBLL();
$mensaje = "";
$tipo = "success";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['registrar_usuario'])) {
            $bll->crearUsuario($_POST['matricula'], $_POST['nombre'], $_POST['email'], $_POST['id_rol']);
            $mensaje = "Usuario registrado correctamente con su rol asignado.";
        } elseif (isset($_POST['desbloquear_usuario'])) {
            $bll->levantarSancion($_POST['id_usuario']);
            $mensaje = "Bloqueo temporal retirado al usuario.";
        }
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipo = "danger";
    }
}

$usuarios = $bll->listarUsuarios();
$roles = $bll->listarRoles();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios y Roles | Montalbán</title>
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
                <h1>Usuarios y Roles</h1>
                <p><?php echo count($usuarios); ?> usuarios registrados en el sistema.</p>
            </div>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipo; ?>"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="panel">
                    <div class="panel-titulo">Registrar usuario</div>
                    <div class="panel-cuerpo">
                        <form method="POST">
                            <input type="hidden" name="registrar_usuario" value="1">
                            <div class="mb-3">
                                <label class="form-label">Matrícula / clave</label>
                                <input type="text" name="matricula" class="form-control" placeholder="Ej. 2026123" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nombre completo</label>
                                <input type="text" name="nombre" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Correo institucional</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-4">
                                <label class="form-label">Rol en el sistema</label>
                                <select name="id_rol" class="form-select" required>
                                    <?php foreach ($roles as $r): ?>
                                        <option value="<?php echo $r['id_rol']; ?>">
                                            <?php echo htmlspecialchars($r['nombre_rol']); ?> (máx. <?php echo $r['limite_prestamos']; ?> libros / <?php echo $r['dias_prestamo']; ?> días)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-marino w-100">Guardar usuario</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="panel">
                    <div class="table-responsive">
                        <table class="table align-middle tabla-datos">
                            <thead>
                                <tr>
                                    <th>Matrícula</th>
                                    <th>Nombre</th>
                                    <th>Rol</th>
                                    <th class="text-center">Préstamos activos</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $u): ?>
                                <tr>
                                    <td><code><?php echo htmlspecialchars($u['matricula']); ?></code></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($u['nombre']); ?></strong><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($u['email']); ?></small>
                                    </td>
                                    <td><span class="etiqueta info"><?php echo htmlspecialchars($u['nombre_rol']); ?></span></td>
                                    <td class="text-center"><?php echo $u['prestamos_activos']; ?> / <?php echo $u['limite_prestamos']; ?></td>
                                    <td class="text-center">
                                        <?php if ($u['sancionado']): ?>
                                            <span class="etiqueta peligro">Bloqueado por retraso</span>
                                            <form method="POST" class="d-inline ms-1">
                                                <input type="hidden" name="desbloquear_usuario" value="1">
                                                <input type="hidden" name="id_usuario" value="<?php echo $u['id_usuario']; ?>">
                                                <button class="btn btn-sm btn-borde-ok">Reactivar</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="etiqueta ok">Activo</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="pie">
        <div class="container">Biblioteca Montalbán &middot; Sistema de gestión bibliotecaria</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
