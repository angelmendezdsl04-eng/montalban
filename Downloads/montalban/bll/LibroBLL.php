<?php
require_once '../dal/LibroDAL.php';

class LibroBLL {
    private $libroDAL;

    public function __construct() {
        $this->libroDAL = new LibroDAL();
    }

    public function listarLibros($busqueda = '', $categoria = '', $disponibilidad = '') {
        return $this->libroDAL->buscarLibros(trim($busqueda), trim($categoria), trim($disponibilidad));
    }

    public function obtenerCategorias() {
        return $this->libroDAL->obtenerCategorias();
    }

    public function registrarEjemplar($isbn, $titulo, $autor, $categoria, $stock_total) {
        if ($stock_total < 0) {
            throw new Exception("El stock total no puede ser negativo.");
        }
        return $this->libroDAL->insertar(trim($isbn), trim($titulo), trim($autor), trim($categoria), (int)$stock_total);
    }

    public function modificarEjemplar($id_libro, $isbn, $titulo, $autor, $categoria, $stock_total, $stock_disponible) {
        if ($stock_disponible > $stock_total || $stock_disponible < 0) {
            throw new Exception("Regla de negocio violada: El stock disponible no puede ser mayor al stock total ni menor a cero.");
        }
        return $this->libroDAL->actualizar((int)$id_libro, trim($isbn), trim($titulo), trim($autor), trim($categoria), (int)$stock_total, (int)$stock_disponible);
    }

    public function eliminarEjemplar($id_libro) {
        return $this->libroDAL->darDeBaja((int)$id_libro);
    }
}
?>