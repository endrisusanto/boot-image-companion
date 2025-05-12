<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// Cek login
if (!isLoggedIn()) {
    redirectWithMessage('login.php', 'Please login first', 'danger');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelName = trim($_POST['model_name'] ?? '');
    $imageFile = $_FILES['boot_image'] ?? null;
    
    // Validasi input
    if (empty($modelName)) {
        $error = 'Model name is required';
    } elseif (!$imageFile || $imageFile['error'] !== UPLOAD_ERR_OK) {
        $error = 'Please select a valid image file';
    } else {
        // Cek tipe file
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $fileType = mime_content_type($imageFile['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $error = 'Only JPG, PNG, and GIF images are allowed';
        } else {
            // Coba buat boot image
            if (createBootImage($modelName, $imageFile)) {
                redirectWithMessage('index.php', 'Boot image created successfully', 'success');
            } else {
                $error = 'Failed to create boot image. Please try again.';
            }
        }
    }
}
?>

<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const preview = document.getElementById('imagePreview');
        preview.src = reader.result;
        preview.style.display = 'block';
        document.getElementById('previewText').style.display = 'none';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h2 class="mb-0">Add New Boot Image</h2>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
                <label for="model_name" class="form-label">Model Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="model_name" name="model_name" 
                       value="<?php echo isset($_POST['model_name']) ? htmlspecialchars($_POST['model_name']) : ''; ?>" 
                       required>
            </div>
            
            <div class="col-md-6">
                <label for="boot_image" class="form-label">Boot Image <span class="text-danger">*</span></label>
                <input type="file" class="form-control" id="boot_image" name="boot_image" 
                       accept="image/jpeg,image/png,image/gif" required onchange="previewImage(event)">
                <div class="form-text">Allowed formats: JPG, PNG, GIF (Max 2MB)</div>
            </div>
            
            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label">Image Preview</label>
                    <div class="border rounded p-3 text-center" style="min-height: 200px; background-color: #f8f9fa;">
                        <div id="previewText" class="text-muted">Preview will appear here</div>
                        <img id="imagePreview" src="#" alt="Image Preview" 
                             style="max-height: 200px; max-width: 100%; display: none;" 
                             class="img-thumbnail">
                    </div>
                </div>
            </div>
            
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to List
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Image
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>