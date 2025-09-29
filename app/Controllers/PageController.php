<?php
namespace App\Controllers;

class PageController {
  public function home() {
    echo "<h1>Absensi Kampus</h1><p><a href='./admin/qr'>Tampilkan QR</a> | <a href='./scan'>Scan</a></p>";
  }
  public function adminQr() {
    require __DIR__ . '/../../public/admin/qr.php';
  }
  public function scanPage() {
    require __DIR__ . '/../../public/scan.php';
  }
}
