<?php
session_start();
if (isset($_SESSION['admin_id'])) { header('Location: dashboard.php'); exit; }

require_once '../db.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username'] ?? ''), ENT_QUOTES, 'UTF-8');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT id, full_name, password_hash FROM admins WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($aid, $aname, $ahash);
    $stmt->fetch();

    if ($stmt->num_rows === 0 || !password_verify($password, $ahash)) {
        $error = 'Invalid admin credentials.';
    } else {
        $_SESSION['admin_id']   = $aid;
        $_SESSION['admin_name'] = $aname;
        session_regenerate_id(true);
        header('Location: dashboard.php');
        exit;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Login — UniClub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../register.css">
</head>
<body>
<div class="topbar">
  <a href="../index.php" class="topbar-logo">Uni<span>Club</span></a>
  <a href="../index.php" class="topbar-back">← Home</a>
</div>

<div class="hero-text">
  <h1>Admin Portal</h1>
  <p>UniClub Administration Panel</p>
</div>

<div class="reg-card" style="max-width:420px;">
  <?php if ($error): ?>
    <div class="flash error">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST">
    <div class="section-label">🛡️ Admin Login</div>

    <div class="form-group" style="margin-bottom:1rem;">
      <label>Username <span class="req">*</span></label>
      <div class="input-wrap">
        <span class="ico">👤</span>
        <input type="text" name="username" placeholder="admin" required>
      </div>
    </div>

    <div class="form-group" style="margin-bottom:0.5rem;">
      <label>Password <span class="req">*</span></label>
      <div class="input-wrap">
        <span class="ico">🔒</span>
        <input type="password" name="password" id="adm_pw" placeholder="Admin password" required>
        <button type="button" class="toggle-pw" onclick="toggle()">👁</button>
      </div>
    </div>

    <button type="submit" class="btn-submit">Sign In →</button>
  </form>

  <p class="signin-link" style="margin-top:1rem;">
    <a href="../login.php">← Student Login</a>
  </p>
</div>

<script>
function toggle() {
  const el = document.getElementById('adm_pw');
  el.type = el.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>