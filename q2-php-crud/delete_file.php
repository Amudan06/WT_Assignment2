<?php
/**
 * delete_file.php - removes ONE uploaded file (used on the edit page). POST only.
 */
require __DIR__ . "/includes/helpers.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

$fileId    = (int) ($_POST["file_id"] ?? 0);
$studentId = (int) ($_POST["student_id"] ?? 0);

// student_id is part of the WHERE clause so one student cannot delete another one"s file
$stmt = db()->prepare("SELECT * FROM student_files WHERE id = ? AND student_id = ?");
$stmt->execute([$fileId, $studentId]);
$row = $stmt->fetch();

if ($row) {
    delete_file_record($row);
    flash("success", "File deleted.");
} else {
    flash("error", "File not found.");
}
redirect("edit.php?id=" . $studentId);
