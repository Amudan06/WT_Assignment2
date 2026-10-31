<?php
/**
 * index.php - READ (list): shows every student with a search box.
 */
$pageTitle = "All Students";
require __DIR__ . "/includes/header.php";

$search = trim($_GET["q"] ?? "");

// LEFT JOIN brings the profile photo path (if any) in the same query
$sql = "SELECT s.*, f.file_path AS photo
        FROM students s
        LEFT JOIN student_files f ON f.student_id = s.id AND f.category = \"profile_image\"";
$params = [];
if ($search !== "") {
    $sql .= " WHERE s.name LIKE ? OR s.roll_number LIKE ? OR s.course LIKE ?";
    $params = ["%$search%", "%$search%", "%$search%"];
}
$sql .= " ORDER BY s.id DESC";

$stmt = db()->prepare($sql);
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<div class="page-head">
  <h1>Student Records <span class="badge"><?= count($students) ?></span></h1>
  <a class="btn" href="add.php">+ Add Student</a>
</div>

<div class="card">
  <form method="GET" style="display:flex;gap:.6rem;margin-bottom:1rem;flex-wrap:wrap">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="Search by name, roll number or course" style="flex:1;min-width:200px">
    <button class="btn" type="submit">Search</button>
    <?php if ($search !== ""): ?><a class="btn secondary" href="index.php">Reset</a><?php endif; ?>
  </form>

  <div class="table-wrap">
  <table>
    <thead><tr><th></th><th>Name</th><th>Roll No.</th><th>Course</th><th>Email</th><th>Actions</th></tr></thead>
    <tbody>
    <?php foreach ($students as $s): ?>
      <tr>
        <td>
          <?php if ($s["photo"]): ?>
            <img class="avatar" src="<?= e($s["photo"]) ?>" alt="">
          <?php else: ?>
            <span class="avatar"><?= e(initials($s["name"])) ?></span>
          <?php endif; ?>
        </td>
        <td><strong><?= e($s["name"]) ?></strong></td>
        <td><?= e($s["roll_number"]) ?></td>
        <td><?= e($s["course"]) ?></td>
        <td><?= e($s["email"]) ?></td>
        <td style="white-space:nowrap">
          <a class="btn small secondary" href="view.php?id=<?= (int) $s["id"] ?>">View</a>
          <a class="btn small" href="edit.php?id=<?= (int) $s["id"] ?>">Edit</a>
          <!-- delete uses POST (never a plain link) so crawlers cannot delete data -->
          <form method="POST" action="delete.php" style="display:inline"
                onsubmit="return confirm('Delete <?= e(addslashes($s["name"])) ?> and all uploaded files?');">
            <input type="hidden" name="id" value="<?= (int) $s["id"] ?>">
            <button class="btn small danger" type="submit">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$students): ?>
      <tr><td colspan="6" class="muted" style="text-align:center;padding:2rem">No students found.</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>
