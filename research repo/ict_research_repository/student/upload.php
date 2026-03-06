<?php
session_start();
include '../config/db.php';

if(!isset($_SESSION['student'])) {
    header("Location: login.php");
    exit();
}

if(isset($_POST['submit'])) {
    $title = $_POST['title'];
    $author = $_SESSION['student'];
    $category = $_POST['category'];
    $abstract = $_POST['abstract'];

    $filename = $_FILES['file']['name'];
    $tempname = $_FILES['file']['tmp_name'];
    $target = "../uploads/".$filename;

    if(move_uploaded_file($tempname, $target)) {
        $stmt = $conn->prepare("INSERT INTO research (title, author, category, abstract, filename) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $title, $author, $category, $abstract, $filename);
        $stmt->execute();
        $msg = "Research submitted successfully!";
    } else {
        $msg = "File upload failed!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
<title>Upload Research</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
<div class="container">
<h2>Upload Research</h2>
<?php if(isset($msg)) echo "<div class='alert alert-info'>$msg</div>"; ?>
<form method="POST" enctype="multipart/form-data">
    <input class="form-control mb-2" type="text" name="title" placeholder="Title" required>
    <select class="form-control mb-2" name="category">
        <option>Programming</option>
        <option>Networking</option>
        <option>Cybersecurity</option>
        <option>Database</option>
    </select>
    <textarea class="form-control mb-2" name="abstract" rows="5" placeholder="Abstract"></textarea>
    <input class="form-control mb-2" type="file" name="file" required>
    <button class="btn btn-success" type="submit" name="submit">Submit</button>
</form>
</div>
</body>
</html>