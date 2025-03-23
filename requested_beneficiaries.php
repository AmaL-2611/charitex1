<?php
session_start();
require_once 'connect.php';

// Database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

// Check if orphanage is logged in
if (!isset($_SESSION['orphanage_id'])) {
    header('location:login_orphanage.php');
    exit();
}

// Fetch orphanage details
$stmt = $pdo->prepare("SELECT name FROM orphanage WHERE id = ?");
$stmt->execute([$_SESSION['orphanage_id']]);
$orphanage = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requested Beneficiaries - CHARITEX</title>
    
    <!-- Modern Fonts -->
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

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
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

        .status-badge i {
            font-size: 0.7rem;
        }

        .status-Pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-Approved {
            background-color: #d1e7dd;
            color: #0f5132;
        }

        .status-Rejected {
            background-color: #f8d7da;
            color: #842029;
        }

        .beneficiary-card {
            transition: transform 0.2s;
        }

        .beneficiary-card:hover {
            transform: translateY(-5px);
        }

        .profile-img {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            object-fit: cover;
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

        .empty-state {
            text-align: center;
            padding: 3rem;
        }

        .empty-state i {
            font-size: 4rem;
            color: #dee2e6;
            margin-bottom: 1rem;
        }

        .back-button {
            margin-top: -1rem;
            padding-top: 1rem;
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

        .back-btn i {
            font-size: 0.8rem;
        }

        .back-btn:hover {
            transform: translateX(-3px);
            background-color: #3498db;
            color: white;
            border-color: #3498db;
        }

        .beneficiary-card h5 {
            font-size: 1rem;
            margin-right: 10px;
        }

        .beneficiary-details {
            margin-top: 1rem;
            font-size: 0.9rem;
        }

        .beneficiary-details p {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0.5rem;
        }

        .beneficiary-details i {
            width: 16px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Dashboard Header -->
    <div class="dashboard-header">
        <div class="container">
            <h2><i class="fas fa-user-clock"></i> Requested Beneficiaries</h2>
            <p class="mb-0">Track all your beneficiary requests and their status</p>
        </div>
    </div>

    <div class="container">
        <div class="back-button mb-4">
            <a href="orphanage_dashboard.php" class="btn btn-outline-primary back-btn">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Filter Buttons -->
        <div class="text-center mb-4">
            <div class="filter-buttons">
                <button class="filter-btn active" data-status="all">
                    <i class="fas fa-list"></i> All
                </button>
                <button class="filter-btn" data-status="Pending">
                    <i class="fas fa-clock"></i> Pending
                </button>
                <button class="filter-btn" data-status="Approved">
                    <i class="fas fa-check"></i> Approved
                </button>
                <button class="filter-btn" data-status="Rejected">
                    <i class="fas fa-times"></i> Rejected
                </button>
            </div>
        </div>

        <!-- Beneficiaries Grid -->
        <div class="row" id="beneficiariesGrid">
            <?php
            // Fetch all beneficiaries for this orphanage
            $stmt = $pdo->prepare("
                SELECT * FROM beneficiaries 
                WHERE orphanage_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$_SESSION['orphanage_id']]);
            $beneficiaries = $stmt->fetchAll();

            if (empty($beneficiaries)): ?>
                <div class="col-12 empty-state">
                    <i class="fas fa-user-plus"></i>
                    <h4>No Beneficiaries Found</h4>
                    <p>Start by adding a new beneficiary request</p>
                    <a href="register_beneficiary.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Beneficiary
                    </a>
                </div>
            <?php else:
                foreach ($beneficiaries as $beneficiary): ?>
                    <div class="col-md-6 col-lg-4 beneficiary-item" data-status="<?= htmlspecialchars($beneficiary['status']) ?>">
                        <div class="card beneficiary-card">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="d-flex align-items-center">
                                        <img src="<?= htmlspecialchars($beneficiary['profile_image']) ?>" 
                                             alt="Profile" 
                                             class="profile-img me-3">
                                        <h5 class="mb-0"><?= htmlspecialchars($beneficiary['full_name']) ?></h5>
                                    </div>
                                    <span class="status-badge status-<?= htmlspecialchars($beneficiary['status']) ?>">
                                        <i class="fas fa-<?= 
                                            $beneficiary['status'] === 'Approved' ? 'check' : 
                                            ($beneficiary['status'] === 'Rejected' ? 'times' : 'clock')
                                        ?>"></i>
                                        <?= htmlspecialchars($beneficiary['status']) ?>
                                    </span>
                                </div>
                                
                                <div class="beneficiary-details">
                                    <p class="mb-2">
                                        <i class="fas fa-<?= $beneficiary['support_type'] === 'Education' ? 'graduation-cap' : 'hospital' ?>"></i>
                                        <?= htmlspecialchars($beneficiary['support_type']) ?> Support
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-money-bill"></i>
                                        ₹<?= number_format($beneficiary['required_amount'], 2) ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-calendar"></i>
                                        Requested: <?= date('d M Y', strtotime($beneficiary['created_at'])) ?>
                                    </p>
                                    <?php if (isset($beneficiary['remarks']) && !empty($beneficiary['remarks'])): ?>
                                        <p class="mb-0 text-muted">
                                            <i class="fas fa-comment"></i>
                                            <?= htmlspecialchars($beneficiary['remarks']) ?>
                                        </p>
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
        // Filter functionality
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', () => {
                // Update active button
                document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                button.classList.add('active');

                // Filter items
                const status = button.dataset.status;
                document.querySelectorAll('.beneficiary-item').forEach(item => {
                    if (status === 'all' || item.dataset.status === status) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html> 