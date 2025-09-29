<?php $base = rtrim(app_config('base_url',''),'/'); $q = trim($_GET['q'] ?? ''); ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Daftar Event</title>
  <link rel="stylesheet" href="<?=$base?>/assets/css/app.css">
  <style>
    body{font-family:system-ui,sans-serif;max-width:960px;margin:0 auto;padding:24px}
    table{width:100%; border-collapse:collapse}
    th,td{border-bottom:1px solid #eee; padding:10px; text-align:left; vertical-align:top}
    .top{display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap}
    .muted{color:#666}
    .ok{color:#0a7a2f} .err{color:#b00020}
    form.inline{display:inline}
    button{padding:6px 10px}
    .search{display:flex; gap:8px; align-items:center}
    .search input{padding:8px; font-size:14px}
  </style>
</head>
<body>
  <div class="top">
    <h2>Daftar Event</h2>
    <div>
      <span class="muted">Login sebagai: <?=htmlspecialchars($me['name'])?> (<?=htmlspecialchars($me['email'])?>)</span>
      <form class="inline" action="<?=$base?>/logout" method="post">
        <button type="submit">Logout</button>
      </form>
    </div>
  </div>

  <p>
    <a href="<?=$base?>/">🏠 Beranda</a> ·
    <a href="<?=$base?>/admin/events/create">➕ Tambah Event</a>
  </p>

  <form method="get" class="search" action="<?=$base?>/admin/events">
    <input type="text" name="q" placeholder="Cari judul/kode/lokasi atau tanggal (YYYY-MM-DD)" value="<?=htmlspecialchars($q)?>">
    <button type="submit">Cari</button>
    <?php if($q!==''): ?><a href="<?=$base?>/admin/events" style="margin-left:6px">Reset</a><?php endif; ?>
  </form>

  <?php if($msg = flash('success')): ?><p class="ok"><?=htmlspecialchars($msg)?></p><?php endif;?>
  <?php if($errs = flash('errors')): ?><p class="err"><?=implode(' ', array_map('htmlspecialchars',$errs))?></p><?php endif;?>

  <table>
    <thead>
      <tr><th>ID</th><th>Judul</th><th>Waktu</th><th>Lokasi</th><th>Aksi</th></tr>
    </thead>
    <tbody>
      <?php foreach($events as $ev): ?>
      <tr>
        <td>#<?= (int)$ev['id'] ?></td>
        <td><?= htmlspecialchars($ev['title']) ?><br><small class="muted"><?= htmlspecialchars($ev['code']) ?></small></td>
        <td>
          <div><?= htmlspecialchars($ev['start_at']) ?> → <?= htmlspecialchars($ev['end_at']) ?></div>
        </td>
        <td><?= htmlspecialchars($ev['location'] ?? '-') ?></td>
        <td>
          <a href="<?=$base?>/admin/qr?event_id=<?=$ev['id']?>">QR</a> ·
          <a href="<?=$base?>/admin/events/edit?id=<?=$ev['id']?>">Edit</a> ·
          <a href="<?=$base?>/admin/events/export/csv?event_id=<?=$ev['id']?>">CSV</a> ·
          <a href="<?=$base?>/admin/events/export/xlsx?event_id=<?=$ev['id']?>">XLSX</a> ·
          <form class="inline" action="<?=$base?>/admin/events/delete" method="post" onsubmit="return confirm('Hapus event ini beserta data hadirnya?');">
            <input type="hidden" name="_csrf" value="<?=csrf_token()?>">
            <input type="hidden" name="id" value="<?=$ev['id']?>">
            <button type="submit">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
      <?php if(empty($events)): ?>
      <tr><td colspan="5" class="muted">Tidak ada data (coba ubah kata kunci).</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
