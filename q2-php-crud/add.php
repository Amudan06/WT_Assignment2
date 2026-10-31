<?php
/**
 * add.php - CREATE: shows the form (GET) and saves the student + files (POST).
 * The Q1 registration form also posts here.
 */
$pageTitle = "Add Student";
require __DIR__ . "/includes/helpers.php";

$errors  = [];
$student = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student = $_POST;

    // 1) validate text fields  2) validate files  - nothing is saved if anything fails
    $errors = validate_student($_POST, true);
    [$fileErrors, $uploads] = validate_all_uploads(true);
    $errors = array_merge($errors, $fileErrors);

    if (!$errors) {
        $pdo = db();
        $saved = [];                       // remember saved files so we can undo on failure
        try {
            $pdo->beginTransaction();      // student row + file rows succeed or fail together

            $stmt = $pdo->prepare(
                "INSERT INTO students (name, roll_number, email, course, password_hash) VALUES (?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                trim($_POST["name"]),
                strtoupper(trim($_POST["roll_number"])),
                trim($_POST["email"]),
                $_POST["course"],
                password_hash($_POST["password"], PASSWORD_DEFAULT),   // never store plain passwords
            ]);
            $studentId = (int) $pdo->lastInsertId();

            foreach ($uploads as $item) {
                $saved[] = save_upload($studentId, $item);
            }
            $pdo->commit();

            flash("success", "Student " . trim($_POST["name"]) . " was added successfully.");
            redirect("view.php?id=" . $studentId);
        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            foreach ($saved as $name) @unlink(UPLOAD_DIR . $name);   // remove orphan files
            error_log($ex->getMessage());
            $errors[] = "Something went wrong while saving. Please try again.";
        }
    }
}

$isEdit = false;
$files  = [];
require __DIR__ . "/includes/header.php";
?>
<div class="card">
  <h1>Add New Student</h1>
  <?php if ($errors): ?>
    <div class="alert error"><strong>Please fix the following:</strong>
      <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>
  <?php require __DIR__ . "/includes/student_form.php"; ?>
</div>
<?php require __DIR__ . "/includes/footer.php"; ?>
