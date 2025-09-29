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
}
