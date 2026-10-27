<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$nombreBiblioteca = "Montalbán";
$mensajeError = "";
$libroDetalle = null;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $idLibroBuscado = (int)$_GET['id'];

    try {
        require_once '../bll/LibroBLL.php';
        $libroBLL = new LibroBLL();

        // Si existe un método específico (p. ej. obtenerLibroPorId), úsalo en su lugar.
        $listaLibros = $libroBLL->listarLibros();

        foreach ($listaLibros as $l) {
            if ($l['id_libro'] == $idLibroBuscado) {
                $libroDetalle = $l;
                break;
            }
        }

        if (!$libroDetalle) {
            $mensajeError = "No se encontró el libro solicitado.";
        }

    } catch (Exception $e) {
        $mensajeError = $e->getMessage();
    }
} else {
    $mensajeError = "Petición no válida. Falta el ID del libro.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del libro | <?php echo htmlspecialchars($nombreBiblioteca); ?></title>
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
                <h1>Detalle del libro</h1>
                <p>Información del ejemplar registrado en el inventario.</p>
            </div>
            <a href="catalogo.php" class="btn btn-borde">Volver al catálogo</a>
        </div>

        <?php if (!empty($mensajeError)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($mensajeError); ?>
            </div>
        <?php elseif ($libroDetalle): ?>
            <?php
                $disponibles = (int)($libroDetalle['stock_disponible'] ?? 0);
                $total = (int)($libroDetalle['stock_total'] ?? 0);
            ?>
            <div class="panel ficha">
                <h2 class="titulo"><?php echo htmlspecialchars($libroDetalle['titulo']); ?></h2>
                <p class="autor">Por <?php echo htmlspecialchars($libroDetalle['autor']); ?></p>

                <div class="ficha-datos">
                    <div>
                        <span class="dato-label">ID del libro</span>
                        <p class="dato-valor">#<?php echo htmlspecialchars($libroDetalle['id_libro']); ?></p>
                    </div>
                    <?php if (!empty($libroDetalle['isbn'])): ?>
                    <div>
                        <span class="dato-label">ISBN</span>
                        <p class="dato-valor"><code><?php echo htmlspecialchars($libroDetalle['isbn']); ?></code></p>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($libroDetalle['categoria'])): ?>
                    <div>
                        <span class="dato-label">Categoría</span>
                        <p class="dato-valor"><span class="etiqueta neutra"><?php echo htmlspecialchars($libroDetalle['categoria']); ?></span></p>
                    </div>
                    <?php endif; ?>
                    <div>
                        <span class="dato-label">Disponibilidad</span>
                        <p class="dato-valor">
                            <?php if ($disponibles > 0): ?>
                                <span class="estado disponible"><?php echo $disponibles; ?> de <?php echo $total; ?> disponibles</span>
                            <?php else: ?>
                                <span class="estado agotado">Agotado</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer class="pie">
        <div class="container">Biblioteca Montalbán &middot; Sistema de gestión bibliotecaria</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
