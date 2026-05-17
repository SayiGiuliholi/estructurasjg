-- Migracion de seguridad y hardening de esquema
-- Ejecutar una sola vez en el entorno correspondiente.

-- 1) Columna de estado para productos (si no existe)
ALTER TABLE productos
    ADD COLUMN IF NOT EXISTS estado TINYINT(1) NOT NULL DEFAULT 1 AFTER fecha;

-- 2) Soporte de traslado en motivo de salida
ALTER TABLE ventas
    MODIFY COLUMN motivo_salida ENUM('normal','devolucion','fallo','traslado') NOT NULL DEFAULT 'normal';

