<?php
namespace App\Services;

use App\Models\Event;
use App\Models\Token as TokenModel;

class TokenService {
  public static function mint(int $eventId): array {
    $ttl = (int)\app_config('token_ttl_seconds', 45);
    $token = \uuidv4();
    $expiresAt = (new \DateTime())->add(new \DateInterval('PT'.$ttl.'S'))->format('Y-m-d H:i:s');
    TokenModel::create($eventId, $token, $expiresAt);
    return ['token'=>$token, 'expires_at'=>$expiresAt];
  }

  public static function validate(string $token, ?float $lat, ?float $lng): array {
    $row = TokenModel::findValid($token);
    if (!$row) return ['ok'=>false,'reason'=>'Token tidak dikenal'];
    if ($row['used_by']) return ['ok'=>false,'reason'=>'Token sudah terpakai'];
    if (new \DateTime($row['expires_at']) < new \DateTime()) return ['ok'=>false,'reason'=>'Token kadaluarsa'];

    // Optional: cek IP range kampus
    if (!Security::inIpRanges(\client_ip(), \app_config('allow_ip_ranges',[]))) {
      return ['ok'=>false,'reason'=>'Akses jaringan tidak diizinkan'];
    }

    // Optional: geofencing
    $radius = (int)\app_config('geofence_radius_m', 0);
    if ($radius > 0) {
      $event = Event::find((int)$row['event_id']);
      if (!$event || $event['latitude']===null || $event['longitude']===null) {
        return ['ok'=>false,'reason'=>'Geofence belum dikonfigurasi'];
      }
      if ($lat===null || $lng===null) {
        return ['ok'=>false,'reason'=>'Lokasi diperlukan'];
      }
      $dist = Security::haversine((float)$event['latitude'], (float)$event['longitude'], $lat, $lng);
      if ($dist > $radius) return ['ok'=>false,'reason'=>"Di luar radius ($radius m)"];
    }

    return ['ok'=>true,'event_id'=>(int)$row['event_id']];
  }
}
