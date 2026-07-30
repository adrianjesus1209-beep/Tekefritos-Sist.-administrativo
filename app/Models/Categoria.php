<?php

class Categoria {
    public static function create(array $data) {
        Database::query(
            "INSERT INTO categorias (nombre, descripcion, orden) VALUES (?, ?, ?)",
            [$data['nombre'], $data['descripcion'] ?? '', $data['orden'] ?? 0]
        );
        return Database::insertId();
    }

    public static function update(int $id, array $data) {
        Database::query(
            "UPDATE categorias SET nombre=?, descripcion=?, orden=? WHERE id=?",
            [$data['nombre'], $data['descripcion'] ?? '', $data['orden'] ?? 0, $id]
        );
    }

    public static function delete(int $id) {
        Database::query("UPDATE productos SET categoria_id = NULL WHERE categoria_id = ?", [$id]);
        Database::query("DELETE FROM categorias WHERE id = ?", [$id]);
    }
}
