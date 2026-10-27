<?php
require_once 'conexion.php';

class LibroDAL {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->obtenerConexion();
    }

    // RF5: Filtro multicriterio (título/autor, categoría y disponibilidad inmediata)
    public function buscarLibros($busqueda = '', $categoria = '', $disponibilidad = '') {
        $sql = "SELECT * FROM libros WHERE activo = 1";
        $params = [];

        if (!empty($busqueda)) {
            $sql .= " AND (titulo LIKE :busqueda OR autor LIKE :busqueda OR isbn LIKE :busqueda)";
            $params[':busqueda'] = "%" . $busqueda . "%";
        }
        if (!empty($categoria)) {
            $sql .= " AND categoria = :categoria";
            $params[':categoria'] = $categoria;
        }
        if ($disponibilidad === 'disponibles') {
            $sql .= " AND stock_disponible > 0";
        } elseif ($disponibilidad === 'agotados') {
            $sql .= " AND stock_disponible = 0";
        }

        $sql .= " ORDER BY id_libro ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // RF1: Registrar nuevo ejemplar
    public function insertar($isbn, $titulo, $autor, $categoria, $stock_total) {
        $sql = "INSERT INTO libros (isbn, titulo, autor, categoria, stock_total, stock_disponible, activo) 
                VALUES (:isbn, :titulo, :autor, :categoria, :stock_total, :stock_disponible, 1)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':isbn' => $isbn,
            ':titulo' => $titulo,
            ':autor' => $autor,
            ':categoria' => $categoria,
            ':stock_total' => $stock_total,
            ':stock_disponible' => $stock_total
        ]);
    }

    // RF1: Modificar ejemplar existente
    public function actualizar($id_libro, $isbn, $titulo, $autor, $categoria, $stock_total, $stock_disponible) {
        $sql = "UPDATE libros SET isbn = :isbn, titulo = :titulo, autor = :autor, 
                categoria = :categoria, stock_total = :stock_total, stock_disponible = :stock_disponible 
                WHERE id_libro = :id_libro";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([
            ':id_libro' => $id_libro,
            ':isbn' => $isbn,
            ':titulo' => $titulo,
            ':autor' => $autor,
            ':categoria' => $categoria,
            ':stock_total' => $stock_total,
            ':stock_disponible' => $stock_disponible
        ]);
    }

    // RF1: Dar de baja ejemplar (Baja lógica para no romper historial de préstamos)
    public function darDeBaja($id_libro) {
        $stmt = $this->conn->prepare("UPDATE libros SET activo = 0 WHERE id_libro = :id");
        return $stmt->execute([':id' => $id_libro]);
    }

    public function obtenerCategorias() {
        $stmt = $this->conn->query("SELECT DISTINCT categoria FROM libros WHERE activo = 1 ORDER BY categoria");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
?>