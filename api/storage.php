<?php
// api/storage.php
// Simple key-value JSON store backed by MySQL to replace localStorage.

require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $key = isset($_GET['key']) ? $_GET['key'] : '';
  if ($key === '') { http_response_code(400); header('Content-Type: application/json'); echo json_encode(['error' => 'key required']); exit; }

  $stmt = $pdo->prepare("SELECT value FROM kv_store WHERE `key` = ? LIMIT 1");
  $stmt->execute([$key]);
  $row = $stmt->fetch();
  header('Content-Type: application/json; charset=utf-8');
  if ($row) {
    // Value is already JSON text; return as-is
    echo $row['value'];
  } else {
    echo json_encode(null);
  }
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $in = json_input();
  $key = isset($in['key']) ? $in['key'] : '';
  $value = isset($in['value']) ? $in['value'] : null;

  if ($key === '') { http_response_code(400); header('Content-Type: application/json'); echo json_encode(['error' => 'key required']); exit; }

  // Ensure we store proper JSON string
  $json = json_encode($value, JSON_UNESCAPED_UNICODE);

  $stmt = $pdo->prepare("INSERT INTO kv_store (`key`, `value`, `updated_at`) VALUES (?, ?, NOW())
                         ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = NOW()");
  $stmt->execute([$key, $json]);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['ok' => true]);
  exit;
}

http_response_code(405);
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['error' => 'method not allowed']);
