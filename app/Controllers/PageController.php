<?php
namespace App\Controllers;

use App\Services\Auth;
use App\Models\Event;
use App\Models\Attendance;

class PageController
{
  // ====== Halaman umum ======
  public function home() {
    echo "<h1>Absensi Kampus</h1><p><a href='./admin/qr?event_id=1'>Tampilkan QR</a> | <a href='./scan'>Scan</a> | <a href='./admin/events'>Admin</a></p>";
  }

  public function adminQr() {
    require __DIR__ . '/../../public/admin/qr.php';
  }

  public function scanPage() {
    require __DIR__ . '/../../public/scan.php';
  }

  // ====== Tambah Event (yang dicari routermu) ======
  public function eventCreate() {
    $base = rtrim(app_config('base_url',''),'/');
    $errors = flash('errors') ?? [];
    $success = flash('success');
    require __DIR__ . '/../../public/admin/event_create.php';
  }

  public function eventStore() {
    if (!csrf_validate($_POST['_csrf'] ?? '')) {
      flash('errors', ['CSRF tidak valid. Muat ulang halaman.']);
      persist_old($_POST);
      header('Location: '.app_config('base_url').'/admin/events/create'); exit;
    }

    $title    = trim($_POST['title'] ?? '');
    $start_at = trim($_POST['start_at'] ?? '');
    $end_at   = trim($_POST['end_at'] ?? '');
    $errors   = [];

    if ($title==='') $errors[] = 'Judul wajib diisi.';
    if ($start_at==='' || $end_at==='') $errors[] = 'Waktu mulai & selesai wajib diisi.';
    if ($start_at!=='' && $end_at!=='' && (new \DateTime($end_at) <= new \DateTime($start_at))) {
      $errors[] = 'Waktu selesai harus setelah waktu mulai.';
    }
    if ($errors) {
      flash('errors', $errors);
      persist_old($_POST);
      header('Location: '.app_config('base_url').'/admin/events/create'); exit;
    }

    $payload = [
      'title'       => $title,
      'description' => trim($_POST['description'] ?? ''),
      'location'    => trim($_POST['location'] ?? ''),
      'latitude'    => trim($_POST['latitude'] ?? ''),
      'longitude'   => trim($_POST['longitude'] ?? ''),
      'start_at'    => $start_at,
      'end_at'      => $end_at,
      // TODO: pakai id admin login; sementara 1
      'created_by'  => (Auth::user()['id'] ?? 1),
    ];

    $res = \App\Models\Event::create($payload);
    clear_old();
    flash('success', "Event berhasil dibuat (ID #{$res['id']}, Kode {$res['code']}).");
    header('Location: '.app_config('base_url').'/admin/events/create'); exit;
  }

  // ====== Auth (opsional jika sudah kamu pasang) ======
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

  // ====== Admin - Events (opsional jika sudah dipasang) ======
  public function eventsIndex() {
  \App\Services\Auth::requireAdmin();
  $base = rtrim(app_config('base_url',''),'/');
  $pdo = app_pdo();

  $q = trim($_GET['q'] ?? '');
  if ($q !== '') {
    $sql = "SELECT id, code, title, start_at, end_at, location
            FROM events
            WHERE title LIKE ?
               OR code LIKE ?
               OR location LIKE ?
               OR DATE(start_at) = ?
               OR DATE(end_at) = ?
            ORDER BY id DESC";
    $like = "%{$q}%";
    $st = $pdo->prepare($sql);
    $st->execute([$like, $like, $like, $q, $q]);
  } else {
    $st = $pdo->query("SELECT id, code, title, start_at, end_at, location FROM events ORDER BY id DESC");
  }

  $events = $st->fetchAll();
  $me = \App\Services\Auth::user();
  require __DIR__ . '/../../public/admin/events_index.php';
}


public function exportCsv() {
  \App\Services\Auth::requireAdmin();
  $eventId = (int)($_GET['event_id'] ?? 0);
  if (!$eventId || !\App\Models\Event::exists($eventId)) {
    http_response_code(404); echo "Event not found"; exit;
  }
  $rows = Attendance::listByEvent($eventId);

  $filename = "event-{$eventId}-attendances-" . date('Ymd_His') . ".csv";
  header('Content-Type: text/csv; charset=utf-8');
  header('Content-Disposition: attachment; filename="'.$filename.'"');
  $out = fopen('php://output', 'w');

  // Header kolom
  fputcsv($out, ['No','Nama','Identity Type','Identity Number','Category','Study Program','Check-in At','Method','IP','User Agent']);

  $i=1;
  foreach($rows as $r){
    fputcsv($out, [
      $i++,
      $r['user_name'],
      $r['identity_type'],
      $r['identity_number'],
      $r['category'],
      $r['study_program'],
      $r['checkin_at'],
      $r['method'],
      $r['ip_address'],
      $r['user_agent'],
    ]);
  }
  fclose($out);
  exit;
}

public function exportXlsx() {
  \App\Services\Auth::requireAdmin();
  $eventId = (int)($_GET['event_id'] ?? 0);
  if (!$eventId || !\App\Models\Event::exists($eventId)) {
    http_response_code(404); echo "Event not found"; exit;
  }
  $rows = Attendance::listByEvent($eventId);

  // PhpSpreadsheet
  $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
  $sheet = $spreadsheet->getActiveSheet();
  $sheet->setTitle('Attendances');

  // Header
  $headers = ['No','Nama','Identity Type','Identity Number','Category','Study Program','Check-in At','Method','IP','User Agent'];
  $col = 'A';
  foreach ($headers as $h) { $sheet->setCellValue($col.'1', $h); $col++; }

  // Data
  $r = 2; $i=1;
  foreach ($rows as $row) {
    $sheet->setCellValue('A'.$r, $i++);
    $sheet->setCellValue('B'.$r, $row['user_name']);
    $sheet->setCellValue('C'.$r, $row['identity_type']);
    $sheet->setCellValue('D'.$r, $row['identity_number']);
    $sheet->setCellValue('E'.$r, $row['category']);
    $sheet->setCellValue('F'.$r, $row['study_program']);
    $sheet->setCellValue('G'.$r, $row['checkin_at']);
    $sheet->setCellValue('H'.$r, $row['method']);
    $sheet->setCellValue('I'.$r, $row['ip_address']);
    $sheet->setCellValue('J'.$r, $row['user_agent']);
    $r++;
  }

  // Auto-size kolom
  foreach (range('A','J') as $c) { $sheet->getColumnDimension($c)->setAutoSize(true); }

  // Output
  $filename = "event-{$eventId}-attendances-" . date('Ymd_His') . ".xlsx";
  header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
  header('Content-Disposition', 'attachment; filename="'.$filename.'"');
  header('Cache-Control', 'max-age=0');

  $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
  $writer->save('php://output');
  exit;
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
