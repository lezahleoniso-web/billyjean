<?php
/**
 * process.php — UniClub Registration Backend
 * Handles form submission, validation, photo upload, and DB insertion.
 */

require_once 'db.php';

// ── Only accept POST ──
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

// ── Sanitize helper ──
function clean($val) {
    return htmlspecialchars(trim($val), ENT_QUOTES, 'UTF-8');
}

// ── Collect & sanitize fields ──
$first_name  = clean($_POST['first_name']  ?? '');
$last_name   = clean($_POST['last_name']   ?? '');
$student_id  = clean($_POST['student_id']  ?? '');
$email       = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$course      = clean($_POST['course']      ?? '');
$year_level  = clean($_POST['year_level']  ?? '');
$club        = clean($_POST['club']        ?? '');
$password    = $_POST['password']          ?? '';
$confirm_pw  = $_POST['confirm_password']  ?? '';
$full_name   = $first_name . ' ' . $last_name;

$errors = [];

// ── Server-side Validation ──
if (strlen($first_name) < 2)            $errors[] = "First name is too short.";
if (strlen($last_name)  < 2)            $errors[] = "Last name is too short.";
if (!preg_match('/^[\w\-]{4,20}$/', $student_id)) $errors[] = "Invalid student ID format.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL))   $errors[] = "Invalid email address.";
if (empty($course))                     $errors[] = "Please select your course.";
if (empty($year_level))                 $errors[] = "Please select your year level.";
if (empty($club))                       $errors[] = "Please select a club.";
if (strlen($password) < 8 || !preg_match('/[0-9]/', $password))
                                        $errors[] = "Password must be 8+ characters with at least one number.";
if ($password !== $confirm_pw)          $errors[] = "Passwords do not match.";

// ── Check for duplicate email or student ID ──
if (empty($errors)) {
    $stmt = $conn->prepare("SELECT id FROM students WHERE email = ? OR student_id = ?");
    $stmt->bind_param("ss", $email, $student_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $errors[] = "An account with this email or student ID already exists.";
    }
    $stmt->close();
}

// ── Profile Photo Upload ──
$photo_path = 'uploads/default_avatar.png'; // default

if (empty($errors) && isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
    $file     = $_FILES['profile_photo'];
    $allowed  = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize  = 3 * 1024 * 1024; // 3 MB

    if (!in_array($file['type'], $allowed)) {
        $errors[] = "Profile photo must be JPG, PNG, GIF, or WebP.";
    } elseif ($file['size'] > $maxSize) {
        $errors[] = "Profile photo must be smaller than 3 MB.";
    } else {
        $ext       = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename  = 'student_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
        $dest      = __DIR__ . '/uploads/' . $filename;
        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $photo_path = 'uploads/' . $filename;
        } else {
            $errors[] = "Failed to save profile photo. Check folder permissions.";
        }
    }
}

// ── If errors, redirect back ──
if (!empty($errors)) {
    session_start();
    $_SESSION['reg_error']  = implode('<br>', $errors);
    $_SESSION['reg_fields'] = $_POST; // preserve form values
    header('Location: register.php');
    exit;
}

// ── Hash password ──
$hashed = password_hash($password, PASSWORD_BCRYPT);

// ── Insert into DB ──
$stmt = $conn->prepare(
    "INSERT INTO students
     (student_id, first_name, last_name, full_name, email, course, year_level, club, password_hash, profile_photo, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
);
$stmt->bind_param(
    "ssssssssss",
    $student_id, $first_name, $last_name, $full_name,
    $email, $course, $year_level, $club,
    $hashed, $photo_path
);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    // Redirect to success page
    header('Location: success.php?name=' . urlencode($first_name) . '&club=' . urlencode($club));
    exit;
} else {
    // DB error
    session_start();
    $_SESSION['reg_error'] = "Database error: " . $stmt->error;
    header('Location: register.php');
    exit;
}