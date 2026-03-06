<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Check if admin is logged in
requireLogin('admin');

// Get research ID and validate it
$id = intval($_GET['id'] ?? 0);
if ($id === 0) {
    header("Location: dashboard.php");
    exit();
}

// Fetch research details securely
$stmt = $conn->prepare("SELECT id, title, author, category, abstract, filename, status, admin_comment, uploaded_at FROM research WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: dashboard.php");
    exit();
}

$row = $result->fetch_assoc();
$message = '';
$messageType = '';

// Handle form submission
if (isset($_POST['update'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = "Invalid request. Please try again.";
        $messageType = "error";
    } else {
        // Sanitize inputs
        $status = sanitizeInput($_POST['status'] ?? '');
        $comment = sanitizeInput($_POST['comment'] ?? '');
        
        // Validate status
        $validStatuses = ['Approved', 'Rejected', 'For Revision'];
        if (!in_array($status, $validStatuses)) {
            $message = "Invalid status selected.";
            $messageType = "error";
        } else {
            // Update database
            $stmt = $conn->prepare("UPDATE research SET status=?, admin_comment=? WHERE id=?");
            if ($stmt) {
                $stmt->bind_param("ssi", $status, $comment, $id);
                if ($stmt->execute()) {
                    $message = "Research review updated successfully!";
                    $messageType = "success";
                    // Refresh row data
                    $row['status'] = $status;
                    $row['admin_comment'] = $comment;
                } else {
                    $message = "Error updating review. Please try again.";
                    $messageType = "error";
                }
                $stmt->close();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Research - ICT Research Repository</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .review-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            margin-top: 20px;
        }

        .review-header h1 {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 15px;
        }

        .research-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .meta-item {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
        }

        .meta-item label {
            font-weight: 700;
            color: var(--dark-color);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .meta-item value {
            display: block;
            color: #666;
            font-size: 14px;
        }

        .document-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .document-section h5 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 20px;
            font-size: 16px;
        }

        .document-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .btn-view-doc {
            background-color: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-view-doc:hover {
            background-color: #006B5B;
            color: white;
        }

        .document-preview {
            background-color: #f5f5f5;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            min-height: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Review Form */
        .review-form-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .review-form-section h5 {
            color: var(--dark-color);
            font-weight: 700;
            margin-bottom: 25px;
            font-size: 16px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 12px;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control,
        .form-select {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 71, 161, 0.1);
        }

        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }

        .status-indicator {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .status-indicator.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-indicator.approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-indicator.revision {
            background-color: #ffe0b2;
            color: #e65100;
        }

        .status-indicator.rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-submit {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background-color: #0A3B8F;
        }

        .btn-cancel {
            background-color: #ccc;
            color: #333;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }

        .btn-cancel:hover {
            background-color: #999;
            color: white;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .research-meta {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-submit,
            .btn-cancel {
                width: 100%;
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
        <!-- Back Link -->
        <div style="margin-top: 20px;">
            <a href="dashboard.php" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <!-- Review Header -->
        <div class="review-header">
            <h1><i class="bi bi-file-earmark-text"></i> <?php echo sanitizeOutput($row['title']); ?></h1>
            
            <div class="status-indicator <?php echo strtolower(str_replace(' ', '-', $row['status'])); ?>">
                <i class="bi bi-info-circle"></i> Current Status: <?php echo htmlspecialchars($row['status']); ?>
            </div>

            <div class="research-meta">
                <div class="meta-item">
                    <label>Author</label>
                    <value><?php echo sanitizeOutput($row['author']); ?></value>
                </div>
                <div class="meta-item">
                    <label>Category</label>
                    <value><?php echo sanitizeOutput($row['category']); ?></value>
                </div>
                <div class="meta-item">
                    <label>Submitted On</label>
                    <value><?php echo date('M d, Y', strtotime($row['uploaded_at'])); ?></value>
                </div>
                <div class="meta-item">
                    <label>Filename</label>
                    <value style="word-break: break-all;"><?php echo htmlspecialchars($row['filename']); ?></value>
                </div>
            </div>
        </div>

        <!-- Messages -->
        <?php
        if (!empty($message)) {
            if ($messageType === 'success') {
                echo showSuccess($message);
            } else {
                echo showError($message);
            }
        }
        ?>

        <!-- Document Section -->
        <div class="document-section">
            <h5><i class="bi bi-file-pdf"></i> Document Preview</h5>
            <div class="document-actions">
                <a href="../uploads/<?php echo htmlspecialchars($row['filename']); ?>" target="_blank" class="btn-view-doc">
                    <i class="bi bi-download"></i> Download Document
                </a>
                <a href="../uploads/<?php echo htmlspecialchars($row['filename']); ?>" target="_blank" class="btn-view-doc">
                    <i class="bi bi-eye"></i> View Document
                </a>
            </div>
            <div class="document-preview">
                <div>
                    <i class="bi bi-file-earmark-pdf" style="font-size: 48px; color: #ccc;"></i>
                    <p style="color: #999; margin-top: 15px;">Click "Download" or "View Document" to open the file</p>
                </div>
            </div>
        </div>

        <!-- Abstract Section -->
        <?php if (!empty($row['abstract'])): ?>
        <div class="document-section">
            <h5><i class="bi bi-file-text"></i> Abstract</h5>
            <p style="color: #666; line-height: 1.6; margin: 0;">
                <?php echo nl2br(sanitizeOutput($row['abstract'])); ?>
            </p>
        </div>
        <?php endif; ?>

        <!-- Review Form Section -->
        <div class="review-form-section">
            <h5><i class="bi bi-pencil-square"></i> Review & Decision</h5>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

                <div class="form-group">
                    <label for="status" class="form-label">Decision</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="">Select a decision</option>
                        <option value="Approved" <?php echo $row['status'] === 'Approved' ? 'selected' : ''; ?>>Approve</option>
                        <option value="For Revision" <?php echo $row['status'] === 'For Revision' ? 'selected' : ''; ?>>Request Revision</option>
                        <option value="Rejected" <?php echo $row['status'] === 'Rejected' ? 'selected' : ''; ?>>Reject</option>
                    </select>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle"></i> Select the appropriate decision for this submission
                    </small>
                </div>

                <div class="form-group">
                    <label for="comment" class="form-label">Admin Comments</label>
                    <textarea id="comment" name="comment" class="form-control" placeholder="Provide feedback or specific notes for the author..."><?php echo !empty($row['admin_comment']) ? htmlspecialchars($row['admin_comment']) : ''; ?></textarea>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle"></i> These comments will be visible to the author
                    </small>
                </div>

                <div class="form-actions">
                    <button type="submit" name="update" class="btn-submit">
                        <i class="bi bi-check-circle"></i> Update Review
                    </button>
                    <a href="dashboard.php" class="btn-cancel">
                        <i class="bi bi-x-circle"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
