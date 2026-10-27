<?php
require_once '../dal/UsuarioDAL.php';
require_once '../dal/PrestamoDAL.php';

class BibliotecaBLL {
    private $usuarioDAL;
    private $prestamoDAL;
    private $tarifaMultaDiaria = 15.00; // $15.00 MXN por día de retraso (RF4)

    public function __construct() {
        $this->usuarioDAL = new UsuarioDAL();
        $this->prestamoDAL = new PrestamoDAL();
    }

    // --- RF2: Gestión de Usuarios ---
    public function listarUsuarios() { return $this->usuarioDAL->listarUsuariosConRol(); }
    public function listarRoles() { return $this->usuarioDAL->obtenerRoles(); }
    
    public function crearUsuario($matricula, $nombre, $email, $id_rol) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El formato del correo electrónico no es válido.");
        }
        return $this->usuarioDAL->registrarUsuario(trim($matricula), trim($nombre), trim($email), (int)$id_rol);
    }

    public function levantarSancion($id_usuario) {
        return $this->usuarioDAL->cambiarEstadoSancion((int)$id_usuario, 0);
    }

    // --- RF3: Procesamiento y Validación de Préstamos ---
    public function listarPrestamosCalculados() {
        $prestamos = $this->prestamoDAL->listarPrestamos();
        $hoy = new DateTime();

        foreach ($prestamos as &$p) {
            $fechaEsperada = new DateTime($p['fecha_devolucion_esperada']);
            $p['dias_retraso'] = 0;
            $p['multa_estimada'] = (float)$p['monto_multa'];

            if ($p['estado'] === 'Activo' && $hoy > $fechaEsperada) {
                $p['dias_retraso'] = $hoy->diff($fechaEsperada)->days;
                $p['multa_estimada'] = $p['dias_retraso'] * $this->tarifaMultaDiaria;
            }
        }
        return $prestamos;
    }

    public function procesarPrestamo($id_usuario, $id_libro) {
        $datosUsuario = $this->prestamoDAL->obtenerDatosValidacionUsuario($id_usuario);
        if (!$datosUsuario) throw new Exception("El usuario seleccionado no existe.");
        if ($datosUsuario['sancionado'] == 1) {
            throw new Exception("Préstamo rechazado (RF4): El usuario tiene un bloqueo temporal por retrasos previos.");
        }
        if ($datosUsuario['activos'] >= $datosUsuario['limite_prestamos']) {
            throw new Exception("Préstamo rechazado (RF3): El usuario ya alcanzó su límite de " . $datosUsuario['limite_prestamos'] . " préstamos activos según su rol.");
        }

        $datosLibro = $this->prestamoDAL->obtenerStockLibro($id_libro);
        if (!$datosLibro || $datosLibro['stock_disponible'] <= 0) {
            throw new Exception("Préstamo rechazado (RF3): No hay ejemplares físicos disponibles de este título.");
        }

        return $this->prestamoDAL->crearPrestamoTransaccional($id_usuario, $id_libro, $datosUsuario['dias_prestamo']);
    }

    // --- RF4: Devolución y Cálculo Automático de Multas ---
    public function procesarDevolucion($id_prestamo) {
        $prestamo = $this->prestamoDAL->obtenerPrestamoPorId($id_prestamo);
        if (!$prestamo || $prestamo['estado'] !== 'Activo') {
            throw new Exception("El préstamo ya fue devuelto o no existe.");
        }

        $hoy = new DateTime();
        $fechaEsperada = new DateTime($prestamo['fecha_devolucion_esperada']);
        $multa = 0.00;
        $aplicarBloqueo = false;

        if ($hoy > $fechaEsperada) {
            $diasRetraso = $hoy->diff($fechaEsperada)->days;
            $multa = $diasRetraso * $this->tarifaMultaDiaria;
            $aplicarBloqueo = true; // Bloquea temporalmente al usuario por entregar tarde
        }

        $this->prestamoDAL->devolverPrestamoTransaccional(
            $id_prestamo, 
            $prestamo['id_libro'], 
            $prestamo['id_usuario'], 
            $multa, 
            $aplicarBloqueo
        );

        return ['multa' => $multa, 'bloqueado' => $aplicarBloqueo];
    }
}
?>