<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['admin']))
    header("Location: login.php");

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM research WHERE id=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (isset($_POST['update'])) {
    $status = $_POST['status'];
    $comment = $_POST['comment'];

    $stmt = $conn->prepare("UPDATE research SET status=?, admin_comment=? WHERE id=?");
    $stmt->bind_param("ssi", $status, $comment, $id);
    $stmt->execute();

    // Optional: send email here using PHPMailer if configured

    header("Location: dashboard.php");
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>Review Research</title>
    <script src="https://mozilla.github.io/pdf.js/build/pdf.mjs" type="module"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script type="module">
        var { pdfjsLib } = globalThis;
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://mozilla.github.io/pdf.js/build/pdf.worker.mjs';
        let url = "../uploads/<?php echo $row['filename']; ?>";

        var loadingTask = pdfjsLib.getDocument(url);
        loadingTask.promise.then(function (pdf) {
            console.log('PDF loaded');

            // Fetch the first page
            var pageNumber = 1;
            pdf.getPage(pageNumber).then(function (page) {
                console.log('Page loaded');

                var scale = 1.5;
                var viewport = page.getViewport({ scale: scale });

                // Prepare canvas using PDF page dimensions
                var canvas = document.getElementById('the-canvas');
                var context = canvas.getContext('2d');
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                // Render PDF page into canvas context
                var renderContext = {
                    canvasContext: context,
                    viewport: viewport
                };
                var renderTask = page.render(renderContext);
                renderTask.promise.then(function () {
                    console.log('Page rendered');
                });
            });
        }, function (reason) {
            // PDF loading error
            console.error(reason);
        });
    </script>
</head>

<body class="p-5">
    <div class="container">
        <h2>Review: <?php echo $row['title']; ?></h2>
        <p>Author: <?php echo $row['author']; ?></p>
        <a href="../uploads/<?php echo $row['filename']; ?>" target="_blank" class="btn btn-info mb-2">View PDF</a>

        <form method="POST">
            <select class="form-control mb-2" name="status">
                <option <?php if ($row['status'] == 'Approved')
                    echo 'selected'; ?>>Approved</option>
                <option <?php if ($row['status'] == 'Rejected')
                    echo 'selected'; ?>>Rejected</option>
                <option <?php if ($row['status'] == 'For Revision')
                    echo 'selected'; ?>>For Revision</option>
            </select>
            <textarea class="form-control mb-2" name="comment" rows="5"
                placeholder="Admin Comment"><?php echo $row['admin_comment']; ?></textarea>
            <button class="btn btn-primary" type="submit" name="update">Update</button>
        </form>

        <canvas id="the-canvas"></canvas>
    </div>
</body>

</html>