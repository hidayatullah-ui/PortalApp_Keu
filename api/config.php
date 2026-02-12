<?php
// api/config.php
// Basic DB + CORS bootstrap for OpenLiteSpeed/CyberPanel
// Set your DB creds here or via environment variables in CyberPanel

$DB_HOST = 'localhost'; // Tambahkan atau pastikan variabel ini ada
$DB_NAME = getenv('DB_NAME') ?: 'sult_portal_db';
$DB_USER = getenv('DB_USER') ?: 'sult_portal_user';
$DB_PASS = getenv('DB_PASS') ?: '#Kopi9976';
$ALLOW_ORIGIN = getenv('ALLOW_ORIGIN') ?: 'https://sulthan.id';

// CORS (only if you set ALLOW_ORIGIN). If API and front-end are on same origin, leave blank.
if (!headers_sent() && !empty($ALLOW_ORIGIN)) {
  header('Access-Control-Allow-Origin: ' . $ALLOW_ORIGIN);
  header('Access-Control-Allow-Credentials: true');
  header('Access-Control-Allow-Headers: Content-Type');
  header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
  http_response_code(204);
  exit;
}

try {
  $pdo = new PDO(
    "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4",
    $DB_USER,
    $DB_PASS,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
  );
} catch (PDOException $e) {
  http_response_code(500);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['error' => 'DB connection failed', 'detail' => $e->getMessage()]);
  exit;
}

// helper
function json_input() {
  $raw = file_get_contents('php://input');
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
