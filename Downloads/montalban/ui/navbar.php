<?php
$paginaActual = basename($_SERVER['PHP_SELF']);
$enlaces = [
    'index.php'     => 'Inicio',
    'catalogo.php'  => 'Catálogo',
    'usuarios.php'  => 'Usuarios',
    'prestamos.php' => 'Préstamos',
];
// El detalle de un libro pertenece a la sección Catálogo
if ($paginaActual === 'detalle.php') { $paginaActual = 'catalogo.php'; }
?>
<header class="barra">
    <div class="container barra-interior">
        <a class="marca" href="index.php">
            <img src="montalban.png" alt="" onerror="this.style.display='none'">
            <span class="marca-texto">
                Montalbán
                <small>Sistema de gestión bibliotecaria</small>
            </span>
        </a>
        <nav class="barra-nav" aria-label="Navegación principal">
            <?php foreach ($enlaces as $archivo => $texto): ?>
                <a href="<?php echo $archivo; ?>" class="<?php echo $paginaActual === $archivo ? 'activo' : ''; ?>"
                   <?php echo $paginaActual === $archivo ? 'aria-current="page"' : ''; ?>>
                    <?php echo $texto; ?>
                </a>
            <?php endforeach; ?>
        </nav>
    </div>
</header>
