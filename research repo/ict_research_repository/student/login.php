<?php
session_start();
include '../config/db.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Plain text password login
    $stmt = $conn->prepare("SELECT * FROM students WHERE username=? AND password=?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        $_SESSION['student'] = $username;
        $_SESSION['student_id'] = $row['id'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Invalid Login!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: Arial, sans-serif;
        }
        .login-container {
            max-width: 450px;
            margin: 120px auto;
            padding: 35px 30px;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
        }
        h2 {
            font-weight: 600;
            color: #333333;
        }
        .form-control {
            height: 45px;
            font-size: 16px;
        }
        .btn-primary {
            height: 45px;
            font-size: 16px;
        }
        .alert {
            font-size: 14px;
        }
        @media (max-width: 576px) {
            .login-container {
                margin: 50px 15px;
                padding: 25px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <div class="login-container">
        <h2 class="text-center mb-4">Student Login</h2>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <input class="form-control mb-3" type="text" name="username" placeholder="Username" required>
            <input class="form-control mb-3" type="password" name="password" placeholder="Password" required>
            <button class="btn btn-primary w-100" type="submit" name="login">Login</button>
        </form>
    </div>
</div>
</body>
</html>