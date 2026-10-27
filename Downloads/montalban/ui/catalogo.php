<?php
// Mientras desarrollas, el servidor muestra el error exacto en lugar del Error 500.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../bll/LibroBLL.php';

$nombreBiblioteca = "Montalbán";
$libroBLL = new LibroBLL();
$mensaje = "";
$tipoAlerta = "success";

// Procesar acciones CRUD de RF1
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $accion = $_POST['accion'] ?? '';
        if ($accion === 'crear') {
            $libroBLL->registrarEjemplar($_POST['isbn'], $_POST['titulo'], $_POST['autor'], $_POST['categoria'], $_POST['stock_total']);
            $mensaje = "Ejemplar registrado correctamente en el inventario.";
        } elseif ($accion === 'editar') {
            $libroBLL->modificarEjemplar($_POST['id_libro'], $_POST['isbn'], $_POST['titulo'], $_POST['autor'], $_POST['categoria'], $_POST['stock_total'], $_POST['stock_disponible']);
            $mensaje = "Ejemplar actualizado correctamente.";
        } elseif ($accion === 'baja') {
            $libroBLL->eliminarEjemplar($_POST['id_libro']);
            $mensaje = "Ejemplar dado de baja del catálogo activo.";
        }
    } catch (Exception $e) {
        $mensaje = $e->getMessage();
        $tipoAlerta = "danger";
    }
}

// Carga inicial de datos
$listaLibros = [];
$categorias = [];
try {
    $listaLibros = $libroBLL->listarLibros('', '', ''); // Carga todos para el filtro JS en tiempo real
    $categorias = $libroBLL->obtenerCategorias();
} catch (Exception $e) {
    $mensaje = "Error de conexión: " . $e->getMessage();
    $tipoAlerta = "danger";
}

