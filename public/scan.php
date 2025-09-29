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

async function postCheckin(payload) {
  const res = await fetch(`${base}/api/checkin`, {
    method:'POST', headers:{'Content-Type':'application/json'},
    body: JSON.stringify(payload)
  });
  const data = await res.json();
  return { ok: res.ok, data };
}

async function checkin(token, ident, coords=null) {
  const payload = { token, identity_number: ident };
  if (coords) { payload.lat = coords.latitude; payload.lng = coords.longitude; }

  let r = await postCheckin(payload);
  if (r.ok) {
    log('✅ ' + (r.data.message||'Hadir tercatat') + (r.data.at?(' @ '+r.data.at):''));
    return;
  }

  // Jika peserta belum ada, minta data singkat dan daftar otomatis
  if (r.data && r.data.need_register) {
    const name = prompt("Nama lengkap Anda?");
    if (!name) { log('❌ Pendaftaran dibatalkan'); return; }
    const identity_type = prompt("Jenis identitas? (NIM/NIP/NIK)", "NIM") || "NIM";
    const category = prompt("Kategori? (mahasiswa/dosen/tendik/umum)", "mahasiswa") || "mahasiswa";
    payload.name = name;
    payload.identity_type = identity_type.toUpperCase();
    payload.category = category.toLowerCase();
    // TODO: kalau ingin pilih prodi, bisa tambah dropdown di UI
    r = await postCheckin(payload);
    if (r.ok) {
      log('✅ ' + (r.data.message||'Hadir tercatat') + (r.data.at?(' @ '+r.data.at):''));
    } else {
      log('❌ ' + (r.data && r.data.message ? r.data.message : 'Gagal mencatat hadir'));
    }
    return;
  }

  log('❌ ' + (r.data && r.data.message ? r.data.message : 'Gagal mencatat hadir'));
}
</script>
</body>
</html>
