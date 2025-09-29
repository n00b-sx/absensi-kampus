<?php $base = rtrim(app_config('base_url',''),'/'); ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Edit Event</title>
  <link rel="stylesheet" href="<?=$base?>/assets/css/app.css">
  <style>
    body{font-family:system-ui,sans-serif;max-width:780px;margin:0 auto;padding:24px}
    label{display:block;margin-top:10px;font-weight:600}
    input,textarea{width:100%;padding:10px;font-size:16px}
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    button{padding:10px 14px;font-size:16px;margin-top:16px}
    .muted{color:#666}
    .msg{padding:8px;border-radius:8px;margin:8px 0}
    .ok{background:#e6f4ea;color:#0a7a2f}
    .err{background:#fde8e8;color:#9b1c1c}
  </style>
</head>
<body>
  <p><a href="<?=$base?>/admin/events">&larr; Kembali</a></p>
  <h2>Edit Event #<?= (int)$event['id'] ?></h2>
  <?php if(!empty($success)): ?><div class="msg ok"><?=htmlspecialchars($success)?></div><?php endif;?>
  <?php if(!empty($errors)): ?><div class="msg err"><?php foreach($errors as $e){echo htmlspecialchars($e).'<br>';}?></div><?php endif;?>

  <form action="<?=$base?>/admin/events/update" method="post">
    <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
    <input type="hidden" name="id" value="<?=$event['id']?>">
    <label>Judul *</label>
    <input type="text" name="title" value="<?=htmlspecialchars($event['title'])?>" required>
    <label>Deskripsi</label>
    <textarea name="description" rows="3"><?=htmlspecialchars($event['description'] ?? '')?></textarea>
    <label>Lokasi</label>
    <input type="text" name="location" value="<?=htmlspecialchars($event['location'] ?? '')?>">
    <div class="row">
      <div>
        <label>Latitude</label>
        <input type="text" name="latitude" value="<?=htmlspecialchars((string)($event['latitude'] ?? ''))?>">
      </div>
      <div>
        <label>Longitude</label>
        <input type="text" name="longitude" value="<?=htmlspecialchars((string)($event['longitude'] ?? ''))?>">
      </div>
    </div>
    <div class="row">
      <div>
        <label>Mulai *</label>
        <input type="datetime-local" name="start_at" value="<?=str_replace(' ','T',substr($event['start_at'],0,16))?>" required>
      </div>
      <div>
        <label>Selesai *</label>
        <input type="datetime-local" name="end_at" value="<?=str_replace(' ','T',substr($event['end_at'],0,16))?>" required>
      </div>
    </div>
    <button type="submit">Simpan Perubahan</button>
  </form>
</body>
</html>
