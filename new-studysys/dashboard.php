<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}
require_once 'db.php';

$id   = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT * FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
$stmt->close();

if (!$student) {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — UniClub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --blue: #0C7BB3; --pink: #F2BAE8; --navy: #0B1F3A;
      --white: #fff; --text: #1e2d3d; --mid: #4a6072; --light: #7a92a5;
      --border: rgba(12,123,179,0.15); --radius: 14px;
    }
    body {
      font-family: 'Nunito', sans-serif;
      min-height: 100vh;
      background: linear-gradient(135deg, #0C7BB3 0%, #7BAED0 35%, #c9a0e0 65%, #F2BAE8 100%);
      background-attachment: fixed;
      color: var(--text);
    }
    /* TOPBAR */
    .topbar {
      height: 72px;
      background-image: url('header.jpg');
      background-repeat: repeat-x;
      background-size: auto 72px;
      border-bottom: 3px solid rgba(255,255,255,0.5);
      box-shadow: 0 3px 18px rgba(0,0,0,0.12);
      display: flex; align-items: center; justify-content: space-between;
      padding: 0 2rem; position: sticky; top: 0; z-index: 100;
    }
    .topbar-logo { font-size:1.4rem; font-weight:800; color:#0a3d60; text-decoration:none; }
    .topbar-logo span { background:#0a3d60; color:#fff; padding:0.1rem 0.5rem; border-radius:6px; }
    .topbar-nav { display:flex; gap:0.75rem; align-items:center; }
    .topbar-nav a {
      color:#0a3d60; text-decoration:none; font-weight:700; font-size:0.85rem;
      background:rgba(255,255,255,0.72); padding:0.4rem 1rem; border-radius:50px;
      border:1.5px solid rgba(10,61,96,0.2); transition:background 0.2s;
    }
    .topbar-nav a:hover { background:rgba(255,255,255,0.95); }
    .topbar-nav a.logout { background:rgba(224,92,122,0.1); border-color:rgba(224,92,122,0.3); color:#c0294a; }
    .topbar-nav a.logout:hover { background:rgba(224,92,122,0.2); }

    /* LAYOUT */
    .container { max-width:960px; margin:0 auto; padding:2rem 1.5rem; }

    /* WELCOME BANNER */
    .welcome-banner {
      background: rgba(255,255,255,0.85);
      border-radius: var(--radius);
      padding: 1.75rem 2rem;
      display: flex; align-items: center; gap: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 4px 24px rgba(12,123,179,0.12);
    }
    .avatar-lg {
      width: 72px; height: 72px; border-radius: 50%;
      object-fit: cover; border: 3px solid var(--blue);
      background: linear-gradient(135deg, #0C7BB3, #F2BAE8);
      flex-shrink: 0;
    }
    .avatar-placeholder {
      width:72px; height:72px; border-radius:50%; flex-shrink:0;
      background:linear-gradient(135deg,#0C7BB3,#F2BAE8);
      display:flex; align-items:center; justify-content:center;
      font-size:2rem; border:3px solid var(--blue);
    }
    .welcome-text h1 { font-family:'Playfair Display',serif; font-size:1.6rem; color:var(--navy); }
    .welcome-text p  { color:var(--mid); font-size:0.9rem; margin-top:0.25rem; }
    .club-chip {
      display:inline-block; margin-top:0.5rem;
      background:rgba(12,123,179,0.1); border:1px solid rgba(12,123,179,0.3);
      color:var(--blue); font-size:0.78rem; font-weight:700;
      padding:0.25rem 0.85rem; border-radius:50px;
    }

    /* CARDS GRID */
    .cards-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:1.25rem; margin-bottom:1.5rem; }
    .card {
      background:rgba(255,255,255,0.88);
      border-radius:var(--radius); padding:1.5rem;
      box-shadow:0 4px 20px rgba(12,123,179,0.1);
      transition:transform 0.2s;
    }
    .card:hover { transform:translateY(-3px); }
    .card-icon { font-size:2rem; margin-bottom:0.75rem; }
    .card h3 { font-size:1rem; font-weight:800; color:var(--navy); margin-bottom:0.4rem; }
    .card p  { font-size:0.84rem; color:var(--mid); line-height:1.5; margin-bottom:1rem; }
    .card-btn {
      display:inline-block; padding:0.5rem 1.25rem;
      background:linear-gradient(135deg,#0C7BB3,#7BAED0);
      color:#fff; border-radius:50px; font-size:0.82rem; font-weight:700;
      text-decoration:none; transition:opacity 0.2s;
    }
    .card-btn:hover { opacity:0.85; }

    /* INFO TABLE */
    .info-card {
      background:rgba(255,255,255,0.88);
      border-radius:var(--radius); padding:1.75rem 2rem;
      box-shadow:0 4px 20px rgba(12,123,179,0.1);
    }
    .info-card h2 { font-family:'Playfair Display',serif; font-size:1.25rem; color:var(--navy); margin-bottom:1.25rem; }
    .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:0.75rem 2rem; }
    .info-item label { font-size:0.72rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--light); }
    .info-item p     { font-size:0.92rem; font-weight:600; color:var(--text); margin-top:0.15rem; }
    .status-chip {
      display:inline-block; padding:0.2rem 0.65rem; border-radius:50px;
      font-size:0.75rem; font-weight:700;
      background:rgba(46,204,142,0.12); color:#1a9e6a; border:1px solid rgba(46,204,142,0.4);
    }

    @media(max-width:600px){
      .welcome-banner { flex-direction:column; text-align:center; }
      .info-grid { grid-template-columns:1fr; }
      .topbar-nav a span { display:none; }
    }
  </style>
</head>
<body>

<div class="topbar">
  <a href="index.php" class="topbar-logo">Uni<span>Club</span></a>
  <div class="topbar-nav">
    <a href="edit_profile.php">✏️ <span>Edit Profile</span></a>
    <a href="change_password.php">🔒 <span>Password</span></a>
    <a href="logout.php" class="logout">🚪 <span>Logout</span></a>
  </div>
</div>

<div class="container">
  <!-- Welcome -->
  <div class="welcome-banner">
    <?php if ($student['profile_photo'] !== 'uploads/default_avatar.png' && file_exists($student['profile_photo'])): ?>
      <img src="<?= htmlspecialchars($student['profile_photo']) ?>" class="avatar-lg" alt="Profile Photo">
    <?php else: ?>
      <div class="avatar-placeholder">👤</div>
    <?php endif; ?>
    <div class="welcome-text">
      <h1>Hello, <?= htmlspecialchars($student['first_name']) ?>! 👋</h1>
      <p>Welcome back to your UniClub dashboard.</p>
      <span class="club-chip">✦ <?= htmlspecialchars($student['club']) ?></span>
    </div>
  </div>

  <!-- Quick Actions -->
  <div class="cards-grid">
    <div class="card">
      <div class="card-icon">✏️</div>
      <h3>Edit Profile</h3>
      <p>Update your personal information, course, year level, and club membership.</p>
      <a href="edit_profile.php" class="card-btn">Edit Now</a>
    </div>
    <div class="card">
      <div class="card-icon">🔒</div>
      <h3>Change Password</h3>
      <p>Keep your account secure by updating your password regularly.</p>
      <a href="change_password.php" class="card-btn">Change</a>
    </div>
    <div class="card">
      <div class="card-icon">🏛️</div>
      <h3>Your Club</h3>
      <p>You are a member of <strong><?= htmlspecialchars($student['club']) ?></strong>. Stay active and engaged!</p>
      <a href="index.php" class="card-btn">Learn More</a>
    </div>
  </div>

  <!-- Personal Info -->
  <div class="info-card">
    <h2>📋 Personal Information</h2>
    <div class="info-grid">
      <div class="info-item">
        <label>Student ID</label>
        <p><?= htmlspecialchars($student['student_id']) ?></p>
      </div>
      <div class="info-item">
        <label>Full Name</label>
        <p><?= htmlspecialchars($student['full_name']) ?></p>
      </div>
      <div class="info-item">
        <label>Email Address</label>
        <p><?= htmlspecialchars($student['email']) ?></p>
      </div>
      <div class="info-item">
        <label>Course</label>
        <p><?= htmlspecialchars($student['course']) ?></p>
      </div>
      <div class="info-item">
        <label>Year Level</label>
        <p><?= htmlspecialchars($student['year_level']) ?></p>
      </div>
      <div class="info-item">
        <label>Club</label>
        <p><?= htmlspecialchars($student['club']) ?></p>
      </div>
      <div class="info-item">
        <label>Account Status</label>
        <p><span class="status-chip"><?= ucfirst($student['status']) ?></span></p>
      </div>
      <div class="info-item">
        <label>Member Since</label>
        <p><?= date('F j, Y', strtotime($student['created_at'])) ?></p>
      </div>
    </div>
  </div>
</div>

</body>
</html>