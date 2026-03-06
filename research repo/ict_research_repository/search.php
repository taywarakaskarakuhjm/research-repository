<?php
session_start();
include 'config/db.php';
include 'includes/functions.php';

// Get search parameters
$keyword = sanitizeInput($_GET['keyword'] ?? '');
$category = sanitizeInput($_GET['category'] ?? '');
$sortBy = sanitizeInput($_GET['sort'] ?? 'recent');
$page = max(1, intval($_GET['page'] ?? 1));
$itemsPerPage = 10;

// Build query
$whereClause = "WHERE status='Approved'";
$params = [];
$types = '';

if (!empty($keyword)) {
    $whereClause .= " AND (title LIKE ? OR author LIKE ?)";
    $likeKeyword = "%$keyword%";
    $params[] = $likeKeyword;
    $params[] = $likeKeyword;
    $types .= 'ss';
}

if (!empty($category)) {
    $whereClause .= " AND category = ?";
    $params[] = $category;
    $types .= 's';
}

// Add sorting
$orderClause = "ORDER BY uploaded_at DESC";
if ($sortBy === 'title') {
    $orderClause = "ORDER BY title ASC";
} elseif ($sortBy === 'author') {
    $orderClause = "ORDER BY author ASC";
}

// Count total results
$countQuery = "SELECT COUNT(*) as total FROM research $whereClause";
$countStmt = $conn->prepare($countQuery);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$countRow = $countResult->fetch_assoc();
$totalResults = $countRow['total'];
$totalPages = getTotalPages($totalResults, $itemsPerPage);

// Validate page number
if ($page > $totalPages && $totalPages > 0) {
    $page = $totalPages;
}

