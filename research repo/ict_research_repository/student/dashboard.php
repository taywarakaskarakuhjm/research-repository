<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['student'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['student'];
$student_id = $_SESSION['student_id'];
$error = $success = "";

// Handle research upload
if (isset($_POST['upload'])) {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $abstract = $_POST['abstract'];
    $file = $_FILES['file'];

    if ($file['error'] == 0) {
        $filename = time().'_'.$file['name'];
        $destination = '../uploads/'.$filename;
        $allowed = ['pdf'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $conn->prepare("INSERT INTO research (title, author, category, abstract, filename, status, date_uploaded) VALUES (?, ?, ?, ?, ?, 'Pending', NOW())");
                $stmt->bind_param("sssss", $title, $username, $category, $abstract, $filename);
                if($stmt->execute()){
                    $success = "Research uploaded successfully!";
                } else {
                    $error = "Database error: Could not upload research.";
                }
            } else {
                $error = "Failed to move uploaded file.";
            }
        } else {
            $error = "Only PDF files are allowed.";
        }
    } else {
        $error = "No file uploaded or file error.";
    }
}

// Handle research edit (revision)
if (isset($_POST['edit'])) {
    $research_id = $_POST['research_id'];
    $title = $_POST['title'];
    $category = $_POST['category'];
    $abstract = $_POST['abstract'];
    $file = $_FILES['file'];

    if ($file['error'] == 0) {
        $filename = time().'_'.$file['name'];
        $destination = '../uploads/'.$filename;
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $error = "Only PDF files are allowed for replacement.";
        } else {
            if (move_uploaded_file($file['tmp_name'], $destination)) {
                $stmt = $conn->prepare("UPDATE research SET title=?, category=?, abstract=?, filename=?, status='Pending' WHERE id=? AND author=?");
                $stmt->bind_param("ssssds", $title, $category, $abstract, $filename, $research_id, $username);
                $stmt->execute();
                $success = "Research updated successfully with new file.";
            } else {
                $error = "Failed to replace file.";
            }
        }
    } else {
        $stmt = $conn->prepare("UPDATE research SET title=?, category=?, abstract=?, status='Pending' WHERE id=? AND author=?");
        $stmt->bind_param("sssds", $title, $category, $abstract, $research_id, $username);
        if ($stmt->execute()) {
            $success = "Research updated successfully.";
        } else {
            $error = "Database error: Could not update research.";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f0f2f5; font-family: Arial, sans-serif; padding-top: 50px; }
        .dashboard-container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        h2, h4 { color: #333; }
        .table th, .table td { vertical-align: middle; }
        .btn { width: 100%; }
        .alert { font-size: 14px; }
        @media (max-width: 576px) { .dashboard-container { margin: 20px; padding: 20px; } }
    </style>
</head>
<body>
<div class="container">
    <div class="dashboard-container">
        <h2 class="text-center mb-4">Welcome, <?php echo htmlspecialchars($username); ?></h2>

        <!-- RRL Search Button -->
        <h4>Search Related Literature (RRL)</h4>
        <a href="search.php" class="btn btn-primary mb-4">Go to RRL Search</a>

        <!-- Upload Research Form -->
        <h4>Submit New Research</h4>
        <?php 
        if($error) echo "<div class='alert alert-danger'>$error</div>"; 
        if($success) echo "<div class='alert alert-success'>$success</div>";
        ?>
        <form method="POST" enctype="multipart/form-data" class="mb-4">
            <div class="mb-2"><input class="form-control" type="text" name="title" placeholder="Research Title" required></div>
            <div class="mb-2"><input class="form-control" type="text" name="category" placeholder="Category" required></div>
            <div class="mb-2"><textarea class="form-control" name="abstract" placeholder="Abstract" rows="3" required></textarea></div>
            <div class="mb-2"><input class="form-control" type="file" name="file" accept="application/pdf" required></div>
            <button class="btn btn-success mb-4" type="submit" name="upload">Upload Research</button>
        </form>

        <!-- Student's Uploaded Research -->
        <h4 class="mt-5">Your Uploaded Research</h4>
        <table class="table table-striped table-bordered mt-2">
            <thead class="table-dark">
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Admin Comment</th>
                    <th>View PDF</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM research WHERE author='$username' ORDER BY date_uploaded DESC";
                $result = mysqli_query($conn, $query);

                if(mysqli_num_rows($result) > 0){
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>".htmlspecialchars($row['title'])."</td>";
                        echo "<td>".htmlspecialchars($row['category'])."</td>";
                        echo "<td>".htmlspecialchars($row['status'])."</td>";
                        echo "<td>".htmlspecialchars($row['admin_comment'])."</td>";
                        echo "<td><a href='../uploads/".$row['filename']."' target='_blank' class='btn btn-primary btn-sm'>View PDF</a></td>";
                        echo "<td>";
                        if($row['status'] === 'For Revision'){
                            echo "<button class='btn btn-warning btn-sm' data-bs-toggle='collapse' data-bs-target='#editForm".$row['id']."'>Edit</button>";
                        }
                        echo "</td>";
                        echo "</tr>";

                        // Edit form (collapse)
                        if($row['status'] === 'For Revision'){
                            echo "<tr class='collapse' id='editForm".$row['id']."'><td colspan='6'>";
                            echo "<form method='POST' enctype='multipart/form-data'>";
                            echo "<input type='hidden' name='research_id' value='".$row['id']."'>";
                            echo "<div class='mb-2'><input class='form-control' type='text' name='title' value='".htmlspecialchars($row['title'])."' required></div>";
                            echo "<div class='mb-2'><input class='form-control' type='text' name='category' value='".htmlspecialchars($row['category'])."' required></div>";
                            echo "<div class='mb-2'><textarea class='form-control' name='abstract' rows='3' required>".htmlspecialchars($row['abstract'])."</textarea></div>";
                            echo "<div class='mb-2'><input class='form-control' type='file' name='file' accept='application/pdf'></div>";
                            echo "<button class='btn btn-success' type='submit' name='edit'>Submit Revision</button>";
                            echo "</form>";
                            echo "</td></tr>";
                        }
                    }
                } else {
                    echo "<tr><td colspan='6' class='text-center'>No research uploaded yet.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>