<?php
require_once __DIR__ . '/app/configuracion/conexion.php';
$c = obtenerConexion();
$queries = [
'productos' => "SELECT id_producto,codigo,descripcion FROM productos ORDER BY CAST(codigo AS UNSIGNED), id_producto",
'entradas_codigos' => "SELECT p.codigo, p.descripcion, COUNT(*) AS veces FROM detalle_compras dc INNER JOIN productos p ON p.id_producto=dc.id_producto GROUP BY p.codigo,p.descripcion ORDER BY CAST(p.codigo AS UNSIGNED), p.codigo",
'salidas_codigos' => "SELECT p.codigo, p.descripcion, COUNT(*) AS veces FROM detalle_ventas dv INNER JOIN productos p ON p.id_producto=dv.id_producto GROUP BY p.codigo,p.descripcion ORDER BY CAST(p.codigo AS UNSIGNED), p.codigo",
'productos_sin_entradas' => "SELECT p.codigo,p.descripcion FROM productos p LEFT JOIN detalle_compras dc ON dc.id_producto=p.id_producto WHERE dc.id_producto IS NULL ORDER BY CAST(p.codigo AS UNSIGNED), p.codigo",
'productos_sin_salidas' => "SELECT p.codigo,p.descripcion FROM productos p LEFT JOIN detalle_ventas dv ON dv.id_producto=p.id_producto WHERE dv.id_producto IS NULL ORDER BY CAST(p.codigo AS UNSIGNED), p.codigo",
];
foreach($queries as $name=>$sql){
  echo "\n=== $name ===\n";
  $rows = $c->query($sql)->fetchAll();
  foreach($rows as $r){ echo json_encode($r, JSON_UNESCAPED_UNICODE)."\n"; }
  if (!$rows) echo "(sin filas)\n";
}
?>
