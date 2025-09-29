<?php
require_once __DIR__ . '/../../app/Bootstrap.php';
\App\Services\Auth::requireAdmin();

$base = rtrim(\app_config('base_url',''),'/');
$errs = $errors ?? [];
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Tambah Event</title>
  <link rel="stylesheet" href="<?=$base?>/assets/css/app.css">
  <style>
    body { font-family: system-ui, sans-serif; max-width: 780px; margin: 0 auto; padding: 24px; }
    .row { display: grid; grid-template-columns: 1fr 1fr; gap:12px; }
    label { display:block; font-weight:600; margin-top:12px; }
    input, textarea { width:100%; padding:10px; font-size:16px; }
    button { padding:10px 14px; font-size:16px; margin-top:16px; }
    .card { padding:14px; border:1px solid #ddd; border-radius:10px; margin:12px 0; background:#fafafa; }
    .error { color:#b00020; }
    .success { color:#0a7a2f; }
    small.muted{ color:#666; }
  </style>
</head>
<body>
  <h2>Tambah Event</h2>

  <?php if (!empty($errs)): ?>
    <div class="card error">
      <strong>Periksa isian:</strong>
      <ul><?php foreach($errs as $e){ echo "<li>".htmlspecialchars($e)."</li>"; } ?></ul>
    </div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="card success"><?=htmlspecialchars($success)?></div>
  <?php endif; ?>

  <form action="<?=$base?>/admin/events/store" method="post">
    <input type="hidden" name="_csrf" value="<?=csrf_token()?>">

    <label>Judul Event *</label>
    <input type="text" name="title" value="<?=old('title')?>" required>

    <label>Deskripsi</label>
    <textarea name="description" rows="3"><?=old('description')?></textarea>

    <label>Lokasi (nama tempat)</label>
    <input type="text" name="location" value="<?=old('location')?>">

    <div class="row">
      <div>
        <label>Latitude <small class="muted">(opsional)</small></label>
        <input type="text" id="lat" name="latitude" value="<?=old('latitude')?>" placeholder="-1.5012345">
      </div>
      <div>
        <label>Longitude <small class="muted">(opsional)</small></label>
        <input type="text" id="lng" name="longitude" value="<?=old('longitude')?>" placeholder="124.8412345">
      </div>
    </div>

    <button type="button" id="btnGeo">📍 Ambil Koordinat Saat Ini</button>
    <small class="muted">Gunakan jika event berlangsung di lokasi Anda saat ini.</small>

    <div class="row">
      <div>
        <label>Mulai *</label>
        <input type="datetime-local" name="start_at" value="<?=old('start_at')?>" required>
      </div>
      <div>
        <label>Selesai *</label>
        <input type="datetime-local" name="end_at" value="<?=old('end_at')?>" required>
      </div>
    </div>

    <button type="submit">Simpan Event</button>
    <a href="<?=$base?>/admin/qr?event_id=1" style="margin-left:8px;">➡️ Lihat QR (ganti event_id sesuai hasil)</a>
  </form>

<script>
document.getElementById('btnGeo').addEventListener('click', async ()=>{
  if(!navigator.geolocation){ alert('Geolokasi tidak didukung'); return; }
  navigator.geolocation.getCurrentPosition(
    (pos)=>{
      document.getElementById('lat').value = pos.coords.latitude.toFixed(7);
      document.getElementById('lng').value = pos.coords.longitude.toFixed(7);
    },
    (err)=>alert('Gagal ambil lokasi: '+err.message),
    {enableHighAccuracy:true, timeout:5000}
  );
});
</script>
</body>
</html>
