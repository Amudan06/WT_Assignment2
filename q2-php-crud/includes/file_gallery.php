<?php
/**
 * file_gallery.php - shows uploaded files with their MySQL metadata.
 * Images are previewed, PDFs get an icon + link.
 * $files = rows from student_files; $showDelete = show a "Delete" button (edit page)
 */
$showDelete = $showDelete ?? ($isEdit ?? false);
$labels = ["profile_image" => "Profile photo", "id_proof" => "ID proof", "certificate" => "Certificate"];
?>
<div class="file-list">
<?php foreach ($files as $f): $isImg = strpos($f["file_type"], "image/") === 0; ?>
  <div class="file-box">
    <?php if ($isImg): ?>
      <a href="<?= e($f["file_path"]) ?>" target="_blank"><img src="<?= e($f["file_path"]) ?>" alt="<?= e($f["original_name"]) ?>"></a>
    <?php else: ?>
      <a href="<?= e($f["file_path"]) ?>" target="_blank"><div class="doc-icon">📄</div></a>
    <?php endif; ?>
    <span class="badge"><?= e($labels[$f["category"]]) ?></span><br>
    <strong><?= e($f["original_name"]) ?></strong><br>
    <span class="muted">
      <?= e($f["file_type"]) ?> &middot; <?= e(human_size((int) $f["file_size"])) ?><br>
      Uploaded <?= e(date("d M Y, h:i A", strtotime($f["uploaded_at"]))) ?>
    </span>
    <?php if ($showDelete): ?>
      <form method="POST" action="delete_file.php" style="margin-top:.5rem"
            onsubmit="return confirm('Delete this file permanently?');">
        <input type="hidden" name="file_id" value="<?= (int) $f["id"] ?>">
        <input type="hidden" name="student_id" value="<?= (int) $f["student_id"] ?>">
        <button class="btn danger small" type="submit">Delete file</button>
      </form>
    <?php endif; ?>
  </div>
<?php endforeach; ?>
</div>
