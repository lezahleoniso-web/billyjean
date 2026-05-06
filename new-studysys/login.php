<?php
session_start();
if (isset($_SESSION['student_id'])) {
    header('Location: dashboard.php');
    exit;
}

require_once 'db.php';

$error = '';
$old_email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $old_email = htmlspecialchars($email);

    if (empty($email) || empty($password)) {
        $error = 'Please enter your email and password.';
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, password_hash, status FROM students WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $full_name, $hash, $status);
        $stmt->fetch();

        if ($stmt->num_rows === 0 || !password_verify($password, $hash)) {
            $error = 'Invalid email or password.';
        } elseif ($status !== 'active') {
            $error = 'Your account is inactive. Please contact the administrator.';
        } else {
            $_SESSION['student_id'] = $id;
            $_SESSION['student_name'] = $full_name;
            session_regenerate_id(true);
            header('Location: dashboard.php');
            exit;
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — UniClub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="register.css">
  <style>
    .login-card {
      max-width: 440px;
      margin: 0 auto;
    }
  </style>
</head>
<body>
<div class="topbar">
  <a href="index.php" class="topbar-logo">Uni<span>Club</span></a>
  <a href="index.php" class="topbar-back">← Home</a>
</div>

<div class="hero-text">
  <h1>Welcome Back</h1>
  <p>Sign in to access your UniClub dashboard</p>
</div>

<div class="reg-card login-card">
  <?php if ($error): ?>
    <div class="flash error">⚠ <?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form method="POST" action="login.php">
    <div class="section-label">🔐 Student Login</div>

    <div class="form-group" style="margin-bottom:1rem;">
      <label for="email">Email Address <span class="req">*</span></label>
      <div class="input-wrap">
        <span class="ico">📧</span>
        <input type="email" id="email" name="email" placeholder="your@email.com"
               value="<?= $old_email ?>" required>
      </div>
    </div>

    <div class="form-group" style="margin-bottom:0.5rem;">
      <label for="password">Password <span class="req">*</span></label>
      <div class="input-wrap">
        <span class="ico">🔒</span>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>
        <button type="button" class="toggle-pw" onclick="togglePw()">👁</button>
      </div>
    </div>

    <button type="submit" class="btn-submit">Sign In →</button>
  </form>

  <p class="signin-link" style="margin-top:1rem;">
    Don't have an account? <a href="register.php">Register here</a>
  </p>
  <p class="signin-link">
    Are you an admin? <a href="admin/login.php">Admin Login</a>
  </p>
</div>

<script>
function togglePw() {
  const pw = document.getElementById('password');
  pw.type = pw.type === 'password' ? 'text' : 'password';
}
</script>
</body>
</html>