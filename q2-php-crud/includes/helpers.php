<?php
/**
 * helpers.php - small reusable functions used by every page.
 *   1. output escaping + flash messages
 *   2. server-side validation of the text fields
 *   3. file upload: validate -> save -> store metadata -> delete
 */
require_once __DIR__ . "/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();               // sessions are used for flash messages
}

/* ------------------------------------------------------------------ */
/* 1. Output escaping and flash messages                              */
/* ------------------------------------------------------------------ */

/** Escape text before printing it in HTML. Prevents XSS attacks. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

/** Store a one-time message that is shown after a redirect. */
function flash(string $type, string $message): void
{
    $_SESSION["flash"] = ["type" => $type, "message" => $message];
}

/** Read and clear the flash message (shows only once). */
function pull_flash(): ?array
{
    $f = $_SESSION["flash"] ?? null;
    unset($_SESSION["flash"]);
    return $f;
}

function redirect(string $url): void
{
    header("Location: " . $url);
    exit;
}

/** "AB" initials used when a student has no profile photo. */
function initials(string $name): string
{
    $parts = preg_split("/\s+/", trim($name));
    $first = mb_substr($parts[0] ?? "?", 0, 1);
    $last  = count($parts) > 1 ? mb_substr(end($parts), 0, 1) : "";
    return mb_strtoupper($first . $last);
}

/** 1536000 -> "1.5 MB" */
function human_size(int $bytes): string
{
    if ($bytes >= 1048576) return round($bytes / 1048576, 1) . " MB";
    if ($bytes >= 1024)    return round($bytes / 1024, 1) . " KB";
    return $bytes . " B";
}

/* ------------------------------------------------------------------ */
/* 2. Server-side validation of the text fields                       */
/* ------------------------------------------------------------------ */

/**
 * Validates name, roll number, email, course (and password when given).
 * Returns an array of error messages - empty array means "all good".
 */
function validate_student(array $in, bool $passwordRequired, ?int $ignoreId = null): array
{
    $errors = [];

    $name = trim($in["name"] ?? "");
    if (!preg_match("/^[A-Za-z][A-Za-z .']{2,99}$/", $name)) {
        $errors[] = "Name must contain letters only (minimum 3 characters).";
    }

    $roll = trim($in["roll_number"] ?? "");
    if (!preg_match("/^[A-Za-z0-9]{6,15}$/", $roll)) {
        $errors[] = "Roll number must be 6-15 letters or digits.";
    }

    $email = trim($in["email"] ?? "");
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 120) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!in_array($in["course"] ?? "", COURSES, true)) {
        $errors[] = "Please choose a course from the list.";
    }

    $pw = $in["password"] ?? "";
    if ($passwordRequired || $pw !== "") {
        if (strlen($pw) < 8 || !preg_match("/[a-z]/", $pw) || !preg_match("/[A-Z]/", $pw)
            || !preg_match("/[0-9]/", $pw) || !preg_match("/[^A-Za-z0-9]/", $pw)) {
            $errors[] = "Password needs 8+ characters with upper, lower, number and symbol.";
        }
        if ($pw !== ($in["confirm_password"] ?? $pw)) {
            $errors[] = "Password and confirm password do not match.";
        }
    }

    // Duplicate roll number / email? (skip the record being edited)
    if (!$errors) {
        $stmt = db()->prepare(
            "SELECT roll_number, email FROM students
             WHERE (roll_number = ? OR email = ?) AND id <> ?"
        );
        $stmt->execute([$roll, $email, $ignoreId ?? 0]);
        foreach ($stmt->fetchAll() as $row) {
            if (strcasecmp($row["roll_number"], $roll) === 0) $errors[] = "Roll number $roll is already registered.";
            if (strcasecmp($row["email"], $email) === 0)      $errors[] = "Email $email is already registered.";
        }
    }
    return $errors;
}

/* ------------------------------------------------------------------ */
/* 3. File upload handling                                            */
/* ------------------------------------------------------------------ */

/**
 * Turns the awkward $_FILES layout for multiple files
 * (name[0], name[1], ...) into a simple list of single-file arrays.
 */
function normalize_files(?array $f): array
{
    if (!$f || !isset($f["name"])) return [];
    if (!is_array($f["name"])) return [$f];
    $list = [];
    foreach ($f["name"] as $i => $unused) {
        $list[] = [
            "name"     => $f["name"][$i],
            "type"     => $f["type"][$i],
            "tmp_name" => $f["tmp_name"][$i],
            "error"    => $f["error"][$i],
            "size"     => $f["size"][$i],
        ];
    }
    return $list;
}

/**
 * Checks one uploaded file against the rules for its category.
 * Returns ["error" => "..."]  or  ["mime" => "...", "ext" => "..."].
 * A file input that was left empty returns ["skip" => true].
 */
