<?php
include 'config/db.php';

if (isset($_POST['submit'])) {

    $title = $_POST['title'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $abstract = $_POST['abstract'];

    $filename = $_FILES['file']['name'];
    $tempname = $_FILES['file']['tmp_name'];

    $target = "uploads/" . $filename;

    if (move_uploaded_file($tempname, $target)) {

        $query = "INSERT INTO research (title, author, category, abstract, filename)
                VALUES ('$title', '$author', '$category', '$abstract', '$filename')";

        mysqli_query($conn, $query);

        echo "<p style='color:green;'>Research submitted successfully! Waiting for admin approval.</p>";
    } else {
        echo "<p style='color:red;'>File upload failed!</p>";
    }
}
?>

<h2>Upload ICT Research</h2>

<form method="POST" enctype="multipart/form-data">
    Title:<br>
    <input type="text" name="title" required><br><br>

    Author:<br>
    <input type="text" name="author" required><br><br>

    Category:<br>
    <select name="category">
        <option>Programming</option>
        <option>Networking</option>
        <option>Cybersecurity</option>
        <option>Database</option>
    </select><br><br>

    Abstract:<br>
    <textarea name="abstract" rows="5"></textarea><br><br>

    Upload PDF:<br>
    <input type="file" name="file" required><br><br>

    <button type="submit" name="submit">Submit Research</button>
</form>