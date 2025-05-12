<?php
require_once __DIR__ . '/../config/database.php';

// Fungsi manajemen session
function startSessionIfNotStarted() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Fungsi autentikasi
function isLoggedIn() {
    startSessionIfNotStarted();
    return isset($_SESSION['user_id']);
}

function loginUser($username, $password) {
    global $pdo;
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password'])) {
        startSessionIfNotStarted();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        return true;
    }
    
    return false;
}
function registerUser($username, $email, $password) {
    global $pdo;
    
    try {
        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            return [
                'status' => false,
                'message' => 'All fields are required'
            ];
        }

        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            return [
                'status' => false,
                'message' => 'Username or email already exists'
            ];
        }

        // Hash the password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$username, $email, $hashedPassword]);

        return [
            'status' => true,
            'message' => 'Registration successful! Redirecting to login...'
        ];
    } catch (PDOException $e) {
        return [
            'status' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}
function logout() {
    startSessionIfNotStarted();
    session_unset();
    session_destroy();
}

function getCurrentUser() {
    if (isLoggedIn()) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    return null;
}

// Fungsi boot images
function getAllBootImages($search = '', $page = 1, $perPage = 5) {
    global $pdo;
    
    $offset = ($page - 1) * $perPage;
    $sql = "SELECT * FROM boot_images";
    
    if (!empty($search)) {
        $sql .= " WHERE model_name LIKE :search";
    }
    
    $sql .= " ORDER BY created_at DESC LIMIT :offset, :perPage";
    
    $stmt = $pdo->prepare($sql);
    
    if (!empty($search)) {
        $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    }
    
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getBootImageById($id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM boot_images WHERE id = ?");
    $stmt->execute([$id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Normalisasi path gambar
    if ($image && strpos($image['image_path'], 'uploads/') !== 0) {
        $image['image_path'] = 'uploads/' . $image['image_path'];
    }
    
    return $image;
}

function createBootImage($modelName, $imageFile) {
    global $pdo;
    
    // Buat direktori upload jika belum ada
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate nama file yang aman
    $fileExtension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
    $cleanModelName = preg_replace('/[^a-zA-Z0-9-]/', '-', strtolower($modelName));
    $fileName = $cleanModelName . '-' . uniqid() . '.' . $fileExtension;
    $relativePath = 'uploads/' . $fileName;
    $destination = $uploadDir . $fileName;
    
    // Pindahkan file upload
    if (move_uploaded_file($imageFile['tmp_name'], $destination)) {
        $stmt = $pdo->prepare("INSERT INTO boot_images (model_name, image_path) VALUES (?, ?)");
        return $stmt->execute([$modelName, $relativePath]);
    }
    
    return false;
}

function updateBootImage($id, $modelName, $imageFile = null) {
    global $pdo;
    
    if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        $fileExtension = pathinfo($imageFile['name'], PATHINFO_EXTENSION);
        $cleanModelName = preg_replace('/[^a-zA-Z0-9-]/', '-', strtolower($modelName));
        $fileName = $cleanModelName . '-' . uniqid() . '.' . $fileExtension;
        $relativePath = 'uploads/' . $fileName;
        $destination = $uploadDir . $fileName;
        
        // Dapatkan data gambar lama
        $oldImage = getBootImageById($id);
        
        if (move_uploaded_file($imageFile['tmp_name'], $destination)) {
            // Hapus gambar lama jika ada
            if ($oldImage && file_exists(__DIR__ . '/../' . $oldImage['image_path'])) {
                unlink(__DIR__ . '/../' . $oldImage['image_path']);
            }
            
            $stmt = $pdo->prepare("UPDATE boot_images SET model_name = ?, image_path = ? WHERE id = ?");
            return $stmt->execute([$modelName, $relativePath, $id]);
        }
        return false;
    } else {
        $stmt = $pdo->prepare("UPDATE boot_images SET model_name = ? WHERE id = ?");
        return $stmt->execute([$modelName, $id]);
    }
}

function deleteBootImage($id) {
    global $pdo;
    
    // Dapatkan data gambar sebelum dihapus
    $image = getBootImageById($id);
    
    if ($image) {
        // Hapus file gambar
        $filePath = __DIR__ . '/../' . $image['image_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }
        
        // Hapus dari database
        $stmt = $pdo->prepare("DELETE FROM boot_images WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    return false;
}

// Fungsi statistik
function countBootImages($search = '') {
    global $pdo;
    
    $sql = "SELECT COUNT(*) FROM boot_images";
    if (!empty($search)) {
        $sql .= " WHERE model_name LIKE ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(["%$search%"]);
    } else {
        $stmt = $pdo->query($sql);
    }
    
    return $stmt->fetchColumn();
}

function countModels() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(DISTINCT model_name) FROM boot_images");
    return $stmt->fetchColumn();
}

function countUsers() {
    global $pdo;
    $stmt = $pdo->query("SELECT COUNT(*) FROM users");
    return $stmt->fetchColumn();
}

function getModelStats() {
    global $pdo;
    $stmt = $pdo->query("SELECT model_name, COUNT(*) as count FROM boot_images GROUP BY model_name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fungsi utilitas
function showAlert($message, $type = 'success') {
    echo '<div class="alert alert-'.$type.' alert-dismissible fade show" role="alert">
            '.htmlspecialchars($message).'
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>';
}

function redirectWithMessage($url, $message, $type = 'success') {
    startSessionIfNotStarted();
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
    header("Location: $url");
    exit;
}

function displayFlashMessage() {
    startSessionIfNotStarted();
    if (isset($_SESSION['flash_message'])) {
        showAlert($_SESSION['flash_message'], $_SESSION['flash_type']);
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}
?>