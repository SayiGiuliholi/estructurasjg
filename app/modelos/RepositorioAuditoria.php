<?php

declare(strict_types=1);

require_once __DIR__ . '/../configuracion/conexion.php';

final class RepositorioAuditoria
{
    private ?bool $tablaDisponible = null;

    public function registrarEvento(array $evento): void
    {
        $this->prepararTabla();

        $conexion = obtenerConexion();
        $sql = <<<SQL
            INSERT INTO auditoria_eventos (
                id_usuario,
                usuario,
                modulo,
                accion,
                entidad,
                id_entidad,
                detalle_json
            ) VALUES (
                :id_usuario,
                :usuario,
                :modulo,
                :accion,
                :entidad,
                :id_entidad,
                :detalle_json
            )
        SQL;

        $detalle = $evento['detalle'] ?? null;
        $detalleJson = $detalle === null ? null : json_encode($detalle, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $sentencia = $conexion->prepare($sql);
        $sentencia->execute([
            'id_usuario' => (int) ($evento['id_usuario'] ?? 0),
            'usuario' => (string) ($evento['usuario'] ?? ''),
            'modulo' => (string) ($evento['modulo'] ?? ''),
            'accion' => (string) ($evento['accion'] ?? ''),
            'entidad' => (string) ($evento['entidad'] ?? ''),
            'id_entidad' => isset($evento['id_entidad']) ? (int) $evento['id_entidad'] : null,
            'detalle_json' => $detalleJson,
        ]);
    }

    public function obtenerEventos(int $limite = 200): array
    {
        $this->prepararTabla();

        $conexion = obtenerConexion();
        $sql = <<<SQL
            SELECT
                id_evento,
                fecha_evento,
                id_usuario,
                usuario,
                modulo,
                accion,
                entidad,
                id_entidad,
                detalle_json
            FROM auditoria_eventos
            ORDER BY id_evento DESC
            LIMIT :limite
        SQL;

        $sentencia = $conexion->prepare($sql);
        $sentencia->bindValue('limite', max(1, min(500, $limite)), PDO::PARAM_INT);
        $sentencia->execute();
        return $sentencia->fetchAll();
    }

    private function prepararTabla(): void
    {
        if ($this->tablaDisponible === true) {
            return;
        }

        $conexion = obtenerConexion();
        $sql = <<<SQL
            CREATE TABLE IF NOT EXISTS auditoria_eventos (
                id_evento BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                fecha_evento DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                id_usuario INT NOT NULL,
                usuario VARCHAR(120) NOT NULL,
                modulo VARCHAR(80) NOT NULL,
                accion VARCHAR(120) NOT NULL,
                entidad VARCHAR(120) NOT NULL,
                id_entidad INT NULL,
                detalle_json JSON NULL,
                INDEX idx_fecha_evento (fecha_evento),
                INDEX idx_usuario (id_usuario),
                INDEX idx_modulo_accion (modulo, accion)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        SQL;
        $conexion->exec($sql);
        $this->tablaDisponible = true;
    }
}

