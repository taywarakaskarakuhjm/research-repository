<?php
session_start();
include '../config/db.php';
include '../includes/functions.php';

$error = '';
$success = '';

if (isset($_POST['login'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $error = "Invalid request. Please try again.";
    } else {
        // Sanitize input
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validate input
        if (empty($username) || empty($password)) {
            $error = "Username and password are required.";
        } else {
            // Query database for admin (using prepared statement for safety)
            $stmt = $conn->prepare("SELECT id, username, password FROM admin WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                
                // Verify password (supports both hashed and plain text for migration)
                if (password_verify($password, $row['password']) || $password === $row['password']) {
                    // If plain text password, update it to hashed version
                    if ($password === $row['password']) {
                        $hashedPassword = hashPassword($password);
                        $updateStmt = $conn->prepare("UPDATE admin SET password = ? WHERE id = ?");
                        $updateStmt->bind_param("si", $hashedPassword, $row['id']);
                        $updateStmt->execute();
                    }
                    
                    $_SESSION['admin'] = $username;
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['login_time'] = time();
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid username or password.";
                }
            } else {
                $error = "Invalid username or password.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - ICT Research Repository</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary-color: #0D47A1;
            --secondary-color: #00897B;
            --light-gray: #F5F5F5;
            --dark-color: #212529;
        }

        body {
            background: linear-gradient(135deg, #1a237e 0%, #0D47A1 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }

        .login-container {
            max-width: 420px;
            width: 100%;
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            padding: 40px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: #1a237e;
            margin-bottom: 8px;
        }

        .login-header p {
            color: #666;
            font-size: 14px;
            margin: 0;
        }

        .admin-badge {
            display: inline-block;
            background-color: #FF6F00;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            height: 48px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 71, 161, 0.1);
        }

        .btn-login {
            height: 48px;
            background-color: var(--primary-color);
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .btn-login:hover {
            background-color: #0A3B8F;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 20px;
        }

        @media (max-width: 576px) {
            .login-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-container">
        <div class="login-header">
            <div class="admin-badge">ADMIN PORTAL</div>
            <h1>Administrator</h1>
            <p>ICT Research Repository</p>
        </div>

        <?php 
        if (!empty($error)) {
            echo showError($error);
        }
        if (!empty($success)) {
            echo showSuccess($success);
        }
        ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="username" class="form-label">Username</label>
                <input class="form-control" type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input class="form-control" type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>

            <button class="btn btn-login w-100" type="submit" name="login">Login</button>
        </form>

        <hr class="my-4">
        <p class="text-center text-muted small">
            Authorized personnel only. All access is logged.
        </p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
