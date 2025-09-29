<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Models\Event;

class PageController {
  // ... method sebelumnya (home, adminQr, scanPage, eventCreate, eventStore) tetap ...

  // ---------- Auth ----------
  public function loginPage() {
    $base = rtrim(app_config('base_url',''),'/');
    $errors = flash('errors') ?? [];
    $success = flash('success');
    require __DIR__ . '/../../public/auth/login.php';
  }

  public function loginPost() {
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    if ($email==='' || $password==='') {
      flash('errors', ['Email dan password wajib diisi.']);
      header('Location: '.app_config('base_url').'/login'); exit;
    }

    if (!Auth::attempt($email, $password)) {
      flash('errors', ['Kredensial tidak valid.']);
      header('Location: '.app_config('base_url').'/login'); exit;
    }

    flash('success','Login berhasil.');
    header('Location: '.app_config('base_url').'/admin/events'); exit;
  }

  public function logout() {
    Auth::logout();
    flash('success','Anda telah logout.');
    header('Location: '.app_config('base_url').'/login'); exit;
  }

  // ---------- Admin - Events (protected) ----------
  public function eventsIndex() {
    Auth::requireAdmin();
    $base = rtrim(app_config('base_url',''),'/');
    // Ambil list event
    $pdo = app_pdo();
    $st = $pdo->query("SELECT id, code, title, start_at, end_at, location FROM events ORDER BY id DESC");
    $events = $st->fetchAll();
    $me = Auth::user();
    require __DIR__ . '/../../public/admin/events_index.php';
  }

  public function eventEdit() {
    Auth::requireAdmin();
    $base = rtrim(app_config('base_url',''),'/');
    $id = (int)($_GET['id'] ?? 0);
    $event = $id ? Event::find($id) : null;
    if (!$event) {
      flash('errors', ['Event tidak ditemukan.']);
      header('Location: '.app_config('base_url').'/admin/events'); exit;
    }
    $errors = flash('errors') ?? [];
    $success = flash('success');
    require __DIR__ . '/../../public/admin/event_edit.php';
  }

  public function eventUpdate() {
    Auth::requireAdmin();
    if (!csrf_validate($_POST['_csrf'] ?? '')) {
      flash('errors', ['CSRF tidak valid.']); header('Location: '.app_config('base_url').'/admin/events'); exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $start_at = trim($_POST['start_at'] ?? '');
    $end_at   = trim($_POST['end_at'] ?? '');
    $errors = [];
    if(!$id) $errors[] = 'ID event invalid.';
    if($title==='') $errors[] = 'Judul wajib diisi.';
    if ($start_at==='' || $end_at==='') $errors[] = 'Waktu mulai & selesai wajib diisi.';
    if ($start_at!=='' && $end_at!=='' && (new \DateTime($end_at) <= new \DateTime($start_at))) {
      $errors[] = 'Waktu selesai harus setelah waktu mulai.';
    }
    if ($errors) {
      flash('errors',$errors);
      header('Location: '.app_config('base_url')."/admin/events/edit?id={$id}"); exit;
    }

    $pdo = app_pdo();
    $st = $pdo->prepare("UPDATE events SET title=?, description=?, location=?, latitude=?, longitude=?, start_at=?, end_at=? WHERE id=?");
    $st->execute([
      $title,
      trim($_POST['description'] ?? ''),
      trim($_POST['location'] ?? ''),
      ($_POST['latitude'] !== '') ? (float)$_POST['latitude'] : null,
      ($_POST['longitude'] !== '') ? (float)$_POST['longitude'] : null,
      $start_at, $end_at, $id
    ]);

    flash('success','Event berhasil diperbarui.');
    header('Location: '.app_config('base_url')."/admin/events/edit?id={$id}"); exit;
  }

  public function eventDelete() {
    Auth::requireAdmin();
    if (!csrf_validate($_POST['_csrf'] ?? '')) {
      flash('errors', ['CSRF tidak valid.']); header('Location: '.app_config('base_url').'/admin/events'); exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    if (!$id) { flash('errors',['ID event invalid.']); header('Location: '.app_config('base_url').'/admin/events'); exit; }

    // Hapus berurutan: attendances -> tokens -> events
    $pdo = app_pdo();
    $pdo->beginTransaction();
    try {
      $pdo->prepare("DELETE FROM attendances WHERE event_id=?")->execute([$id]);
      $pdo->prepare("DELETE FROM event_tokens WHERE event_id=?")->execute([$id]);
      $pdo->prepare("DELETE FROM events WHERE id=?")->execute([$id]);
      $pdo->commit();
      flash('success','Event dihapus.');
    } catch (\Throwable $e) {
      $pdo->rollBack();
      flash('errors',['Gagal menghapus: '.$e->getMessage()]);
    }
    header('Location: '.app_config('base_url').'/admin/events'); exit;
  }
}
