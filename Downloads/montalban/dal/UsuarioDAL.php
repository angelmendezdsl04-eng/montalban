<?php
require_once 'conexion.php';

class UsuarioDAL {
    private $conn;

    public function __construct() {
        $conexion = new Conexion();
        $this->conn = $conexion->obtenerConexion();
    }

    public function listarUsuariosConRol() {
        $sql = "SELECT u.*, r.nombre_rol, r.limite_prestamos, r.dias_prestamo,
                (SELECT COUNT(*) FROM prestamos p WHERE p.id_usuario = u.id_usuario AND p.estado = 'Activo') AS prestamos_activos
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                ORDER BY u.id_usuario ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerRoles() {
        return $this->conn->query("SELECT * FROM roles")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function registrarUsuario($matricula, $nombre, $email, $id_rol) {
        $sql = "INSERT INTO usuarios (matricula, nombre, email, id_rol) VALUES (:m, :n, :e, :r)";
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([':m' => $matricula, ':n' => $nombre, ':e' => $email, ':r' => $id_rol]);
    }

    public function cambiarEstadoSancion($id_usuario, $sancionado) {
        $stmt = $this->conn->prepare("UPDATE usuarios SET sancionado = :s WHERE id_usuario = :id");
        return $stmt->execute([':s' => $sancionado, ':id' => $id_usuario]);
    }
}
?>