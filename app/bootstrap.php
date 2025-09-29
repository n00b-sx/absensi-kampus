<?php
// app/Bootstrap.php
spl_autoload_register(function($class) {
  $prefix = 'App\\';
  $baseDir = __DIR__ . DIRECTORY_SEPARATOR;
  if (strncmp($prefix, $class, strlen($prefix)) !== 0) return;
  $relative = substr($class, strlen($prefix));
  $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';
  if (file_exists($file)) require $file;
});
require_once __DIR__.'/Helpers.php';

$config = require __DIR__ . '/../config/config.php';
$pdo    = require __DIR__ . '/../config/db.php';

function app_config(string $key, $default=null) {
  global $config; return $config[$key] ?? $default;
}
function app_pdo(): PDO { global $pdo; return $pdo; }

session_start();
