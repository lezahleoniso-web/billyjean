<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Welcome — UniClub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="success.css">
</head>
<body>

<?php
$name = htmlspecialchars($_GET['name'] ?? 'Student');
$club = htmlspecialchars($_GET['club'] ?? 'your club');
?>

<div class="card">
  <div class="checkmark">✓</div>
  <h1>Welcome, <?= $name ?>!</h1>
  <p class="sub">Your registration was successful. You're now a member of the UniClub portal.</p>
  <div class="club-badge">✦ <?= $club ?></div>
  <hr class="divider"/>
  <div class="next-steps">
    <h3>What's Next</h3>
    <div class="step"><div class="step-num">1</div>Check your email for a welcome message from your club.</div>
    <div class="step"><div class="step-num">2</div>Attend the next club orientation or general assembly.</div>
    <div class="step"><div class="step-num">3</div>Connect with fellow members and explore upcoming events.</div>
  </div>
  <a href="index.php" class="btn">← Back to Home</a>
</div>

</body>
</html>