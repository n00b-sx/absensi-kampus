<?php
namespace App\Models;

class Event {
  public static function exists(int $id): bool {
    $pdo = \app_pdo();
    $st = $pdo->prepare("SELECT 1 FROM events WHERE id=? LIMIT 1");
    $st->execute([$id]);
    return (bool)$st->fetchColumn();
  }

  public static function find(int $id): ?array {
    $pdo = \app_pdo();
    $st = $pdo->prepare("SELECT * FROM events WHERE id=?");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function create(array $data): array {
  $pdo = \app_pdo();
  $st = $pdo->prepare("INSERT INTO events(code,title,description,location,latitude,longitude,start_at,end_at,created_by)
                       VALUES (?,?,?,?,?,?,?,?,?)");
  $code = \ulid_like();
  $st->execute([
    $code,
    $data['title'],
    $data['description'] ?? null,
    $data['location'] ?? null,
    $data['latitude'] !== '' ? (float)$data['latitude'] : null,
    $data['longitude'] !== '' ? (float)$data['longitude'] : null,
    $data['start_at'],
    $data['end_at'],
    (int)$data['created_by'],
  ]);
  return ['ok'=>true,'id'=>(int)$pdo->lastInsertId(),'code'=>$code];
}

}
