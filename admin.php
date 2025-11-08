<?php
/**
 * Waitlist Admin Panel
 * View and manage waitlist entries
 */

session_start();

// Simple authentication (enhance this for production)
$admin_password = 'changeme123'; // CHANGE THIS!

if (!isset($_SESSION['waitlist_admin'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        if ($_POST['password'] === $admin_password) {
            $_SESSION['waitlist_admin'] = true;
        } else {
            $error = 'Invalid password';
        }
    }

    if (!isset($_SESSION['waitlist_admin'])) {
        // Show login form
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Waitlist Admin</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
        </head>
        <body class="bg-light">
            <div class="container">
                <div class="row justify-content-center mt-5">
                    <div class="col-md-4">
                        <div class="card shadow">
                            <div class="card-body">
                                <h3 class="text-center mb-4">Waitlist Admin</h3>
                                <?php if (isset($error)): ?>
                                    <div class="alert alert-danger"><?php echo $error; ?></div>
                                <?php endif; ?>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control" name="password" required autofocus>
                                    </div>
                                    <button type="submit" class="btn btn-primary w-100">Login</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    unset($_SESSION['waitlist_admin']);
    header('Location: admin.php');
    exit;
}

// Connect to database
$config_file = '/app/config.php';
if (file_exists($config_file)) {
    require_once $config_file;
    require_once CONST_INCLUDES_DIR . '/ebiz-autoload.php';
    \eBizIndia\PDOConn::connectToDB('mysql');
    $db = \eBizIndia\PDOConn::getConnection();
    $table_prefix = CONST_TBL_PREFIX ?? '';
} else {
    // Standalone database connection
    $dsn = "mysql:host=localhost;dbname=your_database;charset=utf8mb4";
    $db = new PDO($dsn, 'your_username', 'your_password', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    $table_prefix = '';
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $id = (int)$_POST['id'];
    $status = $_POST['status'];
    $notes = $_POST['notes'] ?? '';

    $update_sql = "UPDATE `{$table_prefix}waitlist` SET status = :status, notes = :notes";

    if ($status === 'invited') {
        $update_sql .= ", invited_at = NOW()";
    } elseif ($status === 'converted') {
        $update_sql .= ", converted_at = NOW()";
    }

    $update_sql .= " WHERE id = :id";

    $stmt = $db->prepare($update_sql);
    $stmt->execute([
        ':status' => $status,
        ':notes' => $notes,
        ':id' => $id
    ]);

    $success = 'Status updated successfully';
}

// Get filter
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Build query
$sql = "SELECT * FROM `{$table_prefix}waitlist` WHERE 1=1";
$params = [];

if ($filter !== 'all') {
    $sql .= " AND status = :status";
    $params[':status'] = $filter;
}

if (!empty($search)) {
    $sql .= " AND (name LIKE :search OR email LIKE :search OR company LIKE :search)";
    $params[':search'] = "%$search%";
}

$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$entries = $stmt->fetchAll();

// Get statistics
$stats_sql = "SELECT
    COUNT(*) as total,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'invited' THEN 1 ELSE 0 END) as invited,
    SUM(CASE WHEN status = 'converted' THEN 1 ELSE 0 END) as converted,
    SUM(CASE WHEN status = 'declined' THEN 1 ELSE 0 END) as declined
FROM `{$table_prefix}waitlist`";

$stats = $db->query($stats_sql)->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waitlist Admin Dashboard</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: #0f3460;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .table-responsive {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .badge {
            padding: 0.5rem 1rem;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <span class="navbar-brand mb-0 h1">
                <i class="fas fa-users me-2"></i> Waitlist Admin Dashboard
            </span>
            <a href="?logout=1" class="btn btn-outline-light btn-sm">
                <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container-fluid mt-4">
        <?php if (isset($success)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number"><?php echo number_format($stats['total']); ?></div>
                    <div class="stat-label">Total Signups</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-primary"><?php echo number_format($stats['pending']); ?></div>
                    <div class="stat-label">Pending</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-info"><?php echo number_format($stats['invited']); ?></div>
                    <div class="stat-label">Invited</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-number text-success"><?php echo number_format($stats['converted']); ?></div>
                    <div class="stat-label">Converted</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row mb-3">
            <div class="col-md-8">
                <div class="btn-group" role="group">
                    <a href="?filter=all" class="btn btn-<?php echo $filter === 'all' ? 'primary' : 'outline-primary'; ?>">
                        All (<?php echo $stats['total']; ?>)
                    </a>
                    <a href="?filter=pending" class="btn btn-<?php echo $filter === 'pending' ? 'primary' : 'outline-primary'; ?>">
                        Pending (<?php echo $stats['pending']; ?>)
                    </a>
                    <a href="?filter=invited" class="btn btn-<?php echo $filter === 'invited' ? 'info' : 'outline-info'; ?>">
                        Invited (<?php echo $stats['invited']; ?>)
                    </a>
                    <a href="?filter=converted" class="btn btn-<?php echo $filter === 'converted' ? 'success' : 'outline-success'; ?>">
                        Converted (<?php echo $stats['converted']; ?>)
                    </a>
                </div>
            </div>
            <div class="col-md-4">
                <form method="GET" class="d-flex">
                    <input type="text" class="form-control me-2" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Export Button -->
        <div class="row mb-3">
            <div class="col-12">
                <a href="export-waitlist.php" class="btn btn-success">
                    <i class="fas fa-download me-1"></i> Export to CSV
                </a>
            </div>
        </div>

        <!-- Waitlist Table -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Company</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($entries)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3"></i>
                                <p>No entries found</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($entries as $index => $entry): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($entry['name']); ?></strong>
                                </td>
                                <td>
                                    <a href="mailto:<?php echo htmlspecialchars($entry['email']); ?>">
                                        <?php echo htmlspecialchars($entry['email']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($entry['company'] ?: '-'); ?></td>
                                <td><?php echo htmlspecialchars($entry['title'] ?: '-'); ?></td>
                                <td>
                                    <?php
                                    $badge_class = match($entry['status']) {
                                        'pending' => 'bg-primary',
                                        'invited' => 'bg-info',
                                        'converted' => 'bg-success',
                                        'declined' => 'bg-secondary',
                                        default => 'bg-secondary'
                                    };
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo ucfirst($entry['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('M j, Y', strtotime($entry['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo $entry['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>

                            <!-- Edit Modal -->
                            <div class="modal fade" id="editModal<?php echo $entry['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Entry</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <input type="hidden" name="id" value="<?php echo $entry['id']; ?>">

                                                <div class="mb-3">
                                                    <label class="form-label"><strong>Name:</strong></label>
                                                    <p><?php echo htmlspecialchars($entry['name']); ?></p>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label"><strong>Email:</strong></label>
                                                    <p><?php echo htmlspecialchars($entry['email']); ?></p>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select" required>
                                                        <option value="pending" <?php echo $entry['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                                        <option value="invited" <?php echo $entry['status'] === 'invited' ? 'selected' : ''; ?>>Invited</option>
                                                        <option value="converted" <?php echo $entry['status'] === 'converted' ? 'selected' : ''; ?>>Converted</option>
                                                        <option value="declined" <?php echo $entry['status'] === 'declined' ? 'selected' : ''; ?>>Declined</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Notes</label>
                                                    <textarea name="notes" class="form-control" rows="3"><?php echo htmlspecialchars($entry['notes'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" name="update_status" class="btn btn-primary">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
