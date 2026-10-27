<?php
require_once 'conexion.php';

class PrestamoDAL {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->obtenerConexion();
    }

    public function listarPrestamos() {
        $sql = "SELECT p.*, u.nombre AS nombre_usuario, u.matricula, r.nombre_rol, l.titulo AS titulo_libro, l.isbn
                FROM prestamos p
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                INNER JOIN roles r ON u.id_rol = r.id_rol
                INNER JOIN libros l ON p.id_libro = l.id_libro
                ORDER BY p.estado ASC, p.fecha_devolucion_esperada ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerDatosValidacionUsuario($id_usuario) {
        $sql = "SELECT u.sancionado, r.limite_prestamos, r.dias_prestamo,
                (SELECT COUNT(*) FROM prestamos p WHERE p.id_usuario = u.id_usuario AND p.estado = 'Activo') AS activos
                FROM usuarios u INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE u.id_usuario = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $id_usuario]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerStockLibro($id_libro) {
        $stmt = $this->conn->prepare("SELECT stock_disponible, titulo FROM libros WHERE id_libro = :id AND activo = 1");
        $stmt->execute([':id' => $id_libro]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // RF3: Transacción atómica para crear préstamo y descontar stock
    public function crearPrestamoTransaccional($id_usuario, $id_libro, $dias_prestamo) {
        try {
            $this->conn->beginTransaction();

            $sqlPrestamo = "INSERT INTO prestamos (id_usuario, id_libro, fecha_prestamo, fecha_devolucion_esperada, estado)
                            VALUES (:u, :l, CURDATE(), DATE_ADD(CURDATE(), INTERVAL :dias DAY), 'Activo')";
            $stmt1 = $this->conn->prepare($sqlPrestamo);
            $stmt1->execute([':u' => $id_usuario, ':l' => $id_libro, ':dias' => $dias_prestamo]);

            $sqlStock = "UPDATE libros SET stock_disponible = stock_disponible - 1 WHERE id_libro = :l";
            $stmt2 = $this->conn->prepare($sqlStock);
            $stmt2->execute([':l' => $id_libro]);

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }

    public function obtenerPrestamoPorId($id_prestamo) {
        $stmt = $this->conn->prepare("SELECT * FROM prestamos WHERE id_prestamo = :id");
        $stmt->execute([':id' => $id_prestamo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // RF4: Transacción atómica para devolver libro, registrar multa, sancionar si aplica y reponer stock
    public function devolverPrestamoTransaccional($id_prestamo, $id_libro, $id_usuario, $multa, $aplicarBloqueo) {
        try {
            $this->conn->beginTransaction();

            $sqlDev = "UPDATE prestamos SET fecha_devolucion_real = CURDATE(), monto_multa = :multa, estado = 'Devuelto' 
                       WHERE id_prestamo = :id";
            $stmt1 = $this->conn->prepare($sqlDev);
            $stmt1->execute([':multa' => $multa, ':id' => $id_prestamo]);

            $sqlStock = "UPDATE libros SET stock_disponible = stock_disponible + 1 WHERE id_libro = :l";
            $stmt2 = $this->conn->prepare($sqlStock);
            $stmt2->execute([':l' => $id_libro]);

            if ($aplicarBloqueo) {
                $stmt3 = $this->conn->prepare("UPDATE usuarios SET sancionado = 1 WHERE id_usuario = :u");
                $stmt3->execute([':u' => $id_usuario]);
            }

            $this->conn->commit();
            return true;
        } catch (Exception $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
?>