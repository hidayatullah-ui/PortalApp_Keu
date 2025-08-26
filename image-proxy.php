<?php
// image-proxy.php

// Ambil URL gambar dari parameter 'url'
$imageUrl = filter_input(INPUT_GET, 'url', FILTER_VALIDATE_URL);

// Pastikan URL valid untuk keamanan
if (!$imageUrl) {
    http_response_code(400);
    die('URL gambar tidak valid.');
}

// Ambil konten gambar dari URL
$imageContent = @file_get_contents($imageUrl);

// Periksa apakah pengambilan konten berhasil
if ($imageContent === false) {
    http_response_code(404);
    die('Gagal mengambil gambar.');
}

// Dapatkan tipe konten (misalnya, 'image/png')
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($imageContent);

// Atur header HTTP sesuai dengan tipe konten gambar
header('Content-Type: ' . $mimeType);

// Tampilkan konten gambar
echo $imageContent;
exit;
?>
