<?php
// Shared bootstrap: session, database (PDO), and helper functions.
// Separate from any other project: uses database "campus_portal_db".

session_start();


const DB_HOST = 'sql103.infinityfree.com';
const DB_NAME = 'if0_43005819_campusportal';
const DB_USER = 'if0_43005819';
const DB_PASS = 'UKt14YJjX84';


const POSITIONS = ['Student', 'Instructor', 'Department Head', 'Registrar', 'Admin'];

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $ex) {
    die('Cannot reach database "' . DB_NAME . '". Run <a href="setup.php">setup.php</a> first.');
}

function h($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function must_login() {
    if (empty($_SESSION['member'])) {
        header('Location: signin.php');
        exit;
    }
}

function must_be(array $positions) {
    must_login();
    if (!in_array($_SESSION['member']['position'], $positions, true)) {
        http_response_code(403);
        die('Access denied. <a href="home.php">Go back</a>');
    }
}

// Web path of a member's photo (images/users/<Username>.JPG) or a default avatar.
function photo_of($username) {
    $rel  = 'images/users/' . $username . '.JPG';
    $full = __DIR__ . '/' . $rel;
    return is_file($full) ? $rel . '?t=' . filemtime($full) : 'images/avatar.svg';
}

// Validates and stores an uploaded photo as images/users/<Username>.JPG
// Returns [true, 'message'] or [false, 'error message'].
function save_photo(array $file, $username) {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return [false, 'No image received (upload error ' . ($file['error'] ?? '?') . ').'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return [false, 'Image is too large (max 5 MB).'];
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'], true)) {
        return [false, 'Invalid file type. Use JPG, PNG or GIF.'];
    }
    if (@getimagesize($file['tmp_name']) === false) {
        return [false, 'That file is not a real image.'];
    }

    $dir = __DIR__ . '/images/users/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    // File is renamed to the Username + ".JPG"
    if (!move_uploaded_file($file['tmp_name'], $dir . $username . '.JPG')) {
        return [false, 'Sorry, there was an error saving your image.'];
    }
    return [true, 'The image was uploaded successfully.'];
}
