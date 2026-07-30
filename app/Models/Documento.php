<?php

class Documento {
    public static function getById(int $id) {
        $res = Database::query("SELECT * FROM documentos WHERE id = ?", [$id]);
        return Database::fetch($res);
    }

    public static function create(array $data) {
        Database::query(
            "INSERT INTO documentos (id_contacto, nombre_archivo, archivo_real, tipo, id_usuario, estado)
             VALUES (?, ?, ?, ?, ?, 'Disponible')",
            [
                $data['id_contacto'] ?? null,
                $data['nombre_archivo'],
                $data['archivo_real'],
                $data['tipo'] ?? 'Otro',
                $data['id_usuario'] ?? null
            ]
        );
        return Database::insertId();
    }

    public static function delete(int $id) {
        Database::query("UPDATE documentos SET estado = 'Inactivo' WHERE id = ?", [$id]);
    }

    public static function eliminarPermanente(int $id) {
        $doc = self::getById($id);
        if ($doc) {
            $archivo = $doc['archivo_real'];
            $rutas = [
                __DIR__ . '/../../public/uploads/documentos/' . $archivo,
                __DIR__ . '/../../public/uploads/facturas_generadas/' . $archivo,
            ];
            foreach ($rutas as $ruta) {
                if (file_exists($ruta)) {
                    @unlink($ruta);
                }
            }
        }
        Database::query("DELETE FROM documentos WHERE id = ?", [$id]);
    }
}
