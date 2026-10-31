<?php
/**
 * view.php - READ (single record): student details + all uploaded files.
 */
$pageTitle = "Student Details";
require __DIR__ . "/includes/header.php";

$id   = (int) ($_GET["id"] ?? 0);
$stmt = db()->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$student = $stmt->fetch();

if (!$student) {
    echo "<div class=\"alert error\">Student not found.</div><a class=\"btn\" href=\"index.php\">Back</a>";
    require __DIR__ . "/includes/footer.php";
    exit;
}

$files = files_of($id);
$photo = null;
foreach ($files as $f) if ($f["category"] === "profile_image") { $photo = $f; break; }
$showDelete = false;                    // viewing only - no delete buttons here
?>
<div class="card">
  <div class="profile-head">
    <?php if ($photo): ?>
      <img class="avatar big" src="<?= e($photo["file_path"]) ?>" alt="Profile photo">
    <?php else: ?>
      <span class="avatar big"><?= e(initials($student["name"])) ?></span>
    <?php endif; ?>
    <div>
      <h1 style="margin:0"><?= e($student["name"]) ?></h1>
      <span class="badge"><?= e($student["roll_number"]) ?></span>
      <p class="muted"><?= e($student["course"]) ?></p>
    </div>
  </div>

  <table class="details">
    <tr><th>Email</th><td><?= e($student["email"]) ?></td></tr>
    <tr><th>Roll number</th><td><?= e($student["roll_number"]) ?></td></tr>
    <tr><th>Course</th><td><?= e($student["course"]) ?></td></tr>
    <tr><th>Registered on</th><td><?= e(date("d M Y, h:i A", strtotime($student["created_at"]))) ?></td></tr>
    <tr><th>Last updated</th><td><?= e(date("d M Y, h:i A", strtotime($student["updated_at"]))) ?></td></tr>
  </table>

  <h2 style="margin-top:1.8rem">Uploaded Files <span class="badge"><?= count($files) ?></span></h2>
  <?php if ($files): include __DIR__ . "/includes/file_gallery.php"; else: ?>
    <p class="muted">No files uploaded yet.</p>
  <?php endif; ?>

  <div class="actions">
    <a class="btn" href="edit.php?id=<?= (int) $student["id"] ?>">Edit</a>
    <a class="btn secondary" href="index.php">Back to list</a>
  </div>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>
