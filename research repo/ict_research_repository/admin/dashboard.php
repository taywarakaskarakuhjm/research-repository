<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Check if admin is logged in
requireLogin('admin');

// Get filter parameters
$statusFilter = sanitizeInput($_GET['status'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$itemsPerPage = 15;

// Fetch statistics
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM research"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM research WHERE status='Pending'"))['total'];
$approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM research WHERE status='Approved'"))['total'];
$revision = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM research WHERE status='For Revision'"))['total'];
$rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM research WHERE status='Rejected'"))['total'];

// Build filtered query
$whereClause = "1=1";
$params = [];
$types = '';

if (!empty($statusFilter)) {
    $whereClause .= " AND status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

// Count total filtered results
$countQuery = "SELECT COUNT(*) as total FROM research WHERE $whereClause";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalFiltered = $countRow['total'];
$totalPages = getTotalPages($totalFiltered, $itemsPerPage);

// Validate page
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}

// Get paginated results
$pagination = getPaginationParams($page, $itemsPerPage);
$query = "SELECT id, title, author, status, uploaded_at FROM research WHERE $whereClause ORDER BY uploaded_at DESC LIMIT " . $pagination['limit'] . " OFFSET " . $pagination['offset'];
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ICT Research Repository</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: #0D47A1;
            --secondary-color: #00897B;
            --light-gray: #F5F5F5;
            --dark-color: #212529;
        }

        body {
            background-color: var(--light-gray);
        }

        .navbar {
            background-color: var(--primary-color);
        }

        .content-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            margin-bottom: 30px;
            margin-top: 20px;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 32px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border-left: 4px solid #ccc;
            cursor: pointer;
        }

        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .stats-card.total { border-left-color: #2196F3; }
        .stats-card.pending { border-left-color: #FF9800; }
        .stats-card.approved { border-left-color: #4CAF50; }
        .stats-card.revision { border-left-color: #FF5722; }
        .stats-card.rejected { border-left-color: #F44336; }

        .stats-card .number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .stats-card.total .number { color: #2196F3; }
        .stats-card.pending .number { color: #FF9800; }
        .stats-card.approved .number { color: #4CAF50; }
        .stats-card.revision .number { color: #FF5722; }
        .stats-card.rejected .number { color: #F44336; }

        .stats-card .label {
            color: #666;
            font-size: 13px;
            font-weight: 500;
        }

        /* Filter Section */
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .filter-section h5 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 20px;
        }

        .filter-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }

        .filter-group select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
            min-width: 200px;
        }

        .filter-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 71, 161, 0.1);
        }

        .filter-btn, .filter-reset {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 13px;
        }

        .filter-btn:hover {
            background-color: #0A3B8F;
        }

        .filter-reset {
            background-color: #999;
        }

        .filter-reset:hover {
            background-color: #777;
        }

        /* Table Section */
        .table-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .table-section h5 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 25px;
        }

        .research-table {
            margin-bottom: 0;
        }

        .research-table thead th {
            background-color: #f5f5f5;
            color: var(--dark-color);
            font-weight: 700;
            border-bottom: 2px solid #e0e0e0;
            padding: 15px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .research-table tbody td {
            padding: 15px;
            border-bottom: 1px solid #e0e0e0;
            vertical-align: middle;
            font-size: 13px;
        }

        .research-table tbody tr:hover {
            background-color: #fafafa;
        }

        .research-table .title {
            color: var(--primary-color);
            font-weight: 600;
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .research-table .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
        }

        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-badge.approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.revision {
            background-color: #ffe0b2;
            color: #e65100;
        }

        .status-badge.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .review-btn {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            font-weight: 600;
            text-decoration: none;
            font-size: 12px;
            transition: all 0.3s;
            display: inline-block;
        }

        .review-btn:hover {
            background-color: #006B5B;
            color: white;
        }

        /* Pagination */
        .pagination-section {
            display: flex;
            justify-content: center;
            margin-top: 30px;
        }

        .pagination {
            gap: 8px;
        }

        .page-link {
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            color: var(--primary-color);
            padding: 8px 12px;
            font-size: 13px;
        }

        .page-link:hover {
            background-color: var(--primary-color);
            color: white;
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .filter-group {
                flex-direction: column;
            }

            .filter-group select,
            .filter-btn {
                width: 100%;
            }

            .research-table {
                font-size: 12px;
            }

            .research-table thead th,
            .research-table tbody td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="bi bi-book"></i> ICT Repository - Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="bi bi-speedometer2"></i> Admin Dashboard</h1>
            <p class="text-muted mb-0">Manage and review submitted research papers</p>
        </div>

        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stats-card total">
                <div class="number"><?php echo $total; ?></div>
                <div class="label"><i class="bi bi-file-text"></i> Total Research</div>
            </div>
            <div class="stats-card pending">
                <div class="number"><?php echo $pending; ?></div>
                <div class="label"><i class="bi bi-clock"></i> Pending Review</div>
            </div>
            <div class="stats-card approved">
                <div class="number"><?php echo $approved; ?></div>
                <div class="label"><i class="bi bi-check-circle"></i> Approved</div>
            </div>
            <div class="stats-card revision">
                <div class="number"><?php echo $revision; ?></div>
                <div class="label"><i class="bi bi-pencil"></i> For Revision</div>
            </div>
            <div class="stats-card rejected">
                <div class="number"><?php echo $rejected; ?></div>
                <div class="label"><i class="bi bi-x-circle"></i> Rejected</div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5>Filter by Status</h5>
            <form method="GET" action="">
                <div class="filter-group">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="Approved" <?php echo $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                        <option value="For Revision" <?php echo $statusFilter === 'For Revision' ? 'selected' : ''; ?>>For Revision</option>
                        <option value="Rejected" <?php echo $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                    <button type="submit" class="filter-btn"><i class="bi bi-funnel"></i> Apply</button>
                    <a href="dashboard.php" class="filter-reset">Reset</a>
                </div>
            </form>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <h5><i class="bi bi-list-ul"></i> Research Papers</h5>
            
            <table class="table research-table">
                <thead>
                    <tr>
                        <th width="40%">Title</th>
                        <th width="20%">Author</th>
                        <th width="15%">Status</th>
                        <th width="15%">Date</th>
                        <th width="10%">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows === 0) {
                        echo '<tr><td colspan="5" class="text-center text-muted py-4">No research papers found</td></tr>';
                    } else {
                        while ($row = $result->fetch_assoc()) {
                            $statusClass = strtolower(str_replace(' ', '-', $row['status']));
                            $dateUpload = date('M d, Y', strtotime($row['uploaded_at'] ?? 'now'));
                            
                            echo '<tr>';
                            echo '<td><span class="title">' . sanitizeOutput($row['title']) . '</span></td>';
                            echo '<td>' . sanitizeOutput($row['author']) . '</td>';
                            echo '<td><span class="status-badge ' . htmlspecialchars($statusClass) . '">' . htmlspecialchars($row['status']) . '</span></td>';
                            echo '<td>' . $dateUpload . '</td>';
                            echo '<td><a href="review.php?id=' . intval($row['id']) . '" class="review-btn"><i class="bi bi-eye"></i> Review</a></td>';
                            echo '</tr>';
                        }
                    }
                    ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination-section">
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php
                            // Previous button
                            if ($page > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?status=' . urlencode($statusFilter) . '&page=' . ($page - 1) . '">Previous</a></li>';
                            } else {
                                echo '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
                            }
                            
                            // Page numbers
                            $startPage = max(1, $page - 2);
                            $endPage = min($totalPages, $page + 2);
                            
                            if ($startPage > 1) {
                                echo '<li class="page-item"><a class="page-link" href="?status=' . urlencode($statusFilter) . '&page=1">1</a></li>';
                                if ($startPage > 2) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                            }
                            
                            for ($i = $startPage; $i <= $endPage; $i++) {
                                if ($i === $page) {
                                    echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                                } else {
                                    echo '<li class="page-item"><a class="page-link" href="?status=' . urlencode($statusFilter) . '&page=' . $i . '">' . $i . '</a></li>';
                                }
                            }
                            
                            if ($endPage < $totalPages) {
                                if ($endPage < $totalPages - 1) {
                                    echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                }
                                echo '<li class="page-item"><a class="page-link" href="?status=' . urlencode($statusFilter) . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
                            }
                            
                            // Next button
                            if ($page < $totalPages) {
                                echo '<li class="page-item"><a class="page-link" href="?status=' . urlencode($statusFilter) . '&page=' . ($page + 1) . '">Next</a></li>';
                            } else {
                                echo '<li class="page-item disabled"><span class="page-link">Next</span></li>';
                            }
                            ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