// Estadísticas para el encabezado
$totalTitulos = count($listaLibros);
$conStock = 0;
foreach ($listaLibros as $l) {
    if ($l['stock_disponible'] > 0) { $conStock++; }
}
$agotados = $totalTitulos - $conStock;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo e Inventario | <?php echo htmlspecialchars($nombreBiblioteca); ?></title>
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
                <h1>Catálogo e Inventario</h1>
                <p>
                    <?php echo $totalTitulos; ?> títulos registrados:
                    <?php echo $conStock; ?> disponibles y <?php echo $agotados; ?> agotados.
                </p>
            </div>
            <button class="btn btn-marino" data-bs-toggle="modal" data-bs-target="#modalNuevoLibro">
                Registrar ejemplar
            </button>
        </div>

        <?php if ($mensaje): ?>
            <div class="alert alert-<?php echo $tipoAlerta; ?> alert-dismissible fade show">
                <?php echo htmlspecialchars($mensaje); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        <?php endif; ?>

        <!-- Filtros en tiempo real -->
        <div class="filtros">
            <div class="campo">
                <label class="form-label" for="buscar">Búsqueda rápida</label>
                <input type="search" id="buscar" class="form-control" placeholder="Título, autor o ISBN" autocomplete="off">
            </div>
            <div class="campo">
                <label class="form-label" for="filtroCategoria">Categoría</label>
                <select id="filtroCategoria" class="form-select">
                    <option value="todas">Todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="campo">
                <label class="form-label" for="filtroStock">Disponibilidad</label>
                <select id="filtroStock" class="form-select">
                    <option value="todos">Todos los ejemplares</option>
                    <option value="disponible">Con stock disponible</option>
                    <option value="agotado">Agotados</option>
                </select>
            </div>
        </div>

        <!-- Tabla de resultados -->
        <div class="panel">
            <div class="table-responsive">
                <table class="table align-middle tabla-datos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ISBN</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Categoría</th>
                            <th class="text-center">Total</th>
                            <th>Disponibilidad</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="cuerpoTabla">
                        <?php if (empty($listaLibros)): ?>
                            <tr><td colspan="8" class="text-center py-4 text-muted">Aún no hay libros en el inventario.</td></tr>
                        <?php else: ?>
                            <?php foreach ($listaLibros as $libro): ?>
                                <?php $hayStock = $libro['stock_disponible'] > 0; ?>
                                <tr data-estado="<?php echo $hayStock ? 'disponible' : 'agotado'; ?>"
                                    data-categoria="<?php echo htmlspecialchars($libro['categoria']); ?>">
                                    <td class="text-muted"><?php echo $libro['id_libro']; ?></td>
                                    <td><code><?php echo htmlspecialchars($libro['isbn']); ?></code></td>
                                    <td>
                                        <a class="fw-semibold text-decoration-none" style="color: var(--marino);" href="detalle.php?id=<?php echo (int)$libro['id_libro']; ?>">
                                            <?php echo htmlspecialchars($libro['titulo']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($libro['autor']); ?></td>
                                    <td><span class="etiqueta neutra"><?php echo htmlspecialchars($libro['categoria']); ?></span></td>
                                    <td class="text-center"><?php echo $libro['stock_total']; ?></td>
                                    <td>
                                        <?php if ($hayStock): ?>
                                            <span class="estado disponible"><?php echo $libro['stock_disponible']; ?> disponibles</span>
                                        <?php else: ?>
                                            <span class="estado agotado">Agotado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <button type="button" class="btn btn-sm btn-borde"
                                                onclick='abrirModalEditar(<?php echo json_encode($libro, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>Editar</button>
                                        <form method="POST" class="d-inline" onsubmit="return confirm('¿Dar de baja este ejemplar?');">
                                            <input type="hidden" name="accion" value="baja">
                                            <input type="hidden" name="id_libro" value="<?php echo $libro['id_libro']; ?>">
                                            <button type="submit" class="btn btn-sm btn-borde-peligro">Baja</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <p class="pie-tabla" id="contador" aria-live="polite">
            Mostrando <?php echo $totalTitulos; ?> de <?php echo $totalTitulos; ?> libros
        </p>
    </main>

    <footer class="pie">
        <div class="container">Biblioteca Montalbán &middot; Sistema de gestión bibliotecaria</div>
    </footer>

    <!-- Modal: nuevo ejemplar -->
    <div class="modal fade" id="modalNuevoLibro" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar ejemplar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="crear">
                    <div class="mb-3"><label class="form-label">ISBN</label><input type="text" name="isbn" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Título</label><input type="text" name="titulo" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Autor</label><input type="text" name="autor" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Categoría</label><input type="text" name="categoria" class="form-control" placeholder="Ingeniería, Literatura..." required></div>
                    <div class="mb-1"><label class="form-label">Stock total inicial</label><input type="number" name="stock_total" class="form-control" min="1" value="1" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-borde" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-marino">Guardar ejemplar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: modificar ejemplar -->
    <div class="modal fade" id="modalEditarLibro" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Modificar ejemplar</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="accion" value="editar">
                    <input type="hidden" name="id_libro" id="edit_id">
                    <div class="mb-3"><label class="form-label">ISBN</label><input type="text" name="isbn" id="edit_isbn" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Título</label><input type="text" name="titulo" id="edit_titulo" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Autor</label><input type="text" name="autor" id="edit_autor" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Categoría</label><input type="text" name="categoria" id="edit_categoria" class="form-control" required></div>
                    <div class="row">
                        <div class="col-6"><label class="form-label">Stock total</label><input type="number" name="stock_total" id="edit_total" class="form-control" required></div>
                        <div class="col-6"><label class="form-label">Stock disponible</label><input type="number" name="stock_disponible" id="edit_disp" class="form-control" required></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-borde" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-marino">Actualizar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Filtrado en tiempo real
        (function () {
            const buscar = document.getElementById('buscar');
            const filtroStock = document.getElementById('filtroStock');
            const filtroCategoria = document.getElementById('filtroCategoria');
            const filas = document.querySelectorAll('#cuerpoTabla tr[data-estado]');
            const contador = document.getElementById('contador');
            const total = filas.length;

            function normalizar(texto) {
                return texto.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
            }

            function aplicarFiltros() {
                const consulta = normalizar(buscar.value.trim());
                const estado = filtroStock.value;
                const categoria = filtroCategoria.value;
                let visibles = 0;

                filas.forEach(function (fila) {
                    const coincideTexto = normalizar(fila.textContent).includes(consulta);
                    const coincideEstado = estado === 'todos' || fila.dataset.estado === estado;
                    const coincideCat = categoria === 'todas' || fila.dataset.categoria === categoria;

                    const mostrar = coincideTexto && coincideEstado && coincideCat;
                    fila.style.display = mostrar ? '' : 'none';
                    if (mostrar) { visibles++; }
                });

                contador.textContent = visibles === 0
                    ? 'Ningún libro coincide con la búsqueda.'
                    : `Mostrando ${visibles} de ${total} libros`;
            }

            buscar.addEventListener('input', aplicarFiltros);
            filtroStock.addEventListener('change', aplicarFiltros);
            filtroCategoria.addEventListener('change', aplicarFiltros);
        })();

        // Apertura del modal de edición
        function abrirModalEditar(libro) {
            document.getElementById('edit_id').value = libro.id_libro;
            document.getElementById('edit_isbn').value = libro.isbn;
            document.getElementById('edit_titulo').value = libro.titulo;
            document.getElementById('edit_autor').value = libro.autor;
            document.getElementById('edit_categoria').value = libro.categoria;
            document.getElementById('edit_total').value = libro.stock_total;
            document.getElementById('edit_disp').value = libro.stock_disponible;
            new bootstrap.Modal(document.getElementById('modalEditarLibro')).show();
        }
    </script>
</body>
</html>
