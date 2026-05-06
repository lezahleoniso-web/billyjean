<?php
session_start();
if (!isset($_SESSION['student_id'])) { header('Location: login.php'); exit; }
require_once 'db.php';

$id = $_SESSION['student_id'];
$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current  = $_POST['current_password']  ?? '';
    $new      = $_POST['new_password']      ?? '';
    $confirm  = $_POST['confirm_password']  ?? '';

    // Fetch current hash
    $stmt = $conn->prepare("SELECT password_hash FROM students WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($hash);
    $stmt->fetch();
    $stmt->close();

    if (!password_verify($current, $hash)) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new) < 8 || !preg_match('/[0-9]/', $new)) {
        $error = "New password must be at least 8 characters and contain a number.";
    } elseif ($new !== $confirm) {
        $error = "New passwords do not match.";
    } else {
        $new_hash = password_hash($new, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE students SET password_hash = ? WHERE id = ?");
        $stmt->bind_param("si", $new_hash, $id);
        $stmt->execute() ? ($success = "Password changed successfully!") : ($error = "Database error.");
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Change Password — UniClub</title>
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
  <h1>Change Password</h1>
  <p>Keep your account secure</p>
</div>

<div class="reg-card" style="max-width:480px;">
  <?php if ($success): ?>
    <div class="flash success">✅ <?= htmlspecialchars($success) ?></div>
  <?php endif; ?>
  <?php if ($error): ?>
    <div class="flash error">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="section-label">🔒 Update Password</div>

    <div class="form-group" style="margin-bottom:1rem;">
      <label>Current Password <span class="req">*</span></label>
      <div class="input-wrap">
        <span class="ico">🔑</span>
        <input type="password" name="current_password" id="cur_pw" placeholder="Your current password" required>
        <button type="button" class="toggle-pw" onclick="toggle('cur_pw')">👁</button>
      </div>
    </div>

    <div class="form-group" style="margin-bottom:1rem;">
      <label>New Password <span class="req">*</span></label>
      <div class="input-wrap">
        <span class="ico">🔒</span>
        <input type="password" name="new_password" id="new_pw" placeholder="Min 8 chars + 1 number" required>
        <button type="button" class="toggle-pw" onclick="toggle('new_pw')">👁</button>
      </div>
    </div>

    <div class="form-group" style="margin-bottom:0.5rem;">
      <label>Confirm New Password <span class="req">*</span></label>
      <div class="input-wrap">
        <span class="ico">🔒</span>
        <input type="password" name="confirm_password" id="conf_pw" placeholder="Repeat new password" required>
        <button type="button" class="toggle-pw" onclick="toggle('conf_pw')">👁</button>
      </div>
    </div>

    <button type="submit" class="btn-submit">Update Password →</button>
  </form>
</div>

<script>
function toggle(id) {
  const el = document.getElementById(id);
  el.type = el.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>