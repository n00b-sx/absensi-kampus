<?php
namespace App\Controllers;

use App\Models\Event;
use App\Models\Token as TokenModel;
use App\Models\Attendance;
use App\Services\TokenService;

class ApiController {
  public function issueToken() {
    $eventId = (int)($_GET['event_id'] ?? 0);
    if (!$eventId || !Event::exists($eventId)) {
      return \json_response(['message'=>'Invalid event_id'], 422);
    }
    $token = TokenService::mint($eventId);
    return \json_response([
      'token' => $token['token'],
      'expires_at' => $token['expires_at'],
      'refresh_in' => (int)\app_config('token_refresh_seconds', 20),
    ]);
  }

  public function checkin() {
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
    $token  = trim($input['token'] ?? '');
    $ident  = trim($input['identity_number'] ?? '');
    $lat    = isset($input['lat']) ? floatval($input['lat']) : null;
    $lng    = isset($input['lng']) ? floatval($input['lng']) : null;

    if ($token==='' || $ident==='') return \json_response(['message'=>'token & identity_number required'], 422);

    $validation = TokenService::validate($token, $lat, $lng);
    if (!$validation['ok']) return \json_response(['message'=>$validation['reason']], 422);

    $eventId = $validation['event_id'];
    $userId  = Attendance::resolveUserIdByIdentity($ident);
    if (!$userId) {
      // ---- AUTO-REGISTER (minimal) ----
      $name = trim($input['name'] ?? '');
      if ($name === '') {
        // Kalau klien belum kirim nama, beri pesan khusus agar UI meminta nama
        return \json_response(['message'=>'Peserta tidak ditemukan','need_register'=>true], 404);
      }
      $userId = \App\Models\User::createParticipant([
        'name' => $name,
        'identity_number' => $ident,
        'identity_type' => $input['identity_type'] ?? 'NIM',
        'category' => $input['category'] ?? 'mahasiswa',
        'study_program_id' => isset($input['study_program_id']) ? (int)$input['study_program_id'] : null,
      ]);
    }


    $saved = Attendance::mark($eventId, $userId, [
      'method'=>'qr',
      'ip'=>\client_ip(),
      'ua'=>\user_agent(),
      'lat'=>$lat, 'lng'=>$lng
    ]);

    if ($saved['ok']) {
      TokenModel::markUsed($token, $userId);
      return \json_response(['message'=>'Hadir tercatat','at'=>$saved['checkin_at']]);
    }
    return \json_response(['message'=>$saved['reason'] ?? 'Gagal mencatat hadir'], 422);
  }
}
