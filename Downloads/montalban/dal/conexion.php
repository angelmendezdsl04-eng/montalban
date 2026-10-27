<?php
class Conexion {
    private $host = "localhost"; // En Hostinger casi siempre es localhost
    private $db_name = "u802932294_biblioteca"; // Pon el nombre con todo y prefijo
    private $username = "u802932294_admin_biblio"; // Pon el usuario con todo y prefijo
    private $password = "Yir5N#DeR4.mv7r"; // La contraseña que creaste

    public $conn;

    public function obtenerConexion() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>