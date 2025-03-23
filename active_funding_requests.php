<?php
session_start();
require_once 'connect.php';

// Check if orphanage is logged in
if (!isset($_SESSION['orphanage_id'])) {
    header('location:login_orphanage.php');
    exit();
}

// Database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Active Funding Requests - CHARITEX</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
        }

        .dashboard-header {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.3em 0.8em;
            border-radius: 15px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .status-approved {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
        }

        .status-badge i {
            font-size: 0.7rem;
        }

        .status-approved i { color: #28a745; }
        .status-rejected i { color: #dc3545; }
        .status-pending i { color: #ffc107; }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .back-btn {
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            transform: translateX(-3px);
        }

        .filter-buttons {
            display: inline-flex;
            gap: 10px;
            background: white;
            padding: 8px;
            border-radius: 50px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .filter-btn {
            font-size: 0.9rem;
            padding: 0.4rem 1rem;
            border: none;
            border-radius: 25px;
            background: #f8f9fa;
            color: #6c757d;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .filter-btn i {
            font-size: 0.8rem;
        }

        .filter-btn:hover {
            background: #e9ecef;
            transform: translateY(-1px);
        }

        .filter-btn.active {
            background: #3498db;
            color: white;
        }

        .funding-details {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            margin: 10px 0;
        }

        .amount-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .text-primary {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .description-box {
            background: #fff;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }

        .request-info {
            border-top: 1px solid #e9ecef;
            padding-top: 10px;
        }

        .request-info small {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .request-info i {
            width: 16px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <h2><i class="fas fa-money-bill-wave"></i> Active Funding Requests</h2>
            <p class="mb-0">Track your funding request status</p>
        </div>
    </div>

    <div class="container">
        <div class="mb-4">
            <a href="orphanage_dashboard.php" class="btn btn-outline-primary back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="text-center mb-4">
            <div class="filter-buttons">
                <button class="filter-btn active" data-status="all">
                    <i class="fas fa-list"></i> All
                </button>
                <button class="filter-btn" data-status="pending">
                    <i class="fas fa-clock"></i> Pending
                </button>
                <button class="filter-btn" data-status="approved">
                    <i class="fas fa-check"></i> Approved
                </button>
                <button class="filter-btn" data-status="rejected">
                    <i class="fas fa-times"></i> Rejected
                </button>
            </div>
        </div>

        <div class="row">
            <?php
            // Update the query to fetch all details including image
            $stmt = $pdo->prepare("
                SELECT * FROM funding_requests 
                WHERE orphanage_id = ?
                ORDER BY created_at DESC
            ");
            $stmt->execute([$_SESSION['orphanage_id']]);
            $requests = $stmt->fetchAll();

            if (empty($requests)): ?>
                <div class="col-12">
                    <div class="card">
                        <div class="card-body text-center">
                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                            <h4>No Funding Requests Found</h4>
                            <p>You haven't created any funding requests yet.</p>
                            <a href="create_funding_request.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Create Funding Request
                            </a>
                        </div>
                    </div>
                </div>
            <?php else:
                foreach ($requests as $request): ?>
                    <div class="col-md-6 col-lg-4 funding-item" data-status="<?= strtolower(htmlspecialchars($request['status'])) ?>">
                        <div class="card">
                            <?php if (!empty($request['image_path'])): ?>
                                <img src="<?= htmlspecialchars($request['image_path']) ?>" 
                                     class="card-img-top" 
                                     alt="Funding Request"
                                     style="height: 200px; object-fit: cover;">
                            <?php else: ?>
                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" 
                                     style="height: 200px;">
                                    <i class="fas fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title mb-0"><?= htmlspecialchars($request['title']) ?></h5>
                                    <span class="status-badge status-<?= strtolower(htmlspecialchars($request['status'])) ?>">
                                        <i class="fas fa-<?= 
                                            strtolower($request['status']) === 'approved' ? 'check' : 
                                            (strtolower($request['status']) === 'rejected' ? 'times' : 'clock')
                                        ?>"></i>
                                        <?= htmlspecialchars($request['status']) ?>
                                    </span>
                                </div>

                                <div class="funding-details mb-3">
                                    <div class="amount-info">
                                        <strong>Goal Amount:</strong>
                                        <span class="text-primary">₹<?= number_format($request['goal_amount'], 2) ?></span>
                                    </div>
                                </div>

                                <div class="description-box mb-3">
                                    <p class="card-text"><?= htmlspecialchars($request['description']) ?></p>
                                </div>

                                <div class="request-info">
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i>
                                            Created: <?= date('d M Y', strtotime($request['created_at'])) ?>
                                        </small>
                                    </div>
                                    
                                    <?php if (!empty($request['end_date'])): ?>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <i class="fas fa-clock"></i>
                                            End Date: <?= date('d M Y', strtotime($request['end_date'])) ?>
                                        </small>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach;
            endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Update active button
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                // Filter items
                const status = button.dataset.status;
                document.querySelectorAll('.funding-item').forEach(item => {
                    if (status === 'all' || item.dataset.status === status) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html> 