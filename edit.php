<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// Cek login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: index.php');
    exit;
}

$bootImage = getBootImageById($id);

if (!$bootImage) {
    header('Location: index.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelName = $_POST['model_name'] ?? '';
    $imageFile = $_FILES['boot_image'] ?? null;
    
    if (!empty($modelName)) {
        if ($imageFile && $imageFile['error'] === UPLOAD_ERR_OK) {
            if (updateBootImage($id, $modelName, $imageFile)) {
                header('Location: index.php?status=updated');
                exit;
            } else {
                showAlert('Failed to update boot image with new file!', 'danger');
            }
        } else {
            if (updateBootImage($id, $modelName)) {
                header('Location: index.php?status=updated');
                exit;
            } else {
                showAlert('Failed to update boot image data!', 'danger');
            }
        }
    } else {
        showAlert('Please fill all required fields correctly!', 'danger');
    }
}

// Script untuk preview gambar
?>
<script>
function previewImage(event) {
    const reader = new FileReader();
    reader.onload = function() {
        const preview = document.getElementById('imagePreview');
        preview.src = reader.result;
        preview.style.display = 'block';
    };
    reader.readAsDataURL(event.target.files[0]);
}
</script>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h2 class="mb-0">Edit Boot Image</h2>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
                <label for="model_name" class="form-label">Model Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="model_name" name="model_name" 
                       value="<?php echo htmlspecialchars($bootImage['model_name']); ?>" required>
            </div>
            
            <div class="col-md-6">
                <label for="boot_image" class="form-label">New Boot Image (Leave blank to keep current)</label>
                <input type="file" class="form-control" id="boot_image" name="boot_image" 
                       accept="image/*" onchange="previewImage(event)">
            </div>
            
            <div class="col-12">
                <div class="mb-3">
                    <label class="form-label">Image Preview</label>
                    <div class="d-flex gap-4 align-items-center">
                        <div class="text-center">
                            <p class="mb-2"><strong>Current Image</strong></p>
                            <?php if (file_exists(__DIR__ . '/' . $bootImage['image_path'])): ?>
                                <img src="<?php echo $bootImage['image_path']; ?>" 
                                     alt="Current Image" 
                                     style="max-height: 200px; max-width: 100%;" 
                                     class="img-thumbnail">
                                <div class="mt-2 small text-muted">
                                    <?php echo basename($bootImage['image_path']); ?>
                                </div>
                            <?php else: ?>
                                <div class="text-danger py-3">
                                    <i class="bi bi-exclamation-triangle-fill"></i> Current image not found
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-center">
                            <p class="mb-2"><strong>New Image Preview</strong></p>
                            <img id="imagePreview" src="#" alt="New Image Preview" 
                                 style="max-height: 200px; max-width: 100%; display: none;" 
                                 class="img-thumbnail">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-12">
                <div class="d-flex justify-content-between">
                    <div>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to List
                        </a>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save"></i> Update Image
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>