<?php
try {
  $pdo = new PDO('mysql:host=127.0.0.1;dbname=estructurasjg;charset=utf8mb4','root','', [PDO::ATTR_TIMEOUT=>3]);
  echo "OK\n";
} catch (Throwable $e) {
  echo "ERR: " . $e->getMessage() . "\n";
}
