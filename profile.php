<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// Pastikan user sudah login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = getCurrentUser();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validasi input
    if (empty($username) || empty($email)) {
        $error = 'Username and email are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } else {
        // Verifikasi password saat ini jika ingin mengubah password
        if (!empty($new_password) && !password_verify($current_password, $user['password'])) {
            $error = 'Current password is incorrect';
        } else {
            try {
                global $pdo;
                
                // Jika ada password baru, update password
                if (!empty($new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $hashed_password, $user['id']]);
                    $message = 'Profile and password updated successfully!';
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                    $stmt->execute([$username, $email, $user['id']]);
                    $message = 'Profile updated successfully!';
                }
                
                // Update session username jika berubah
                $_SESSION['username'] = $username;
                $user = getCurrentUser(); // Refresh data user
                
            } catch (PDOException $e) {
                $error = 'Failed to update profile: ' . $e->getMessage();
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h4>User Profile</h4>
            </div>
            <div class="card-body">
                <?php if ($message): ?>
                    <div class="alert alert-success"><?php echo $message; ?></div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password (only required if changing password)</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label">New Password (leave blank to keep current)</label>
                            <input type="password" class="form-control" id="new_password" name="new_password">
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Account Created</label>
                        <input type="text" class="form-control" 
                               value="<?php echo date('Y-m-d H:i:s', strtotime($user['created_at'])); ?>" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Last Updated</label>
                        <input type="text" class="form-control" 
                               value="<?php echo date('Y-m-d H:i:s', strtotime($user['updated_at'])); ?>" readonly>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Profile</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>