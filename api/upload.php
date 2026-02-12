<?php
// api/upload.php
// Handle "bukti" file uploads used by the Transactions form.

require __DIR__ . '/config.php';
header('Content-Type: application/json; charset=utf-8');

$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
  mkdir($uploadDir, 0755, true);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  http_response_code(405);
  echo json_encode(['error' => 'method not allowed']);
  exit;
}

if (!isset($_FILES['file'])) {
  http_response_code(400);
  echo json_encode(['error' => 'file field is required']);
  exit;
}

$f = $_FILES['file'];
if ($f['error'] !== UPLOAD_ERR_OK) {
  http_response_code(400);
  echo json_encode(['error' => 'upload error', 'code' => $f['error']]);
  exit;
}

$ext = pathinfo($f['name'], PATHINFO_EXTENSION);
try {
  $basename = bin2hex(random_bytes(8)) . ($ext ? ('.' . $ext) : '');
} catch (Exception $e) {
  $basename = time() . '_' . mt_rand(1000,9999) . ($ext ? ('.' . $ext) : '');
}

$target = $uploadDir . '/' . $basename;
if (!move_uploaded_file($f['tmp_name'], $target)) {
  http_response_code(500);
  echo json_encode(['error' => 'failed to save file']);
  exit;
}

// Public URL assuming this folder lives at https://yourdomain.com/api/uploads/
echo json_encode([
  'ok' => true,
  'filename' => $basename,
  'url' => '/api/uploads/' . $basename
]);
