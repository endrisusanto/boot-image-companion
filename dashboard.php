<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

$totalImages = countBootImages();
$totalModels = countModels();
$totalUsers = countUsers();
$modelStats = getModelStats();
?>

<h2 class="mb-4">Dashboard</h2>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Total Boot Images</h5>
                <p class="card-text display-4"><?php echo $totalImages; ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Total Models</h5>
                <p class="card-text display-4"><?php echo $totalModels; ?></p>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Total Users</h5>
                <p class="card-text display-4"><?php echo $totalUsers; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- <div class="card mb-4">
    <div class="card-header">
        <h5>Model Distribution</h5>
    </div>
    <div class="card-body">
        <canvas id="modelChart" height="200"></canvas>
    </div>
</div> -->

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('modelChart').getContext('2d');
        const modelStats = <?php echo json_encode($modelStats); ?>;
        
        const labels = modelStats.map(item => item.model_name);
        const data = modelStats.map(item => item.count);
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Number of Images',
                    data: data,
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(153, 102, 255, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)',
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(153, 102, 255, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>