function check_upload(array $file, string $category): array
{
    $rule = FILE_RULES[$category];
    $label = $rule["label"];

    if ($file["error"] === UPLOAD_ERR_NO_FILE) {
        return ["skip" => true];
    }
    // PHP itself rejects files bigger than upload_max_filesize
    if ($file["error"] === UPLOAD_ERR_INI_SIZE || $file["error"] === UPLOAD_ERR_FORM_SIZE) {
        return ["error" => "$label is too large (limit {$rule['max_mb']} MB)."];
    }
    if ($file["error"] !== UPLOAD_ERR_OK || !is_uploaded_file($file["tmp_name"])) {
        return ["error" => "$label could not be uploaded. Please try again."];
    }
    // our own size limit
    if ($file["size"] > $rule["max_mb"] * 1024 * 1024) {
        return ["error" => "$label must be smaller than {$rule['max_mb']} MB."];
    }
    // detect the real type from the file content, not from the file name
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file["tmp_name"]);
    if (!isset($rule["mimes"][$mime])) {
        $allowed = strtoupper(implode(", ", array_values($rule["mimes"])));
        return ["error" => "$label must be one of: $allowed."];
    }
    return ["mime" => $mime, "ext" => $rule["mimes"][$mime]];
}

/**
 * Validates every upload field that was submitted and returns
 * [ list of error messages, list of files ready to be saved ].
 * Nothing is written to disk yet - we only save after ALL checks pass.
 */
function validate_all_uploads(bool $requireProfileAndId = false): array
{
    $errors = [];
    $ready  = [];
    $fields = [
        "profile_image" => normalize_files($_FILES["profile_image"] ?? null),
        "id_proof"      => normalize_files($_FILES["id_proof"] ?? null),
        "certificates"  => normalize_files($_FILES["certificates"] ?? null),
    ];
    foreach ($fields as $input => $files) {
        $category = $input === "certificates" ? "certificate" : $input;
        $got = 0;
        foreach ($files as $file) {
            $r = check_upload($file, $category);
            if (isset($r["skip"])) continue;
            $got++;
            if (isset($r["error"])) { $errors[] = $r["error"]; continue; }
            $ready[] = ["file" => $file, "category" => $category, "mime" => $r["mime"], "ext" => $r["ext"]];
        }
        if ($requireProfileAndId && $got === 0 && $category !== "certificate") {
            $errors[] = FILE_RULES[$category]["label"] . " is required.";
        }
    }
    return [$errors, $ready];
}

/**
 * Moves one validated file into /uploads and records its metadata in MySQL.
 * Returns the stored file name so the caller can undo it if needed.
 */
function save_upload(int $studentId, array $item): string
{
    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

    // Build a unique name. random_bytes makes a clash practically impossible,
    // and the file_exists loop guarantees an existing file is NEVER overwritten.
    do {
        $stored = sprintf("%s_%d_%s.%s", $item["category"], $studentId, bin2hex(random_bytes(6)), $item["ext"]);
        $target = UPLOAD_DIR . $stored;
    } while (file_exists($target));

    if (!move_uploaded_file($item["file"]["tmp_name"], $target)) {
        throw new RuntimeException("Could not save " . $item["file"]["name"]);
    }

    $stmt = db()->prepare(
        "INSERT INTO student_files
           (student_id, category, original_name, stored_name, file_path, file_type, file_size)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([
        $studentId,
        $item["category"],
        mb_substr(basename($item["file"]["name"]), 0, 255),   // basename strips any ../ tricks
        $stored,
        UPLOAD_URL . $stored,
        $item["mime"],
        (int) $item["file"]["size"],
    ]);
    return $stored;
}

/** Deletes one file row AND the physical file. */
function delete_file_record(array $row): void
{
    $path = UPLOAD_DIR . basename($row["stored_name"]);   // basename = safe path
    if (is_file($path)) unlink($path);
    db()->prepare("DELETE FROM student_files WHERE id = ?")->execute([$row["id"]]);
}

/** All files of a student, newest first. */
function files_of(int $studentId): array
{
    $stmt = db()->prepare("SELECT * FROM student_files WHERE student_id = ? ORDER BY uploaded_at DESC, id DESC");
    $stmt->execute([$studentId]);
    return $stmt->fetchAll();
}

/** profile_image / id_proof allow only ONE file: remove older ones when a new one arrives. */
function replace_single_category(int $studentId, string $category, string $keepStoredName): void
{
    $stmt = db()->prepare("SELECT * FROM student_files WHERE student_id = ? AND category = ? AND stored_name <> ?");
    $stmt->execute([$studentId, $category, $keepStoredName]);
    foreach ($stmt->fetchAll() as $old) delete_file_record($old);
}
