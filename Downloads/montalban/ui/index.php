<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio | Montalbán</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="estilos.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main class="container pb-4">
        <section class="portada">
            <span class="sobretitulo">Panel de administración</span>
            <h1>Sistema de Gestión Bibliotecaria Montalbán</h1>
            <p>Administración centralizada del acervo, los usuarios y la circulación de materiales, con arquitectura en capas (UI / BLL / DAL) y soporte PWA.</p>
        </section>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="modulo">
                    <span class="numero">MÓDULO 01</span>
                    <h2>Catálogo e Inventario</h2>
                    <p>Alta, edición y baja de ejemplares con ISBN, y filtros multicriterio en tiempo real.</p>
                    <a href="catalogo.php" class="btn btn-marino">Gestionar catálogo</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="modulo">
                    <span class="numero">MÓDULO 02</span>
                    <h2>Usuarios y Roles</h2>
                    <p>Administración diferenciada de estudiantes, docentes y bibliotecarios, con límites de préstamo por rol.</p>
                    <a href="usuarios.php" class="btn btn-marino">Gestionar usuarios</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="modulo">
                    <span class="numero">MÓDULO 03</span>
                    <h2>Préstamos y Sanciones</h2>
                    <p>Validación de stock disponible, control de límites y cálculo automático de multas por retraso.</p>
                    <a href="prestamos.php" class="btn btn-marino">Módulo de préstamos</a>
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
