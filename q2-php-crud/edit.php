<?php
/**
 * edit.php - UPDATE: loads the record (GET) and saves changes (POST).
 * Uploading a new profile photo / ID proof REPLACES the old one;
 * new certificates are ADDED to the existing ones.
 */
$pageTitle = "Edit Student";
require __DIR__ . "/includes/helpers.php";

$id   = (int) ($_GET["id"] ?? $_POST["id"] ?? 0);
$stmt = db()->prepare("SELECT * FROM students WHERE id = ?");
$stmt->execute([$id]);
$current = $stmt->fetch();
if (!$current) {
    flash("error", "Student not found.");
    redirect("index.php");
}

$errors  = [];
$student = $current;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student = array_merge($current, $_POST);

    $errors = validate_student($_POST, false, $id);       // password optional when editing
    [$fileErrors, $uploads] = validate_all_uploads(false);
    $errors = array_merge($errors, $fileErrors);

    if (!$errors) {
        $pdo = db();
        $saved = [];
        try {
            $pdo->beginTransaction();

            // only touch the password column when a new password was typed
            if ($_POST["password"] !== "") {
                $sql = "UPDATE students SET name=?, roll_number=?, email=?, course=?, password_hash=? WHERE id=?";
                $args = [trim($_POST["name"]), strtoupper(trim($_POST["roll_number"])), trim($_POST["email"]),
                         $_POST["course"], password_hash($_POST["password"], PASSWORD_DEFAULT), $id];
            } else {
                $sql = "UPDATE students SET name=?, roll_number=?, email=?, course=? WHERE id=?";
                $args = [trim($_POST["name"]), strtoupper(trim($_POST["roll_number"])), trim($_POST["email"]),
                         $_POST["course"], $id];
            }
            $pdo->prepare($sql)->execute($args);

            foreach ($uploads as $item) {
                $stored  = save_upload($id, $item);
                $saved[] = $stored;
                if ($item["category"] !== "certificate") {
                    replace_single_category($id, $item["category"], $stored);   // swap old file out
                }
            }
            $pdo->commit();

            flash("success", "Student details updated successfully.");
            redirect("view.php?id=" . $id);
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            foreach ($saved as $name) @unlink(UPLOAD_DIR . $name);
            error_log($ex->getMessage());
            $errors[] = "Something went wrong while saving. Please try again.";
        }
    }
}

$isEdit = true;
$files  = files_of($id);
require __DIR__ . "/includes/header.php";
?>
<div class="card">
  <h1>Edit Student</h1>
  <?php if ($errors): ?>
    <div class="alert error"><strong>Please fix the following:</strong>
      <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>
  <?php require __DIR__ . "/includes/student_form.php"; ?>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>
