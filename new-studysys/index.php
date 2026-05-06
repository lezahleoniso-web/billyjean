<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>UniClub — Student Club Portal</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="index.css">
</head>
<body>

<!-- NAV -->
<nav>
  <div class="nav-logo">Uni<span>Club</span></div>
  <ul class="nav-links"></ul>
  <div style="display:flex;gap:0.75rem;">
    <a href="login.php" class="nav-cta" style="background:transparent;border:2px solid #0a3d60;color:#0a3d60;">Login</a>
    <a href="register.php" class="nav-cta">Join Now</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-badge">Student Club Portal — Academic Year 2025–2026</div>
  <h1>Where Students<br><em>Connect & Grow</em></h1>
  <p>
    <i>Discover your passion, join a community, and build lasting connections.
    UniClub is the official gateway to all student organizations at our university.</i>
  </p>
  <div class="hero-btns">
    <a href="register.php" class="btn-primary">Register Now</a>
    <!--a href="#clubs" class="btn-secondary">Explore Clubs ↓</a-->
  </div>
</section>




<section id="clubs" class="section">
  <div class="section-label">Our Organizations</div>
  <div class="divider"></div>
  <h2 class="section-title">Find Your Community</h2>
  <p class="section-sub">From technology to the arts — there's a place for every student.</p>

  <div class="clubs-grid">
    <div class="club-card">
      <div class="club-icon">💻</div>
      <h3>Computer Science Society</h3>
      <p>Hackathons, coding bootcamps, and industry mentorship for tech-minded students.</p>
      <span class="club-members">★ 280 Members</span>
    </div>
    <div class="club-card">
      <div class="club-icon">🎨</div>
      <h3>Fine Arts & Design Club</h3>
      <p>Exhibitions, workshops, and collaborative projects celebrating visual creativity.</p>
      <span class="club-members">★ 145 Members</span>
    </div>
    <div class="club-card">
      <div class="club-icon">📢</div>
      <h3>Debate & Oratory Society</h3>
      <p>Sharpen your critical thinking and public speaking in regional competitions.</p>
      <span class="club-members">★ 90 Members</span>
    </div>
    <div class="club-card">
      <div class="club-icon">🌱</div>
      <h3>Environmental Advocates</h3>
      <p>Sustainability campaigns, clean-up drives, and green campus initiatives.</p>
      <span class="club-members">★ 165 Members</span>
    </div>
    <div class="club-card">
      <div class="club-icon">🎭</div>
      <h3>Performing Arts Guild</h3>
      <p>Theater productions, dance showcases, and musical performances all year round.</p>
      <span class="club-members">★ 200 Members</span>
    </div>
    <div class="club-card">
      <div class="club-icon">📰</div>
      <h3>Campus Journalism Club</h3>
      <p>Student newspaper, photography, and digital media storytelling for the campus.</p>
      <span class="club-members">★ 75 Members</span>
    </div>
  </div> 
</section>

<!-- CTA BANNER 
<div class="cta-banner" id="about">
  <div class="divider"></div>
  <h2>Ready to Be Part of Something Bigger?</h2>
  <p>Registration is free and open to all enrolled students. Join today.</p>
  <a href="register.php" class="btn-primary">Create Your Account →</a>
</div>-->

<!-- FOOTER -->
<footer>
  <div class="footer-logo">UniClub</div>
  <div>© 2025 Cebu Technological University. All rights reserved.</div>
  <div>Built with PHP & MySQL</div>
</footer>

</body>
</html>
