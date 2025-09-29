<?php
namespace App\Models;

class Attendance {
  public static function resolveUserIdByIdentity(string $identityNumber): ?int {
    $pdo = \app_pdo();
    $st = $pdo->prepare("SELECT id FROM users WHERE identity_number=? LIMIT 1");
    $st->execute([$identityNumber]);
    $id = $st->fetchColumn();
    return $id ? (int)$id : null;
  }

  public static function mark(int $eventId, int $userId, array $meta): array {
    $pdo = \app_pdo();
    // Cegah duplikat (UNIQUE event_id,user_id)
    try {
      $st = $pdo->prepare("INSERT INTO attendances(event_id,user_id,checkin_at,method,ip_address,user_agent,latitude,longitude)
                           VALUES (?,?,?,?,?,?,?,?)");
      $st->execute([
        $eventId, $userId, \now(),
        $meta['method'] ?? 'qr',
        $meta['ip'] ?? null,
        $meta['ua'] ?? null,
        $meta['lat'] ?? null,
        $meta['lng'] ?? null,
      ]);
      return ['ok'=>true,'checkin_at'=>\now()];
    } catch (\PDOException $e) {
      if ($e->getCode()==='23000') {
        return ['ok'=>false,'reason'=>'Peserta sudah tercatat hadir'];
      }
      return ['ok'=>false,'reason'=>$e->getMessage()];
    }
  }

  public static function listByEvent(int $eventId): array {
    $pdo = \app_pdo();
    $sql = "SELECT a.id, a.checkin_at, a.method, a.ip_address, a.user_agent,
                  u.name AS user_name, u.identity_type, u.identity_number, u.category,
                  sp.name AS study_program
            FROM attendances a
            JOIN users u ON u.id = a.user_id
            LEFT JOIN study_programs sp ON sp.id = u.study_program_id
            WHERE a.event_id = ?
            ORDER BY a.checkin_at ASC, a.id ASC";
    $st = $pdo->prepare($sql);
    $st->execute([$eventId]);
    return $st->fetchAll();
  }
}
