<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

// Check if student is logged in
requireLogin('student');

$message = '';
$messageType = '';
$uploadDir = '../uploads/';

// Create uploads directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

if (isset($_POST['submit'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $message = "Invalid request. Please try again.";
        $messageType = "error";
    } else {
        // Sanitize and validate inputs
        $title = sanitizeInput($_POST['title'] ?? '');
        $category = sanitizeInput($_POST['category'] ?? '');
        $abstract = sanitizeInput($_POST['abstract'] ?? '');
        $author = $_SESSION['student'];

        // Validate inputs
        if (empty($title)) {
            $message = "Title is required.";
            $messageType = "error";
        } elseif (strlen($title) < 5) {
            $message = "Title must be at least 5 characters long.";
            $messageType = "error";
        } elseif (empty($category)) {
            $message = "Category is required.";
            $messageType = "error";
        } elseif (empty($abstract)) {
            $message = "Abstract is required.";
            $messageType = "error";
        } elseif (!isset($_FILES['file']) || $_FILES['file']['error'] != 0) {
            $message = "Please select a file to upload.";
            $messageType = "error";
        } else {
            // Validate file upload
            $fileValidation = validateFileUpload(
                $_FILES['file'],
                5242880, // 5MB max
                ['pdf', 'doc', 'docx', 'txt']
            );

            if (!$fileValidation['valid']) {
                $message = $fileValidation['error'];
                $messageType = "error";
            } else {
                // Generate safe filename
                $safeFilename = generateSafeFilename($_FILES['file']['name']);
                $targetPath = $uploadDir . $safeFilename;

                // Move uploaded file
                if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                    // Insert into database
                    $stmt = $conn->prepare("INSERT INTO research (title, author, category, abstract, filename, uploaded_at, status) VALUES (?, ?, ?, ?, ?, NOW(), 'Pending')");
                    
                    if ($stmt) {
                        $stmt->bind_param("sssss", $title, $author, $category, $abstract, $safeFilename);
                        
                        if ($stmt->execute()) {
                            $message = "Research submitted successfully! It is now pending admin review.";
                            $messageType = "success";
                        } else {
                            // Delete uploaded file if database insert fails
                            unlink($targetPath);
                            $message = "Database error. Please try again.";
                            $messageType = "error";
                        }
                        $stmt->close();
                    } else {
                        unlink($targetPath);
                        $message = "Database error. Please try again.";
                        $messageType = "error";
                    }
                } else {
                    $message = "Failed to upload file. Please try again.";
                    $messageType = "error";
                }
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
    <title>Upload Research - ICT Research Repository</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: #0D47A1;
            --secondary-color: #00897B;
            --light-gray: #F5F5F5;
            --dark-color: #212529;
            --success-color: #4CAF50;
        }

        body {
            background-color: var(--light-gray);
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .navbar {
            background-color: var(--primary-color);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 18px;
        }

        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: white !important;
        }

        .container-main {
            max-width: 700px;
            margin: 40px auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            padding: 40px;
        }

        .page-header {
            margin-bottom: 30px;
        }

        .page-header h1 {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 28px;
            margin-bottom: 8px;
        }

        .page-header p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 71, 161, 0.1);
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .file-input-wrapper {
            position: relative;
            overflow: hidden;
        }

        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }

        .file-input-label {
            display: block;
            padding: 30px;
            text-align: center;
            border: 2px dashed #e0e0e0;
            border-radius: 8px;
            background-color: #fafafa;
            cursor: pointer;
            transition: all 0.3s;
        }

        .file-input-wrapper input[type=file]:focus + .file-input-label,
        .file-input-label:hover {
            border-color: var(--primary-color);
            background-color: #f0f7ff;
        }

        .file-input-label .icon {
            font-size: 32px;
            color: var(--primary-color);
            display: block;
            margin-bottom: 8px;
        }

        .file-input-label .text {
            color: var(--dark-color);
            font-weight: 500;
            display: block;
            margin-bottom: 4px;
        }

        .file-input-label .subtext {
            color: #999;
            font-size: 12px;
        }

        .btn-submit {
            height: 48px;
            background-color: var(--success-color);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s;
            width: 100%;
        }

        .btn-submit:hover {
            background-color: #45a049;
            color: white;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 24px;
        }

        .help-text {
            font-size: 12px;
            color: #999;
            margin-top: 6px;
        }

        @media (max-width: 576px) {
            .container-main {
                margin: 20px;
                padding: 20px;
            }

            .page-header h1 {
                font-size: 22px;
            }

            .file-input-label {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">ICT Repository</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">My Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="upload.php">Upload Research</a></li>
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-main">
        <div class="page-header">
            <h1>Upload Research</h1>
            <p>Submit your research paper for review</p>
        </div>

        <?php 
        if (!empty($message)) {
            if ($messageType === 'success') {
                echo showSuccess($message);
            } else {
                echo showError($message);
            }
        }
        ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">

            <div class="form-group">
                <label for="title" class="form-label">Research Title *</label>
                <input class="form-control" type="text" id="title" name="title" placeholder="Enter the title of your research" maxlength="255" required>
                <div class="help-text">Minimum 5 characters</div>
            </div>

            <div class="form-group">
                <label for="category" class="form-label">Category *</label>
                <select class="form-control" id="category" name="category" required>
                    <option value="">Select a category</option>
                    <option value="Programming">Programming</option>
                    <option value="Networking">Networking</option>
                    <option value="Cybersecurity">Cybersecurity</option>
                    <option value="Database">Database</option>
                    <option value="AI/Machine Learning">AI/Machine Learning</option>
                    <option value="Web Development">Web Development</option>
                    <option value="Mobile Development">Mobile Development</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="abstract" class="form-label">Abstract *</label>
                <textarea class="form-control" id="abstract" name="abstract" placeholder="Provide a brief summary of your research" required></textarea>
                <div class="help-text">Describe the key findings and contributions of your research</div>
            </div>

            <div class="form-group">
                <label for="file" class="form-label">Research Document *</label>
                <div class="file-input-wrapper">
                    <input type="file" id="file" name="file" accept=".pdf,.doc,.docx,.txt" required>
                    <label for="file" class="file-input-label">
                        <span class="icon">📄</span>
                        <span class="text">Click to upload or drag and drop</span>
                        <span class="subtext">PDF, DOC, DOCX, or TXT (Max 5MB)</span>
                    </label>
                </div>
            </div>

            <button class="btn btn-submit" type="submit" name="submit">Submit Research</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
