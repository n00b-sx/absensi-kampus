<?php
namespace App\Services;

class Security {
  public static function inIpRanges(string $ip, array $cidrs): bool {
    if (!$cidrs) return true;
    foreach ($cidrs as $cidr) {
      [$subnet,$bits] = explode('/', $cidr);
      $ipDec = ip2long($ip);
      $subnetDec = ip2long($subnet);
      $mask = -1 << (32 - (int)$bits);
      $subnetDec &= $mask;
      if (($ipDec & $mask) === $subnetDec) return true;
    }
    return false;
  }

  public static function haversine(float $lat1, float $lon1, float $lat2, float $lon2): float {
    $R = 6371000; // m
    $dLat = deg2rad($lat2-$lat1);
    $dLon = deg2rad($lon2-$lon1);
    $a = sin($dLat/2)**2 + cos(deg2rad($lat1))*cos(deg2rad($lat2))*sin($dLon/2)**2;
    return 2*$R*asin(min(1,sqrt($a)));
  }
}
