-- Crea el rol Superadmin y asigna la cuenta admin principal.
-- Ejecutar una sola vez.

INSERT INTO roles (
    nombre,
    p_registrar_productos,
    p_modificar_productos,
    p_registrar_movimientos,
    p_consultar_movimientos,
    p_gestionar_roles,
    p_configuracion
)
SELECT
    'Superadmin',
    1, 1, 1, 1, 1, 1
WHERE NOT EXISTS (
    SELECT 1 FROM roles WHERE LOWER(nombre) = 'superadmin'
);

SET @id_rol_superadmin := (
    SELECT id_rol
    FROM roles
    WHERE LOWER(nombre) = 'superadmin'
    LIMIT 1
);

-- Mueve la cuenta "admin" al rol Superadmin (si existe).
UPDATE usuarios
SET id_rol = @id_rol_superadmin
WHERE LOWER(usuario) = 'admin'
  AND @id_rol_superadmin IS NOT NULL;

