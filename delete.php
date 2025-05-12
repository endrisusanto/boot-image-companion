<?php
require_once __DIR__ . '/includes/functions.php';

$id = $_GET['id'] ?? null;

if ($id) {
    $bootImage = getBootImageById($id);
    
    if ($bootImage) {
        // Delete image file
        $imagePath = __DIR__ . '/uploads/' . $bootImage['image_path'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        
        // Delete from database
        deleteBootImage($id);
    }
}

header('Location: index.php?status=deleted');
exit;
?>