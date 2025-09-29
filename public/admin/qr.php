<?php
require_once __DIR__ . '/../../app/Bootstrap.php';
\App\Services\Auth::requireAdmin();

$eventId = (int)($_GET['event_id'] ?? 1); // untuk demo
$base = rtrim(app_config('base_url',''),'/');
$refreshIn = (int)app_config('token_refresh_seconds',20);
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>QR Dinamis - Event #<?=htmlspecialchars($eventId)?></title>
  <link rel="stylesheet" href="<?=$base?>/assets/css/app.css">
  <!-- pakai CDN untuk QRCode.js, atau salin ke assets/js/qrcode.min.js -->
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <style>
    body { font-family: system-ui, sans-serif; padding:20px; }
    #qrcode { width: 320px; height: 320px; margin: 20px auto; }
    .muted { color:#666; }
  </style>
</head>
<body>
  <h2>QR Dinamis – Event #<?=$eventId?></h2>
  <div id="qrcode"></div>
  <p id="info" class="muted">Menunggu token…</p>
  <p><a href="<?=$base?>/admin/events/create">+ Tambah Event</a></p>

<script>
const eventId = <?=json_encode($eventId)?>;
const base = <?=json_encode($base)?>;
let qrel;

async function getToken() {
  const res = await fetch(`${base}/api/token?event_id=${eventId}`, {cache:'no-store'});
  if(!res.ok){ document.getElementById('info').textContent='Gagal ambil token'; return; }
  return res.json();
}

function renderQR(payload) {
  const el = document.getElementById('qrcode');
  el.innerHTML = '';
  qrel = new QRCode(el, {
    text: JSON.stringify(payload),
    width: 320, height: 320,
    correctLevel: QRCode.CorrectLevel.M
  });
}

async function loop() {
  const data = await getToken();
  if (!data) return;
  // Payload QR berisi token + event untuk dipindai klien
  renderQR({ t: data.token, e: eventId, exp: data.expires_at });
  document.getElementById('info').textContent =
    `Token kadaluarsa: ${data.expires_at} | refresh tiap ${data.refresh_in}s`;
  setTimeout(loop, (data.refresh_in ?? 20) * 1000);
}
loop();
</script>
</body>
</html>
