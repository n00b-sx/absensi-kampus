<?php
// app/Support/functions.php

// ------- Konfigurasi & PDO getter (diisi oleh Bootstrap) -------
function app_config(string $key, $default = null) {
  global $config;
  return $config[$key] ?? $default;
}
function app_pdo(): PDO {
  global $pdo;
  return $pdo;
}

// -------------------- Helper umum ------------------------------
function json_response($data, int $code = 200): void {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data);
  exit;
}
function now(): string {
  return (new DateTime('now'))->format('Y-m-d H:i:s');
}
function client_ip(): string {
  return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}
function user_agent(): string {
  return $_SERVER['HTTP_USER_AGENT'] ?? '-';
}
function ulid_like(): string {
  // ULID-like 26 chars (bukan ULID murni, cukup unik untuk kode event)
  return substr(strtoupper(base_convert(time(), 10, 36) . bin2hex(random_bytes(8))), 0, 26);
}
function uuidv4(): string {
  $data = random_bytes(16);
  $data[6] = chr((ord($data[6]) & 0x0f) | 0x40); // version 4
  $data[8] = chr((ord($data[8]) & 0x3f) | 0x80); // variant
  return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

// -------------------- CSRF & Flash & Old -----------------------
function csrf_token(): string {
  if (empty($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(16));
  }
  return $_SESSION['_csrf'];
}
function csrf_validate(?string $t): bool {
  return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], (string)$t);
}
function flash(string $key, $val = null) {
  if ($val === null) {
    if (!isset($_SESSION['_flash'][$key])) return null;
    $v = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $v;
  } else {
    $_SESSION['_flash'][$key] = $val;
  }
}
function persist_old(array $src): void { $_SESSION['_old'] = $src; }
function clear_old(): void { unset($_SESSION['_old']); }
function old(string $key, $default = '') {
  $v = $_SESSION['_old'][$key] ?? $default;
  return is_string($v) ? htmlspecialchars($v, ENT_QUOTES, 'UTF-8') : $v;
}
