<?php

class Database {
    private static ?\mysqli $conexion = null;

    public static function getConnection() {
        if (self::$conexion === null) {
            $host = 'localhost';
            $user = 'root';
            $pass = '';
            $name = 'tekefritos';

            self::$conexion = mysqli_connect($host, $user, $pass, $name);

            if (!self::$conexion) {
                die("Error de conexion: " . mysqli_connect_error());
            }

            mysqli_set_charset(self::$conexion, "utf8mb4");
        }
        return self::$conexion;
    }

    public static function query(string $sql, array $params = []) {
        $db = self::getConnection();

        if (empty($params)) {
            $resultado = mysqli_query($db, $sql);
            if ($resultado === false) {
                throw new Exception("Error en consulta: " . mysqli_error($db));
            }
            return ['stmt' => $resultado, 'db' => $db];
        }

        $tipos = '';
        $valores = [];
        foreach ($params as $p) {
            if (is_int($p)) $tipos .= 'i';
            elseif (is_float($p)) $tipos .= 'd';
            else $tipos .= 's';
            $valores[] = $p;
        }

        $stmt = mysqli_prepare($db, $sql);
        if (!$stmt) {
            throw new Exception("Error al preparar: " . mysqli_error($db));
        }

        mysqli_stmt_bind_param($stmt, $tipos, ...$valores);
        mysqli_stmt_execute($stmt);

        return ['stmt' => $stmt, 'db' => $db];
    }

    public static function fetch(array $result) {
        if ($result['stmt'] instanceof mysqli_result) {
            return mysqli_fetch_assoc($result['stmt']);
        }
        $res = mysqli_stmt_get_result($result['stmt']);
        return $res ? mysqli_fetch_assoc($res) : null;
    }

    public static function fetchAll(array $result) {
        if ($result['stmt'] instanceof mysqli_result) {
            return mysqli_fetch_all($result['stmt'], MYSQLI_ASSOC);
        }
        $res = mysqli_stmt_get_result($result['stmt']);
        return $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    }

    public static function insertId() {
        return mysqli_insert_id(self::getConnection());
    }

    public static function fetchColumn(string $sql, array $params = []) {
        $result = self::query($sql, $params);
        if ($result['stmt'] instanceof mysqli_result) {
            $row = mysqli_fetch_array($result['stmt']);
            return $row ? $row[0] : null;
        }
        mysqli_stmt_bind_result($result['stmt'], $val);
        $fetched = mysqli_stmt_fetch($result['stmt']);
        return $fetched ? $val : null;
    }

    public static function getResult(array $result) {
        if ($result['stmt'] instanceof mysqli_result) {
            return $result['stmt'];
        }
        return mysqli_stmt_get_result($result['stmt']);
    }
}