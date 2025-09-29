<?php
require_once __DIR__ . '/../app/Bootstrap.php';

use App\Controllers\ApiController;
use App\Controllers\PageController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$base = rtrim(app_config('base_url',''),'/');
$path = '/'.ltrim(str_replace($base,'',$uri),'/');
$method = $_SERVER['REQUEST_METHOD'];

$routes = [
  // Pages
  ['GET',  '/',                 [PageController::class,'home']],
  ['GET',  '/admin/qr',         [PageController::class,'adminQr']],
  ['GET',  '/scan',             [PageController::class,'scanPage']],

  // APIs
  ['GET',  '/api/token',        [ApiController::class,'issueToken']],   // ?event_id=1
  ['POST', '/api/checkin',      [ApiController::class,'checkin']],      // JSON: token, identity_number, [lat,lng]
];

foreach ($routes as [$m,$p,$h]) {
  if ($method === $m && rtrim($path,'/') === rtrim($p,'/')) {
    [$cls,$fn] = $h; (new $cls)->{$fn}();
    exit;
  }
}

http_response_code(404);
echo "<h1>404</h1><p>Route not found: {$method} {$path}</p>";
