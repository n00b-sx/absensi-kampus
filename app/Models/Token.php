<?php
namespace App\Models;

class Token {
  public static function create(int $eventId, string $token, string $expiresAt): void {
    $pdo = \app_pdo();
    $st = $pdo->prepare("INSERT INTO event_tokens(event_id, token, expires_at) VALUES (?,?,?)");
    $st->execute([$eventId, $token, $expiresAt]);
  }

  public static function findValid(string $token): ?array {
    $pdo = \app_pdo();
    $st = $pdo->prepare("SELECT * FROM event_tokens WHERE token=? LIMIT 1");
    $st->execute([$token]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function markUsed(string $token, int $userId): void {
    $pdo = \app_pdo();
    $st = $pdo->prepare("UPDATE event_tokens SET used_by=?, used_at=? WHERE token=?");
    $st->execute([$userId, \now(), $token]);
  }
}
