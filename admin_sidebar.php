<?php
// Get the current page name
$current_page = basename($_SERVER['PHP_SELF']);

// Count pending volunteer requests
try {
    $pending_count_stmt = $pdo->prepare("SELECT COUNT(*) FROM volunteers WHERE status = 'pending'");
    $pending_count_stmt->execute();
    $pending_count = $pending_count_stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Error counting pending volunteers: " . $e->getMessage());
    $pending_count = 0;
}
?>

<nav class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <h3 class="text-white">CHARITEX</h3>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'admin_dashboard.php') ? 'active' : ''; ?>" 
                   href="admin_dashboard.php">
                    <i class="fas fa-home"></i>
                    <span class="ms-2">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'volunteer_requests.php') ? 'active' : ''; ?>" 
                   href="volunteer_requests.php">
                    <i class="fas fa-user-clock"></i>
                    <span class="ms-2">Volunteer Requests</span>
                    <?php if($pending_count > 0): ?>
                        <span class="badge rounded-pill bg-danger ms-2"><?php echo $pending_count; ?></span>
                    <?php endif; ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'manage_volunteers.php') ? 'active' : ''; ?>" 
                   href="manage_volunteers.php">
                    <i class="fas fa-users"></i>
                    <span class="ms-2">Manage Volunteers</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'manage_events.php') ? 'active' : ''; ?>" 
                   href="manage_events.php">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="ms-2">Manage Events</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'donations.php') ? 'active' : ''; ?>" 
                   href="donations.php">
                    <i class="fas fa-hand-holding-heart"></i>
                    <span class="ms-2">Donations</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo ($current_page === 'settings.php') ? 'active' : ''; ?>" 
                   href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span class="ms-2">Settings</span>
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span class="ms-2">Logout</span>
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.sidebar {
    min-height: 100vh;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.nav-link {
    color: rgba(255,255,255,.8);
    padding: 0.5rem 1rem;
    margin: 0.2rem 0;
    border-radius: 0.25rem;
    transition: all 0.3s ease;
}

.nav-link:hover {
    color: #fff;
    background-color: rgba(255,255,255,0.1);
}

.nav-link.active {
    color: #fff;
    background-color: rgba(255,255,255,0.2);
}

.badge {
    font-size: 0.75em;
}

.sidebar .fas {
    width: 20px;
    text-align: center;
}

@media (max-width: 768px) {
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        z-index: 100;
        padding: 0;
    }
}
</style>