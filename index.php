<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

// Cek login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Handle search dan pagination
$search = $_GET['search'] ?? '';
$page = max(1, $_GET['page'] ?? 1);
$perPage = 5;

$bootImages = getAllBootImages($search, $page, $perPage);
$totalItems = countBootImages($search);
$totalPages = ceil($totalItems / $perPage);

// Notifikasi
if (isset($_GET['status'])) {
    $status = $_GET['status'];
    if ($status === 'created') {
        showAlert('Boot image created successfully!');
    } elseif ($status === 'updated') {
        showAlert('Boot image updated successfully!');
    } elseif ($status === 'deleted') {
        showAlert('Boot image deleted successfully!', 'danger');
    }
}
?>

<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mb-0">Boot Images</h2>
            <a href="create.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Add New
            </a>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-8">
                <input type="text" name="search" class="form-control" placeholder="Search by model name..." 
                       value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Search
                </button>
            </div>
            <div class="col-md-2">
                <a href="index.php" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Model Name</th>
                <th>Image</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bootImages)): ?>
                <tr>
                    <td colspan="5" class="text-center py-4">No boot images found</td>
                </tr>
            <?php else: ?>
                <?php foreach ($bootImages as $image): ?>
                    <tr>
                        <td><?php echo $image['id']; ?></td>
                        <td><?php echo htmlspecialchars($image['model_name']); ?></td>
                        <td>
                            <?php 
                            $imagePath = $image['image_path'];
                            $fullPath = __DIR__ . '/' . $imagePath;
                            if (file_exists($fullPath)): 
                            ?>
                                <img src="<?php echo $imagePath; ?>" 
                                     alt="<?php echo htmlspecialchars($image['model_name']); ?>" 
                                     class="img-thumbnail" 
                                     style="max-height: 60px; max-width: 60px; object-fit: contain;">
                            <?php else: ?>
                                <div class="text-danger small">
                                    <i class="bi bi-exclamation-triangle"></i> Image not found
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('M d, Y H:i', strtotime($image['created_at'])); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="edit.php?id=<?php echo $image['id']; ?>" class="btn btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="delete.php?id=<?php echo $image['id']; ?>" 
                                   class="btn btn-outline-danger" 
                                   onclick="return confirm('Are you sure you want to delete this?')">
                                    <i class="bi bi-trash"></i> Delete
                                </a>
                                <a href="download.php?id=<?php echo $image['id']; ?>" class="btn btn-outline-success">
                                    <i class="bi bi-download"></i> Download
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                    <i class="bi bi-chevron-left"></i> Previous
                </a>
            </li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                    Next <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</nav>

<?php require_once __DIR__ . '/includes/footer.php'; ?>