<?php

class Session {
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function isLoggedIn() {
        self::start();
        return isset($_SESSION['usuario_id']);
    }

    public static function get(string $key) {
        self::start();
        return $_SESSION[$key] ?? null;
    }

    public static function set(string $key, mixed $value) {
        self::start();
        $_SESSION[$key] = $value;
    }

    public static function destroy() {
        self::start();
        $_SESSION = [];
        session_destroy();
    }

    public static function rol() {
        return self::get('rol');
    }

    public static function nombre() {
        return self::get('nombre');
    }

    public static function id() {
        return self::get('usuario_id');
    }

    public static function redirectIfNotLoggedIn(string $url = 'index.php?action=login') {
        if (!self::isLoggedIn()) {
            header("Location: $url");
            exit();
        }
    }

    public static function redirectIfNotAdmin(string $url = 'index.php?action=login') {
        self::redirectIfNotLoggedIn($url);
        if (!in_array(self::rol(), ['admin', 'trabajador', 'vendedor'])) {
            header("Location: index.php");
            exit();
        }
    }
}