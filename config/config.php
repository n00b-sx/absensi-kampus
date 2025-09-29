<?php
return [
  'app_name' => 'Absensi Kampus',
  'base_url' => '/absensi-kampus/public', // sesuaikan dengan VirtualHost/alias
  'token_ttl_seconds' => 45,              // masa berlaku token QR
  'token_refresh_seconds' => 20,          // interval refresh QR di client
  'allow_ip_ranges' => [],                // contoh ['10.10.0.0/16'] jika mau batasi IP
  'geofence_radius_m' => 0,               // set >0 untuk aktifkan batas jarak (meter)
];
