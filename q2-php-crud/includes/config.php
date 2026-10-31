<?php
/**
 * config.php - one place for every setting.
 * Change the DB credentials here to match your own MySQL / XAMPP setup.
 */
const DB_HOST = "127.0.0.1";
const DB_NAME = "student_portal";
const DB_USER = "root";
const DB_PASS = "";            // XAMPP default is an empty password

// Folder where uploaded files are saved (shared with the Servlet portal in Q3)
const UPLOAD_DIR = __DIR__ . "/../uploads/";
const UPLOAD_URL = "uploads/";  // same folder as seen from the browser

// What each upload category accepts. Types are checked against the REAL file
// content (finfo), not the file name, so renaming virus.exe to virus.jpg fails.
const FILE_RULES = [
    "profile_image" => [
        "label"   => "Profile photo",
        "max_mb"  => 2,
        "mimes"   => ["image/jpeg" => "jpg", "image/png" => "png", "image/webp" => "webp"],
    ],
    "id_proof" => [
        "label"   => "ID proof",
        "max_mb"  => 5,
        "mimes"   => ["application/pdf" => "pdf", "image/jpeg" => "jpg", "image/png" => "png"],
    ],
    "certificate" => [
        "label"   => "Certificate",
        "max_mb"  => 5,
        "mimes"   => ["application/pdf" => "pdf", "image/jpeg" => "jpg", "image/png" => "png"],
    ],
];

const COURSES = [
    "B.E. Computer Science and Engineering",
    "B.E. Electronics and Communication",
    "B.E. Mechanical Engineering",
    "B.Tech Artificial Intelligence and Data Science",
    "B.Tech Information Technology",
];
