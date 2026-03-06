<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch statistics
$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM research"))['total'];
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM research WHERE status='Pending'"))['total'];
$approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM research WHERE status='Approved'"))['total'];
$revision = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM research WHERE status='For Revision'"))['total'];
$rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM research WHERE status='Rejected'"))['total'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 50px;
        }
        .stats-card {
            border-radius: 10px;
            padding: 20px;
            color: white;
            text-align: center;
            margin-bottom: 20px;
        }
        .stats-total { background-color: #007bff; }
        .stats-pending { background-color: #ffc107; }
        .stats-approved { background-color: #28a745; }
        .stats-revision { background-color: #17a2b8; }
        .stats-rejected { background-color: #dc3545; }
        .research-table th, .research-table td {
            vertical-align: middle;
        }
        .review-btn {
            text-decoration: none;
        }
        .review-btn button {
            width: 100%;
        }
    </style>
</head>
<body>
<div class="container">
    <h2 class="mb-4 text-center">Admin Research Dashboard</h2>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="stats-card stats-total">
                <h5>Total Research</h5>
                <h3><?php echo $total; ?></h3>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card stats-pending">
                <h5>Pending</h5>
                <h3><?php echo $pending; ?></h3>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card stats-approved">
                <h5>Approved</h5>
                <h3><?php echo $approved; ?></h3>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card stats-revision">
                <h5>For Revision</h5>
                <h3><?php echo $revision; ?></h3>
            </div>
        </div>
        <div class="col-md-2">
            <div class="stats-card stats-rejected">
                <h5>Rejected</h5>
                <h3><?php echo $rejected; ?></h3>
            </div>
        </div>
    </div>

    <!-- Research Checklist -->
    <h3>Research Checklist</h3>
    <table class="table table-striped table-bordered research-table">
        <thead class="table-dark">
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Status</th>
                <th>Date Uploaded</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $query = "SELECT * FROM research ORDER BY date_uploaded DESC";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            echo "<tr>";
            echo "<td>".$row['title']."</td>";
            echo "<td>".$row['author']."</td>";
            echo "<td><b>".$row['status']."</b></td>";
            echo "<td>".$row['date_uploaded']."</td>";
            echo "<td class='review-btn'><a href='review.php?id=".$row['id']."'><button class='btn btn-primary btn-sm'>Review</button></a></td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>