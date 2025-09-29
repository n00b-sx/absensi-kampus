<?php
function json_response($data, int $code=200) {
  http_response_code($code);
  header('Content-Type: application/json');
  echo json_encode($data);
  exit;
}
function now(): string { return (new DateTime('now'))->format('Y-m-d H:i:s'); }
function client_ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }
function user_agent(): string { return $_SERVER['HTTP_USER_AGENT'] ?? '-'; }
function ulid_like(): string {
  // cukup placeholder 26 chars: timestamp base36 + random
  return substr(strtoupper(base_convert(time(),10,36).bin2hex(random_bytes(8))),0,26);
}
function uuidv4(): string {
  $data = random_bytes(16);
  $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
  $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
  return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
