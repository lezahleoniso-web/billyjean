<?php
session_start();
if (!isset($_SESSION['student_id'])) { header('Location: login.php'); exit; }
require_once 'db.php';

$id = $_SESSION['student_id'];
$success = $error = '';

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
    $last_name  = htmlspecialchars(trim($_POST['last_name']  ?? ''), ENT_QUOTES, 'UTF-8');
    $course     = htmlspecialchars(trim($_POST['course']     ?? ''), ENT_QUOTES, 'UTF-8');
    $year_level = htmlspecialchars(trim($_POST['year_level'] ?? ''), ENT_QUOTES, 'UTF-8');
    $club       = htmlspecialchars(trim($_POST['club']       ?? ''), ENT_QUOTES, 'UTF-8');
    $full_name  = $first_name . ' ' . $last_name;
    $photo_path = $student['profile_photo'];

    if (strlen($first_name) < 2) $error = "First name is too short.";
    elseif (strlen($last_name) < 2) $error = "Last name is too short.";
    elseif (empty($course)) $error = "Please select your course.";
    elseif (empty($year_level)) $error = "Please select your year level.";
    elseif (empty($club)) $error = "Please select a club.";

    // Photo upload
    if (empty($error) && isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file    = $_FILES['profile_photo'];
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (!in_array($file['type'], $allowed)) {
            $error = "Photo must be JPG, PNG, GIF, or WebP.";
        } elseif ($file['size'] > 3 * 1024 * 1024) {
            $error = "Photo must be smaller than 3 MB.";
        } else {
            $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = 'student_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
            $dest     = __DIR__ . '/uploads/' . $filename;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $photo_path = 'uploads/' . $filename;
            } else {
                $error = "Failed to save photo. Check folder permissions.";
            }
        }
    }

    if (empty($error)) {
        $stmt = $conn->prepare("UPDATE students SET first_name=?, last_name=?, full_name=?, course=?, year_level=?, club=?, profile_photo=? WHERE id=?");
        $stmt->bind_param("sssssssi", $first_name, $last_name, $full_name, $course, $year_level, $club, $photo_path, $id);
        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            $_SESSION['student_name'] = $full_name;
            // Refresh student data
            $stmt2 = $conn->prepare("SELECT * FROM students WHERE id = ?");
            $stmt2->bind_param("i", $id);
            $stmt2->execute();
            $student = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
        } else {
            $error = "Database error: " . $stmt->error;
        }
        $stmt->close();
    }
}

$clubs = ['Computer Science Society','Fine Arts & Design Club','Debate & Oratory Society','Environmental Advocates','Performing Arts Guild','Campus Journalism Club'];
$courses = ['BS Computer Science','BS Information Technology','BS Computer Engineering','BS Electrical Engineering','BS Civil Engineering','BS Architecture','BS Nursing','BS Education','BS Business Administration','BS Accountancy'];
$years = ['1st Year','2nd Year','3rd Year','4th Year','5th Year'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Edit Profile — UniClub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="register.css">
</head>
<body>
<div class="topbar">
  <a href="dashboard.php" class="topbar-logo">Uni<span>Club</span></a>
  <a href="dashboard.php" class="topbar-back">← Dashboard</a>
</div>

<div class="hero-text">
  <h1>Edit Profile</h1>
  <p>Update your personal information</p>
</div>

<div class="reg-card">
  <?php if ($success): ?>
    <div class="flash success">✅ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="flash error">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" enctype="multipart/form-data">
    <!-- Photo -->
    <div class="photo-section" onclick="document.getElementById('profile_photo').click()">
      <div class="photo-avatar" id="avatarCircle">
        <?php if ($student['profile_photo'] !== 'uploads/default_avatar.png'): ?>
          <img id="photoPreview" src="<?= htmlspecialchars($student['profile_photo']) ?>" style="display:block;width:100%;height:100%;object-fit:cover;">
        <?php else: ?>
          <span id="avatarEmoji">👤</span>
          <img id="photoPreview" style="display:none;width:100%;height:100%;object-fit:cover;">
        <?php endif; ?>
      </div>
      <div class="photo-info">
        <strong>Profile Photo</strong>
        <span>Click to change • JPG, PNG, WebP • Max 3MB</span>
      </div>
      <button type="button" class="photo-btn">Choose Photo</button>
      <input type="file" id="profile_photo" name="profile_photo" accept="image/*" hidden
             onchange="previewPhoto(this)">
    </div>

    <div class="section-label">👤 Personal Details</div>
    <div class="form-grid">
      <div class="form-group">
        <label>First Name <span class="req">*</span></label>
        <div class="input-wrap">
          <span class="ico">👤</span>
          <input type="text" name="first_name" value="<?= htmlspecialchars($student['first_name']) ?>" required>
        </div>
      </div>
      <div class="form-group">
        <label>Last Name <span class="req">*</span></label>
        <div class="input-wrap">
          <span class="ico">👤</span>
          <input type="text" name="last_name" value="<?= htmlspecialchars($student['last_name']) ?>" required>
        </div>
      </div>
    </div>

    <div class="section-label">🎓 Academic Info</div>
    <div class="form-grid">
      <div class="form-group">
        <label>Course <span class="req">*</span></label>
        <div class="select-wrap">
          <select name="course" required>
            <option value="">Select course...</option>
            <?php foreach ($courses as $c): ?>
              <option value="<?= $c ?>" <?= $student['course'] === $c ? 'selected' : '' ?>><?= $c ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label>Year Level <span class="req">*</span></label>
        <div class="select-wrap">
          <select name="year_level" required>
            <option value="">Select year...</option>
            <?php foreach ($years as $y): ?>
              <option value="<?= $y ?>" <?= $student['year_level'] === $y ? 'selected' : '' ?>><?= $y ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
    </div>

    <div class="section-label">🏛️ Club Membership</div>
    <div class="club-picker">
      <?php
      $clubEmojis = ['Computer Science Society'=>'💻','Fine Arts & Design Club'=>'🎨','Debate & Oratory Society'=>'📢','Environmental Advocates'=>'🌱','Performing Arts Guild'=>'🎭','Campus Journalism Club'=>'📰'];
      foreach ($clubs as $cl):
        $checked = $student['club'] === $cl ? 'checked' : '';
        $slug = strtolower(preg_replace('/[^a-z0-9]/i','',$cl));
      ?>
      <input type="radio" class="club-opt-input" name="club" id="club_<?= $slug ?>" value="<?= $cl ?>" <?= $checked ?> required>
      <label class="club-opt-label" for="club_<?= $slug ?>">
        <span class="club-emoji"><?= $clubEmojis[$cl] ?></span>
        <?= $cl ?>
      </label>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="btn-submit" style="margin-top:1.5rem;">Save Changes ✓</button>
  </form>
</div>

<script>
function previewPhoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('photoPreview').src = e.target.result;
      document.getElementById('photoPreview').style.display = 'block';
      const emoji = document.getElementById('avatarEmoji');
      if (emoji) emoji.style.display = 'none';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
</body>
</html>