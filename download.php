<?php
require_once __DIR__ . '/includes/functions.php';

// Pastikan user sudah login
if (!isLoggedIn()) {
    redirectWithMessage('login.php', 'Please login to download files', 'danger');
}

// Dapatkan ID dari parameter URL
$id = $_GET['id'] ?? null;

if (!$id || !is_numeric($id)) {
    redirectWithMessage('index.php', 'Invalid file ID', 'danger');
}

// Dapatkan data boot image dari database
$bootImage = getBootImageById($id);

if (!$bootImage) {
    redirectWithMessage('index.php', 'File not found in database', 'danger');
}

// Siapkan path file
$baseDir = __DIR__ . '/';
$filePath = $baseDir . $bootImage['image_path'];

// Cek alternatif path jika file tidak ditemukan
if (!file_exists($filePath)) {
    // Coba path alternatif (untuk kompatibilitas)
    $altPath = $baseDir . 'uploads/' . basename($bootImage['image_path']);
    if (file_exists($altPath)) {
        $filePath = $altPath;
    } else {
        redirectWithMessage('index.php', 'File not found on server: ' . basename($bootImage['image_path']), 'danger');
    }
}

// Validasi bahwa file memang ada dan bisa dibaca
if (!is_readable($filePath)) {
    redirectWithMessage('index.php', 'File is not readable', 'danger');
}

// Generate nama file download dengan prefix "BootImage_"
$cleanModelName = preg_replace('/[^a-zA-Z0-9-_]/', '-', $bootImage['model_name']);
$fileExtension = pathinfo($filePath, PATHINFO_EXTENSION);
$downloadFilename = 'Boot Image_' . $cleanModelName . '.' . $fileExtension;

// Set headers untuk download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $downloadFilename . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($filePath));

// Bersihkan output buffer dan kirim file
ob_clean();
flush();

// Baca file dan kirim ke output
$file = fopen($filePath, 'rb');
if ($file) {
    while (!feof($file)) {
        print fread($file, 1024 * 8);
        flush();
        if (connection_status() != 0) {
            fclose($file);
            exit;
        }
    }
    fclose($file);
}

exit;
?>