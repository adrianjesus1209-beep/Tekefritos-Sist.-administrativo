<?php

class AuthController {
    public static function login() {
        $error = "";
        $correo = "";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $correo = trim($_POST['correo'] ?? '');
            $clave = $_POST['clave'] ?? '';

            if (empty($correo) || empty($clave)) {
                $error = "Por favor, rellena todos los campos.";
            } else {
                try {
                    $result = Database::query(
                        "SELECT id, nombre, contrasena, rol FROM usuarios WHERE correo = ?",
                        [$correo]
                    );
                    $usuario = Database::fetch($result);

                    if ($usuario && password_verify($clave, $usuario['contrasena'])) {
                        if ($usuario['rol'] === 'cliente') {
                            $error = "Tu cuenta no tiene acceso al sistema.";
                        } else {
                            session_regenerate_id(true);
                            Session::set('usuario_id', $usuario['id']);
                            Session::set('nombre', $usuario['nombre']);
                            Session::set('rol', $usuario['rol']);
                            Session::set('correo', $correo);
                            header("Location: index.php?action=admin");
                            exit();
                        }
                    } else {
                        $error = "El correo o la contrasena son incorrectos.";
                    }
                } catch (Exception $e) {
                    $error = "Error del sistema. Intenta mas tarde.";
                }
            }
        }

        require_once __DIR__ . '/../../public/views/auth/login.php';
    }

    public static function logout() {
        Session::destroy();
        header("Location: index.php?action=login");
        exit();
    }
}