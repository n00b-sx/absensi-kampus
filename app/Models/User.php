<?php
namespace App\Models;

class User {
  public static function findByEmail(string $email): ?array {
    $pdo = \app_pdo();
    $st = $pdo->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
    $st->execute([$email]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function findById(int $id): ?array {
    $pdo = \app_pdo();
    $st = $pdo->prepare("SELECT * FROM users WHERE id=? LIMIT 1");
    $st->execute([$id]);
    $row = $st->fetch();
    return $row ?: null;
  }

  public static function createAdmin(string $name, string $email, string $password): int {
    $pdo = \app_pdo();
    $st = $pdo->prepare("INSERT INTO users(name,email,password,role,identity_type,identity_number)
                         VALUES (?,?,?,?,?,?)");
    $st->execute([
      $name, $email, password_hash($password, PASSWORD_DEFAULT),
      'admin', 'NIM', 'ADM-'.bin2hex(random_bytes(3))
    ]);
    return (int)$pdo->lastInsertId();
  }

  public static function createParticipant(array $data): int {
  $pdo = \app_pdo();
  $st = $pdo->prepare("INSERT INTO users(name,email,password,role,category,identity_type,identity_number,study_program_id)
                       VALUES (?,?,?,?,?,?,?,?)");
  $st->execute([
    $data['name'],
    null,                   // email opsional
    null,                   // password null untuk peserta
    'peserta',
    $data['category'] ?? 'mahasiswa',
    $data['identity_type'] ?? 'NIM',
    $data['identity_number'],
    $data['study_program_id'] ?? null
  ]);
  return (int)$pdo->lastInsertId();
}

}
