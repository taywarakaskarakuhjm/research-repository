<?php
include 'config/db.php';

$keyword = $_GET['keyword'] ?? '';
$category = $_GET['category'] ?? '';

$query = "SELECT * FROM research WHERE status='Approved' AND (title LIKE ? OR author LIKE ?)";
if($category != '') $query .= " AND category=?";

$stmt = $conn->prepare($query);
$like = "%$keyword%";

if($category != '') $stmt->bind_param("sss",$like,$like,$category);
else $stmt->bind_param("ss",$like,$like);

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
<title>Search Research</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-5">
<div class="container">
<h2>Search Research</h2>
<form method="GET">
<input class="form-control mb-2" type="text" name="keyword" placeholder="Search Title or Author">
<select class="form-control mb-2" name="category">
<option value="">All Categories</option>
<option>Programming</option>
<option>Networking</option>
<option>Cybersecurity</option>
<option>Database</option>
</select>
<button class="btn btn-primary mb-2" type="submit">Search</button>
</form>

<?php
while($row = $result->fetch_assoc()) {
    echo "<div class='card mb-2 p-2'>";
    echo "<h4>".$row['title']."</h4>";
    echo "<p>Author: ".$row['author']." | Category: ".$row['category']."</p>";
    echo "<a href='uploads/".$row['filename']."' target='_blank' class='btn btn-info'>View PDF</a>";
    echo "</div>";
}
?>
</div>
</body>
</html>