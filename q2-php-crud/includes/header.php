<?php
/** header.php - top of every page. Expects $pageTitle to be set before it is included. */
require_once __DIR__ . "/helpers.php";
$flash = pull_flash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? "Student Admin") ?> | Admin Panel</title>
  <link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="topbar">
  <span class="brand">🛠️ College Admin Panel</span>
  <nav>
    <a href="index.php">All Students</a>
    <a href="add.php">+ Add Student</a>
    <a href="../q1-registration-form/index.html">Registration Form</a>
  </nav>
</header>
<main class="container">
<?php if ($flash): ?>
  <div class="alert <?= e($flash["type"]) ?>"><?= e($flash["message"]) ?></div>
<?php endif; ?>
