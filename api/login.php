<?php
// File: api/login.php
// Menangani verifikasi login pengguna.

include_once __DIR__ . '/../db.php'; // Hubungkan ke database

// Ambil data yang dikirim dari JavaScript
$data = json_decode(file_get_contents("php://input"));

// Pastikan username dan password dikirim
if (empty($data->username) || empty($data->password)) {
    http_response_code(400);
    echo json_encode(["message" => "Username dan password dibutuhkan."]);
    exit();
}

// Cari pengguna berdasarkan username
$query = "SELECT * FROM users WHERE username = :username";
$stmt = $conn->prepare($query);
$stmt->bindParam(':username', $data->username);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verifikasi password yang dikirim (sudah di-encode base64 dari JS)
    // dengan password di database
    if ($data->password === $user['password']) {
        // Jika cocok, kirim kembali data pengguna (tanpa password)
        unset($user['password']); // Hapus password dari respons demi keamanan
        http_response_code(200);
        echo json_encode($user);
    } else {
        // Jika password salah
        http_response_code(401);
        echo json_encode(["message" => "Password salah."]);
    }
} else {
    // Jika username tidak ditemukan
    http_response_code(404);
    echo json_encode(["message" => "Username tidak ditemukan."]);
}
?>