<?php

declare(strict_types=1);

/**
 * Prepara los datos visibles del modulo de configuracion de usuarios/permisos.
 */
function prepararDatosModuloConfiguracion(
    array $formularioUsuario,
    array $usuarios,
    array $roles,
    array $auditoriaEventos = [],
    string $mensajeExito = '',
    string $mensajeError = ''
): array
{
    $rolesOpciones = array_map(
        static fn(array $rol): array => [
            'id' => (int) ($rol['id_rol'] ?? 0),
            'nombre' => (string) ($rol['nombre'] ?? ''),
        ],
        $roles
    );

    $usuariosNormalizados = array_map(
        static function (array $usuario): array {
            $marcaTiempo = strtotime((string) ($usuario['ultimo_acceso'] ?? ''));
            $ultimoAcceso = $marcaTiempo !== false ? date('d/m/Y H:i', $marcaTiempo) : 'Sin acceso';

            return [
                'id_usuario' => (int) ($usuario['id_usuario'] ?? 0),
                'nombre' => (string) ($usuario['nombre'] ?? ''),
                'usuario' => (string) ($usuario['usuario'] ?? ''),
                'rol' => (string) ($usuario['rol_nombre'] ?? ''),
                'estado' => ((int) ($usuario['estado'] ?? 0)) === 1 ? 'Activo' : 'Inactivo',
                'ultimo_acceso' => $ultimoAcceso,
            ];
        },
        $usuarios
    );

    $rolesConPermisos = array_map(
        static function (array $rol): array {
            $nombreRol = strtolower((string) ($rol['nombre'] ?? ''));
            $esRolEmpleado = $nombreRol === 'empleado';

            return [
                'id_rol' => (int) ($rol['id_rol'] ?? 0),
                'nombre' => (string) ($rol['nombre'] ?? ''),
                'permisos' => [
                    'registrar_productos' => (int) ($rol['p_registrar_productos'] ?? 0) === 1,
                    'modificar_productos' => (int) ($rol['p_modificar_productos'] ?? 0) === 1,
                    'registrar_movimientos' => (int) ($rol['p_registrar_movimientos'] ?? 0) === 1,
                    'consultar_movimientos' => (int) ($rol['p_consultar_movimientos'] ?? 0) === 1,
                    'gestionar_roles' => $esRolEmpleado ? false : ((int) ($rol['p_gestionar_roles'] ?? 0) === 1),
                    'configuracion' => $esRolEmpleado ? false : ((int) ($rol['p_configuracion'] ?? 0) === 1),
                ],
            ];
        },
        $roles
    );

    $auditoriaNormalizada = array_map(
        static function (array $evento): array {
            $marcaTiempo = strtotime((string) ($evento['fecha_evento'] ?? ''));
            $fechaEvento = $marcaTiempo !== false ? date('d/m/Y H:i', $marcaTiempo) : 'Sin fecha';

            $detalleRaw = (string) ($evento['detalle_json'] ?? '');
            $detalleTexto = '';
            if ($detalleRaw !== '') {
                $detalleDecodificado = json_decode($detalleRaw, true);
                if (is_array($detalleDecodificado)) {
                    $detalleTexto = json_encode($detalleDecodificado, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
                } else {
                    $detalleTexto = $detalleRaw;
                }
            }

            return [
                'fecha' => $fechaEvento,
                'usuario' => (string) ($evento['usuario'] ?? ''),
                'modulo' => (string) ($evento['modulo'] ?? ''),
                'accion' => (string) ($evento['accion'] ?? ''),
                'entidad' => (string) ($evento['entidad'] ?? ''),
                'id_entidad' => (string) (($evento['id_entidad'] ?? '') !== null ? $evento['id_entidad'] : ''),
                'detalle' => $detalleTexto,
            ];
        },
        $auditoriaEventos
    );

    return [
        'tituloPagina' => 'Configuracion',
        'tituloSeccion' => 'Configuracion de usuarios y permisos',
        'descripcionSeccion' => 'Crea y modifica usuarios del sistema, y controla los permisos de cada rol.',
        'moduloActivo' => '',
        'resaltarConfiguracion' => true,
        'formularioUsuario' => $formularioUsuario,
        'rolesOpciones' => $rolesOpciones,
        'usuarios' => $usuariosNormalizados,
        'rolesConPermisos' => $rolesConPermisos,
        'auditoriaEventos' => $auditoriaNormalizada,
        'mensajeExito' => $mensajeExito,
        'mensajeError' => $mensajeError,
    ];
}
