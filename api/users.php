<?php
// File: api/users.php
// Mengelola semua permintaan untuk data pengguna (CRUD - Create, Read, Update, Delete)

// Menampilkan error untuk debugging (hapus di produksi)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Menggunakan path absolut untuk keandalan
include_once __DIR__ . '/../db.php'; // Hubungkan ke database

$method = $_SERVER['REQUEST_METHOD'];

// Ambil ID dari URL jika ada, contoh: /api/users.php?id=123
$id = isset($_GET['id']) ? $_GET['id'] : null;

switch ($method) {
    case 'GET':
        // Jika ada ID, ambil satu pengguna. Jika tidak, ambil semua.
        if ($id) {
            $stmt = $conn->prepare("SELECT id, fullName, username, email, role FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($user);
        } else {
            $stmt = $conn->prepare("SELECT id, fullName, username, email, role FROM users");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($users);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));

        // Validasi data sederhana
        if (empty($data->fullName) || empty($data->username) || empty($data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Data tidak lengkap."]);
            exit();
        }

        $query = "INSERT INTO users (id, fullName, username, email, password, role) VALUES (:id, :fullName, :username, :email, :password, :role)";
        $stmt = $conn->prepare($query);

        // Buat ID unik di sisi server
        $newId = "user-" . time() . "-" . rand(100, 999);

        // Bind parameter
        $stmt->bindParam(':id', $newId);
        $stmt->bindParam(':fullName', $data->fullName);
        $stmt->bindParam(':username', $data->username);
        $stmt->bindParam(':email', $data->email);
        $stmt->bindParam(':password', $data->password); // Password sudah di-encode (base64) dari frontend
        $stmt->bindParam(':role', $data->role);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode(["message" => "Pengguna berhasil dibuat.", "id" => $newId]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Gagal membuat pengguna."]);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        
        // Membutuhkan ID untuk update
        if (empty($data->id)) {
             http_response_code(400);
             echo json_encode(["message" => "ID pengguna dibutuhkan."]);
             exit();
        }

        $query = "UPDATE users SET fullName = :fullName, username = :username, email = :email, role = :role";
        // Hanya update password jika diisi
        if (!empty($data->password)) {
            $query .= ", password = :password";
        }
        $query .= " WHERE id = :id";

        $stmt = $conn->prepare($query);

        // Bind parameter
        $stmt->bindParam(':id', $data->id);
        $stmt->bindParam(':fullName', $data->fullName);
        $stmt->bindParam(':username', $data->username);
        $stmt->bindParam(':email', $data->email);
        $stmt->bindParam(':role', $data->role);
        if (!empty($data->password)) {
            $stmt->bindParam(':password', $data->password);
        }

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Pengguna berhasil diperbarui."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Gagal memperbarui pengguna."]);
        }
        break;

    case 'DELETE':
        // Membutuhkan ID untuk delete
        if (!$id) {
            http_response_code(400);
            echo json_encode(["message" => "ID pengguna dibutuhkan."]);
            exit();
        }

        $query = "DELETE FROM users WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            http_response_code(200);
            echo json_encode(["message" => "Pengguna berhasil dihapus."]);
        } else {
            http_response_code(503);
            echo json_encode(["message" => "Gagal menghapus pengguna."]);
        }
        break;
        
    default:
        http_response_code(405);
        echo json_encode(["message" => "Metode tidak diizinkan."]);
        break;
}
?>
