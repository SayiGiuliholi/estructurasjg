<?php
require_once __DIR__ . '/app/configuracion/conexion.php';
$c = obtenerConexion();
$sql = "SELECT r.id_rol,r.nombre,r.p_registrar_productos,r.p_modificar_productos,r.p_registrar_movimientos,r.p_consultar_movimientos,r.p_gestionar_roles,r.p_configuracion FROM roles r ORDER BY r.id_rol";
foreach($c->query($sql)->fetchAll() as $r){ echo json_encode($r, JSON_UNESCAPED_UNICODE).PHP_EOL; }
?>
