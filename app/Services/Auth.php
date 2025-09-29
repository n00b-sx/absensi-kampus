<?php
namespace App\Services;

use App\Models\User;

class Auth {
  public static function attempt(string $email, string $password): bool {
    $user = User::findByEmail($email);
    if (!$user) return false;
    if (!$user['password']) return false;
    if (!password_verify($password, $user['password'])) return false;

    // simpan session minimal
    $_SESSION['auth'] = [
      'id' => (int)$user['id'],
      'name' => $user['name'],
      'email' => $user['email'],
      'role' => $user['role'],
    ];
    return true;
  }

  public static function check(): bool {
    return !empty($_SESSION['auth']);
  }

  public static function user(): ?array {
    return $_SESSION['auth'] ?? null;
  }

  public static function isAdmin(): bool {
    return self::check() && (($_SESSION['auth']['role'] ?? '') === 'admin');
  }

  public static function logout(): void {
    unset($_SESSION['auth']);
  }

  public static function requireAdmin(): void {
    if (!self::isAdmin()) {
      \flash('errors', ['Silakan login sebagai admin.']);
      header('Location: '.\app_config('base_url').'/login'); exit;
    }
  }
}
