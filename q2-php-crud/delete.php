<?php
/**
 * delete.php - DELETE a student. Accepts POST only.
 * Physical files are removed first; the file rows disappear automatically
 * because of ON DELETE CASCADE in the database schema.
 */
require __DIR__ . "/includes/helpers.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

$id = (int) ($_POST["id"] ?? 0);
$stmt = db()->prepare("SELECT name FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    flash("error", "Student not found.");
} else {
    foreach (files_of($id) as $f) {
        $path = UPLOAD_DIR . basename($f["stored_name"]);
        if (is_file($path)) unlink($path);
    }
    db()->prepare("DELETE FROM students WHERE id = ?")->execute([$id]);
    flash("success", "Student " . $student["name"] . " and all uploaded files were deleted.");
}
redirect("index.php");