// Get paginated results
$pagination = getPaginationParams($page, $itemsPerPage);
$query = "SELECT * FROM research $whereClause $orderClause LIMIT " . $pagination['limit'] . " OFFSET " . $pagination['offset'];
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
    <title>Search Research - ICT Research Repository</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style.css">
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
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .page-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            margin-top: 20px;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 20px;
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
            font-size: 16px;
        }

        .filter-group {
            margin-bottom: 20px;
        }

        .filter-group label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            font-size: 13px;
        }

        .filter-group input,
        .filter-group select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 13px;
        }

        .filter-group input:focus,
        .filter-group select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 71, 161, 0.1);
        }

        .filter-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.3s;
            margin-top: 25px;
        }

        .filter-btn:hover {
            background-color: #0A3B8F;
        }

        /* Results Section */
        .results-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .results-count {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }

        .sort-dropdown {
            min-width: 180px;
        }

        /* Research Card */
        .research-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }

        .research-card:hover {
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            transform: translateY(-2px);
            border-color: var(--primary-color);
        }

        .research-card h3 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 12px;
            font-size: 20px;
        }

        .research-card .meta {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            font-size: 13px;
            color: #666;
        }

        .research-card .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .research-card .meta-item i {
            color: var(--primary-color);
        }

        .research-card .abstract {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .research-card .badges {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
        }

        .badge {
            background-color: #f0f0f0;
            color: var(--dark-color);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-category {
            background-color: #e3f2fd;
            color: var(--primary-color);
        }

        .research-card .btn-group {
            display: flex;
            gap: 10px;
        }

        .btn-view {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-view:hover {
            background-color: #006B5B;
            color: white;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .empty-state i {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #999;
            font-size: 20px;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #aaa;
            margin-bottom: 20px;
        }

        /* Pagination */
        .pagination-section {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .pagination {
            gap: 8px;
        }

        .page-link {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            color: var(--primary-color);
            padding: 8px 12px;
            font-size: 13px;
            transition: all 0.3s;
        }

        .page-link:hover {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .page-item.active .page-link {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        @media (max-width: 768px) {
            .results-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .sort-dropdown {
                width: 100%;
            }

            .research-card h3 {
                font-size: 18px;
            }

            .research-card .meta {
                flex-direction: column;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-book"></i> ICT Repository
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                    <?php if (isset($_SESSION['student'])): ?>
                        <li class="nav-item"><a class="nav-link" href="student/dashboard.php">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="student/logout.php">Logout</a></li>
                    <?php elseif (isset($_SESSION['admin'])): ?>
                        <li class="nav-item"><a class="nav-link" href="admin/dashboard.php">Admin</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="student/login.php">Student</a></li>
                        <li class="nav-item"><a class="nav-link" href="admin/login.php">Admin</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="content-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="bi bi-search"></i> Search Research</h1>
            <p class="text-muted mb-0">Find approved research papers across all categories</p>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h5>Filter & Search</h5>
            <form method="GET" action="">
                <div class="row">
                    <div class="col-md-4">
                        <div class="filter-group">
                            <label for="keyword">Keyword</label>
                            <input type="text" id="keyword" class="form-control" name="keyword" value="<?php echo htmlspecialchars($keyword); ?>" placeholder="Search title or author...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="filter-group">
                            <label for="category">Category</label>
                            <select id="category" class="form-control" name="category">
                                <option value="">All Categories</option>
                                <option value="Programming" <?php echo $category === 'Programming' ? 'selected' : ''; ?>>Programming</option>
                                <option value="Networking" <?php echo $category === 'Networking' ? 'selected' : ''; ?>>Networking</option>
                                <option value="Cybersecurity" <?php echo $category === 'Cybersecurity' ? 'selected' : ''; ?>>Cybersecurity</option>
                                <option value="Database" <?php echo $category === 'Database' ? 'selected' : ''; ?>>Database</option>
                                <option value="AI/Machine Learning" <?php echo $category === 'AI/Machine Learning' ? 'selected' : ''; ?>>AI/Machine Learning</option>
                                <option value="Web Development" <?php echo $category === 'Web Development' ? 'selected' : ''; ?>>Web Development</option>
                                <option value="Mobile Development" <?php echo $category === 'Mobile Development' ? 'selected' : ''; ?>>Mobile Development</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="filter-group">
                            <label for="sort">Sort By</label>
                            <select id="sort" class="form-control" name="sort">
                                <option value="recent" <?php echo $sortBy === 'recent' ? 'selected' : ''; ?>>Most Recent</option>
                                <option value="title" <?php echo $sortBy === 'title' ? 'selected' : ''; ?>>Title (A-Z)</option>
                                <option value="author" <?php echo $sortBy === 'author' ? 'selected' : ''; ?>>Author (A-Z)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <button type="submit" class="filter-btn"><i class="bi bi-search"></i> Search</button>
            </form>
        </div>

        <!-- Results Section -->
        <div class="results-section">
            <div class="results-header">
                <div class="results-count">
                    <strong><?php echo $totalResults; ?></strong> result<?php echo $totalResults !== 1 ? 's' : ''; ?> found
                </div>
                <?php if ($totalResults > 0): ?>
                    <div class="text-muted" style="font-size: 13px;">
                        Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                    </div>
                <?php endif; ?>
            </div>

            <?php
            if ($totalResults === 0) {
                echo '<div class="empty-state">';
                echo '<i class="bi bi-search"></i>';
                echo '<h3>No Research Found</h3>';
                echo '<p>Try adjusting your filters or search terms</p>';
                echo '<a href="index.php" class="btn btn-primary">Back to Home</a>';
                echo '</div>';
            } else {
                while ($row = $result->fetch_assoc()) {
                    $safeTitle = sanitizeOutput($row['title']);
                    $safeAuthor = sanitizeOutput($row['author']);
                    $safeCategory = sanitizeOutput($row['category']);
                    $safeAbstract = sanitizeOutput(substr($row['abstract'], 0, 150)) . (strlen($row['abstract']) > 150 ? '...' : '');
                    
                    echo '<div class="research-card">';
                    echo '<h3>' . $safeTitle . '</h3>';
                    echo '<div class="meta">';
                    echo '<div class="meta-item"><i class="bi bi-person"></i> ' . $safeAuthor . '</div>';
                    echo '<div class="meta-item"><i class="bi bi-calendar"></i> ' . date('M d, Y', strtotime($row['uploaded_at'] ?? 'now')) . '</div>';
                    echo '</div>';
                    
                    if (!empty($row['abstract'])) {
                        echo '<p class="abstract">' . $safeAbstract . '</p>';
                    }
                    
                    echo '<div class="badges">';
                    echo '<span class="badge badge-category"><i class="bi bi-tag"></i> ' . $safeCategory . '</span>';
                    echo '</div>';
                    
                    echo '<div class="btn-group">';
                    echo '<a href="uploads/' . htmlspecialchars($row['filename']) . '" target="_blank" class="btn-view"><i class="bi bi-file-pdf"></i> View Document</a>';
                    echo '</div>';
                    echo '</div>';
                }
                
                // Pagination
                if ($totalPages > 1) {
                    echo '<div class="pagination-section">';
                    echo '<nav aria-label="Page navigation">';
                    echo '<ul class="pagination">';
                    
                    // Previous button
                    if ($page > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?keyword=' . urlencode($keyword) . '&category=' . urlencode($category) . '&sort=' . urlencode($sortBy) . '&page=' . ($page - 1) . '">Previous</a></li>';
                    } else {
                        echo '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
                    }
                    
                    // Page numbers
                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);
                    
                    if ($startPage > 1) {
                        echo '<li class="page-item"><a class="page-link" href="?keyword=' . urlencode($keyword) . '&category=' . urlencode($category) . '&sort=' . urlencode($sortBy) . '&page=1">1</a></li>';
                        if ($startPage > 2) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++) {
                        if ($i === $page) {
                            echo '<li class="page-item active"><span class="page-link">' . $i . '</span></li>';
                        } else {
                            echo '<li class="page-item"><a class="page-link" href="?keyword=' . urlencode($keyword) . '&category=' . urlencode($category) . '&sort=' . urlencode($sortBy) . '&page=' . $i . '">' . $i . '</a></li>';
                        }
                    }
                    
                    if ($endPage < $totalPages) {
                        if ($endPage < $totalPages - 1) {
                            echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        echo '<li class="page-item"><a class="page-link" href="?keyword=' . urlencode($keyword) . '&category=' . urlencode($category) . '&sort=' . urlencode($sortBy) . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
                    }
                    
                    // Next button
                    if ($page < $totalPages) {
                        echo '<li class="page-item"><a class="page-link" href="?keyword=' . urlencode($keyword) . '&category=' . urlencode($category) . '&sort=' . urlencode($sortBy) . '&page=' . ($page + 1) . '">Next</a></li>';
                    } else {
                        echo '<li class="page-item disabled"><span class="page-link">Next</span></li>';
                    }
                    
                    echo '</ul>';
                    echo '</nav>';
                    echo '</div>';
                }
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
