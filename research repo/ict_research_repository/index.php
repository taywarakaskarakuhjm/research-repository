<?php
include 'config/db.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>ICT Research Repository</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 60px;
            background-color: #f8f9fa;
        }
        .hero {
            padding: 60px;
            background-color: #007bff;
            color: white;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 30px;
        }
        .search-card {
            padding: 20px;
            border-radius: 10px;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>

<div class="container">

    <!-- Hero Section -->
    <div class="hero">
        <h1>Welcome to ICT Research Repository</h1>
        <p>Search and explore student research papers and RRLs</p>
    </div>

    <!-- Search Form -->
    <div class="search-card">
        <h4>Find Research / RRL</h4>
        <form method="GET" action="search.php">
            <div class="row mb-2">
                <div class="col-md-6">
                    <input type="text" class="form-control" name="keyword" placeholder="Search by Title or Author">
                </div>
                <div class="col-md-4">
                    <select class="form-control" name="category">
                        <option value="">All Categories</option>
                        <option>Programming</option>
                        <option>Networking</option>
                        <option>Cybersecurity</option>
                        <option>Database</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary w-100" type="submit">Search</button>
                </div>
            </div>
        </form>
    </div>

    <!-- Optional: Test DB Connection -->
    <div class="mt-4">
        <?php
        if($conn) {
            echo "<div class='alert alert-success'>Database Connected Successfully!</div>";
        } else {
            echo "<div class='alert alert-danger'>Database Connection Failed!</div>";
        }
        ?>
    </div>

    <!-- Optional: Quick Links -->
    <div class="mt-4">
        <a href="admin/login.php" class="btn btn-warning me-2">Admin Login</a>
        <a href="student/login.php" class="btn btn-success">Student Login</a>
    </div>

</div>

</body>
</html>