<?php $base = rtrim(app_config('base_url',''),'/'); ?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login Admin</title>
  <link rel="stylesheet" href="<?=$base?>/assets/css/app.css">
  <style>
    body{font-family:system-ui, sans-serif; display:grid; place-items:center; min-height:100vh}
    .card{width:360px; padding:18px; border:1px solid #ddd; border-radius:12px; background:#fff}
    label{display:block; margin:8px 0 4px; font-weight:600}
    input{width:100%; padding:10px; font-size:16px}
    button{padding:10px 14px; font-size:16px; width:100%; margin-top:12px}
    .msg{margin:8px 0; padding:8px; border-radius:8px}
    .err{background:#fde8e8; color:#9b1c1c}
    .ok{background:#e6f4ea; color:#0a7a2f}
  </style>
</head>
<body>
  <div class="card">
    <h2>Login Admin</h2>

    <?php if(!empty($errors)): ?><div class="msg err"><?php foreach($errors as $e){echo htmlspecialchars($e).'<br>';}?></div><?php endif;?>
    <?php if(!empty($success)): ?><div class="msg ok"><?=htmlspecialchars($success)?></div><?php endif;?>

    <form action="<?=$base?>/login" method="post">
      <label>Email</label>
      <input type="email" name="email" required autofocus>
      <label>Password</label>
      <input type="password" name="password" required>
      <button type="submit">Masuk</button>
    </form>
  </div>
</body>
</html>
