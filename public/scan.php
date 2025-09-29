<?php
require_once __DIR__ . '/../app/Bootstrap.php';
$base = rtrim(app_config('base_url',''),'/');
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Scan Absensi</title>
  <link rel="stylesheet" href="<?=$base?>/assets/css/app.css">
  <!-- Library scanner (html5-qrcode) via CDN -->
  <script src="https://unpkg.com/html5-qrcode" type="text/javascript"></script>
  <style>
    body { font-family: system-ui, sans-serif; padding: 16px; }
    #reader { width: 360px; margin: 12px auto; }
    form { margin-top: 16px; }
    input, button { padding: 10px; font-size: 16px; }
  </style>
</head>
<body>
  <h2>Scan Absensi</h2>

  <div id="reader"></div>

  <form id="manualForm">
    <h3>Atau masukkan token & NIM/NIP/NIK</h3>
    <input type="text" id="token" placeholder="Token" required />
    <input type="text" id="ident" placeholder="Identity Number (NIM/NIP/NIK)" required />
    <button type="submit">Kirim</button>
  </form>

  <pre id="log" style="margin-top:16px; white-space:pre-wrap;"></pre>

<script>
const base = <?=json_encode($base)?>;
const log = (t)=>document.getElementById('log').textContent=t;

async function checkin(token, ident, coords=null) {
  const payload = { token, identity_number: ident };
  if (coords) { payload.lat = coords.latitude; payload.lng = coords.longitude; }
  const res = await fetch(`${base}/api/checkin`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  log((res.ok?'✅':'❌') + ' ' + (data.message||'') + (data.at?(' @ '+data.at):''));
}

async function handleQr(decodedText) {
  try {
    const obj = JSON.parse(decodedText);
    // {t:token, e:eventId, exp:datetime}
    const ident = prompt("Masukkan NIM/NIP/NIK Anda:");
    if (!ident) return;
    let coords = null;
    if (navigator.geolocation) {
      try {
        coords = await new Promise((ok,fail)=>{
          navigator.geolocation.getCurrentPosition(
            p=>ok(p.coords),
            err=>ok(null),
            {enableHighAccuracy:true, timeout:4000}
          );
        });
      } catch(e){}
    }
    await checkin(obj.t, ident, coords);
  } catch(e) {
    log('QR tidak valid');
  }
}

function startScanner() {
  const html5QrCode = new Html5Qrcode("reader");
  html5QrCode.start(
    {facingMode:"environment"},
    {fps:10, qrbox: {width: 240, height: 240}},
    (decodedText) => {
      html5QrCode.stop().then(()=>handleQr(decodedText));
    },
    (errorMessage) => {}
  ).catch(err => log('Tidak bisa membuka kamera: '+err));
}

startScanner();

document.getElementById('manualForm').addEventListener('submit', async (e)=>{
  e.preventDefault();
  const token = document.getElementById('token').value.trim();
  const ident = document.getElementById('ident').value.trim();
  let coords = null;
  if (navigator.geolocation) {
    try {
      coords = await new Promise((ok,fail)=>{
        navigator.geolocation.getCurrentPosition(
          p=>ok(p.coords),
          err=>ok(null),
          {enableHighAccuracy:true, timeout:4000}
        );
      });
    } catch(e){}
  }
  await checkin(token, ident, coords);
});
</script>
</body>
</html>
