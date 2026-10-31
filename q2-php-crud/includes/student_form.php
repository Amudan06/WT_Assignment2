<?php
/**
 * student_form.php - the form shared by add.php and edit.php.
 * Variables expected from the parent page:
 *   $student  array with the current values (empty on "add")
 *   $isEdit   true when editing an existing record
 *   $files    existing uploaded files (edit only)
 */
$v = fn($key) => e($student[$key] ?? "");
?>
<form method="POST" enctype="multipart/form-data" novalidate>
  <div class="form-grid">
    <div class="field">
      <label for="name">Full Name <span class="req">*</span></label>
      <input type="text" id="name" name="name" value="<?= $v("name") ?>" required>
    </div>
    <div class="field">
      <label for="roll_number">Roll Number <span class="req">*</span></label>
      <input type="text" id="roll_number" name="roll_number" value="<?= $v("roll_number") ?>" maxlength="15" required>
    </div>
    <div class="field">
      <label for="email">Email <span class="req">*</span></label>
      <input type="email" id="email" name="email" value="<?= $v("email") ?>" required>
    </div>
    <div class="field">
      <label for="course">Course <span class="req">*</span></label>
      <select id="course" name="course" required>
        <option value="">-- Select course --</option>
        <?php foreach (COURSES as $c): ?>
          <option <?= ($student["course"] ?? "") === $c ? "selected" : "" ?>><?= e($c) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="password">Password <?= $isEdit ? "" : "<span class=\"req\">*</span>" ?></label>
      <input type="password" id="password" name="password" autocomplete="new-password">
      <?php if ($isEdit): ?><span class="hint">Leave blank to keep the current password.</span><?php endif; ?>
    </div>
    <div class="field">
      <label for="confirm_password">Confirm Password</label>
      <input type="password" id="confirm_password" name="confirm_password" autocomplete="new-password">
    </div>

    <div class="field">
      <label for="profile_image">Profile Photo <?= $isEdit ? "" : "<span class=\"req\">*</span>" ?></label>
      <input type="file" id="profile_image" name="profile_image" accept=".jpg,.jpeg,.png,.webp">
      <span class="hint"><?= $isEdit ? "Choose a file only to replace the current photo. " : "" ?>JPG / PNG / WEBP, max 2 MB</span>
    </div>
    <div class="field">
      <label for="id_proof">ID Proof <?= $isEdit ? "" : "<span class=\"req\">*</span>" ?></label>
      <input type="file" id="id_proof" name="id_proof" accept=".pdf,.jpg,.jpeg,.png">
      <span class="hint"><?= $isEdit ? "Choose a file only to replace the current ID proof. " : "" ?>PDF / JPG / PNG, max 5 MB</span>
    </div>
    <div class="field full">
      <label for="certificates">Certificates (optional, you can pick several)</label>
      <input type="file" id="certificates" name="certificates[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
      <span class="hint">PDF / JPG / PNG, max 5 MB each</span>
    </div>
  </div>

  <?php if ($isEdit && !empty($files)): ?>
    <h3 style="margin-top:1.5rem">Uploaded files</h3>
    <?php include __DIR__ . "/file_gallery.php"; ?>
  <?php endif; ?>

  <div class="actions">
    <button type="submit" class="btn"><?= $isEdit ? "Save Changes" : "Add Student" ?></button>
    <a class="btn secondary" href="index.php">Cancel</a>
  </div>
</form>
