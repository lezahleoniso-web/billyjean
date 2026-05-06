<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
require_once '../db.php';

// Search
$search = htmlspecialchars(trim($_GET['search'] ?? ''), ENT_QUOTES, 'UTF-8');
$where  = '';
$params = [];
$types  = '';

if (!empty($search)) {
    $where    = "WHERE s.full_name LIKE ? OR s.email LIKE ? OR s.student_id LIKE ? OR s.club LIKE ?";
    $like     = "%$search%";
    $params   = [$like, $like, $like, $like];
    $types    = 'ssss';
}

// Count totals for dashboard
$total    = $conn->query("SELECT COUNT(*) FROM students")->fetch_row()[0];
$active   = $conn->query("SELECT COUNT(*) FROM students WHERE status='active'")->fetch_row()[0];
$clubs_q  = $conn->query("SELECT COUNT(DISTINCT club) FROM students")->fetch_row()[0];

// Fetch students
$sql  = "SELECT s.id, s.student_id, s.full_name, s.email, s.course, s.year_level, s.club, s.status, s.created_at FROM students s $where ORDER BY s.created_at DESC";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$students = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard — UniClub</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700;800&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --blue:#0C7BB3; --navy:#0B1F3A; --white:#fff;
      --text:#1e2d3d; --mid:#4a6072; --light:#7a92a5;
      --border:rgba(12,123,179,0.15); --radius:12px;
      --red:#e05c7a; --green:#2ecc8e;
    }
    body {
      font-family:'Nunito',sans-serif;
      background:linear-gradient(135deg,#0C7BB3 0%,#7BAED0 35%,#c9a0e0 65%,#F2BAE8 100%);
      background-attachment:fixed; min-height:100vh; color:var(--text);
    }
    /* TOPBAR */
    .topbar {
      height:72px; background-image:url('../header.jpg');
      background-repeat:repeat-x; background-size:auto 72px;
      border-bottom:3px solid rgba(255,255,255,0.5);
      box-shadow:0 3px 18px rgba(0,0,0,0.12);
      display:flex; align-items:center; justify-content:space-between;
      padding:0 2rem; position:sticky; top:0; z-index:100;
    }
    .topbar-logo { font-size:1.4rem; font-weight:800; color:#0a3d60; text-decoration:none; }
    .topbar-logo span { background:#0a3d60; color:#fff; padding:0.1rem 0.5rem; border-radius:6px; }
    .topbar-right { display:flex; align-items:center; gap:0.75rem; }
    .admin-badge {
      font-size:0.78rem; font-weight:700; color:#0a3d60;
      background:rgba(255,255,255,0.8); padding:0.3rem 0.8rem; border-radius:50px;
    }
    .btn-logout {
      font-size:0.82rem; font-weight:700; color:#c0294a;
      background:rgba(224,92,122,0.1); border:1.5px solid rgba(224,92,122,0.3);
      padding:0.35rem 0.9rem; border-radius:50px; text-decoration:none;
      transition:background 0.2s;
    }
    .btn-logout:hover { background:rgba(224,92,122,0.2); }

    /* LAYOUT */
    .container { max-width:1100px; margin:0 auto; padding:2rem 1.5rem; }

    /* STATS */
    .stats { display:grid; grid-template-columns:repeat(3,1fr); gap:1rem; margin-bottom:1.5rem; }
    .stat-card {
      background:rgba(255,255,255,0.88); border-radius:var(--radius);
      padding:1.25rem 1.5rem; display:flex; align-items:center; gap:1rem;
      box-shadow:0 4px 20px rgba(12,123,179,0.1);
    }
    .stat-icon { font-size:2rem; }
    .stat-num { font-family:'Playfair Display',serif; font-size:2rem; font-weight:800; color:var(--navy); line-height:1; }
    .stat-label { font-size:0.78rem; color:var(--mid); font-weight:600; margin-top:0.2rem; }

    /* TABLE CARD */
    .table-card {
      background:rgba(255,255,255,0.92); border-radius:var(--radius);
      box-shadow:0 4px 20px rgba(12,123,179,0.1); overflow:hidden;
    }
    .table-header {
      display:flex; align-items:center; justify-content:space-between;
      padding:1.25rem 1.5rem;
      border-bottom:1px solid var(--border);
      flex-wrap:wrap; gap:1rem;
    }
    .table-header h2 { font-family:'Playfair Display',serif; font-size:1.2rem; color:var(--navy); }
    .search-form { display:flex; gap:0.5rem; }
    .search-input {
      padding:0.5rem 1rem; border-radius:50px;
      border:1.5px solid var(--border); font-family:inherit;
      font-size:0.85rem; outline:none; background:#f4f9fd;
      transition:border-color 0.2s, box-shadow 0.2s; min-width:220px;
    }
    .search-input:focus { border-color:var(--blue); box-shadow:0 0 0 3px rgba(12,123,179,0.1); }
    .btn-search {
      padding:0.5rem 1.1rem; background:var(--blue); color:#fff;
      border:none; border-radius:50px; font-family:inherit;
      font-size:0.85rem; font-weight:700; cursor:pointer;
      transition:opacity 0.2s;
    }
    .btn-search:hover { opacity:0.85; }
    .btn-clear {
      padding:0.5rem 1rem; background:rgba(12,123,179,0.08);
      color:var(--blue); border:1.5px solid var(--border);
      border-radius:50px; font-family:inherit; font-size:0.85rem;
      font-weight:700; cursor:pointer; text-decoration:none;
    }

    /* TABLE */
    .table-wrap { overflow-x:auto; }
    table { width:100%; border-collapse:collapse; font-size:0.85rem; }
    thead th {
      background:rgba(12,123,179,0.06); color:var(--mid);
      font-size:0.72rem; font-weight:800; text-transform:uppercase;
      letter-spacing:0.08em; padding:0.85rem 1rem; text-align:left;
      border-bottom:1px solid var(--border); white-space:nowrap;
    }
    tbody tr { border-bottom:1px solid rgba(12,123,179,0.07); transition:background 0.15s; }
    tbody tr:hover { background:rgba(12,123,179,0.03); }
    tbody td { padding:0.85rem 1rem; color:var(--text); vertical-align:middle; }
    .status-chip {
      display:inline-block; padding:0.2rem 0.65rem; border-radius:50px;
      font-size:0.72rem; font-weight:700;
    }
    .status-active   { background:rgba(46,204,142,0.12); color:#1a9e6a; border:1px solid rgba(46,204,142,0.4); }
    .status-inactive { background:rgba(224,92,122,0.1);  color:#c0294a; border:1px solid rgba(224,92,122,0.3); }
    .status-pending  { background:rgba(255,165,0,0.1);   color:#b07000; border:1px solid rgba(255,165,0,0.3); }

    .action-btns { display:flex; gap:0.4rem; flex-wrap:wrap; }
    .btn-view, .btn-edit-row, .btn-del {
      padding:0.3rem 0.75rem; border-radius:50px;
      font-size:0.75rem; font-weight:700; border:none;
      cursor:pointer; text-decoration:none; display:inline-block;
      transition:opacity 0.2s;
    }
    .btn-view     { background:rgba(12,123,179,0.1); color:var(--blue); border:1px solid rgba(12,123,179,0.3); }
    .btn-edit-row { background:rgba(255,165,0,0.12); color:#b07000;    border:1px solid rgba(255,165,0,0.3); }
    .btn-del      { background:rgba(224,92,122,0.1); color:#c0294a;    border:1px solid rgba(224,92,122,0.3); }
    .btn-view:hover, .btn-edit-row:hover, .btn-del:hover { opacity:0.75; }

    .no-results { text-align:center; padding:3rem; color:var(--mid); }

    /* MODAL OVERLAY */
    .modal-overlay {
      display:none; position:fixed; inset:0;
      background:rgba(11,31,58,0.55); backdrop-filter:blur(4px);
      z-index:200; align-items:center; justify-content:center;
    }
    .modal-overlay.open { display:flex; }
    .modal {
      background:#fff; border-radius:16px; padding:2rem;
      max-width:560px; width:90%; max-height:90vh; overflow-y:auto;
      box-shadow:0 20px 60px rgba(0,0,0,0.2); animation:popIn 0.3s ease both;
    }
    @keyframes popIn { from{opacity:0;transform:scale(0.93)} to{opacity:1;transform:scale(1)} }
    .modal h2 { font-family:'Playfair Display',serif; font-size:1.3rem; color:var(--navy); margin-bottom:1.25rem; }
    .modal-grid { display:grid; grid-template-columns:1fr 1fr; gap:0.75rem 1.5rem; margin-bottom:1.5rem; }
    .modal-item label { font-size:0.7rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--light); }
    .modal-item p     { font-size:0.9rem; font-weight:600; color:var(--text); margin-top:0.1rem; }
    .modal-close {
      background:rgba(12,123,179,0.08); border:1.5px solid var(--border);
      color:var(--blue); border-radius:50px; padding:0.45rem 1.25rem;
      font-family:inherit; font-weight:700; cursor:pointer; font-size:0.85rem;
    }

    /* EDIT MODAL INPUTS */
    .modal-form-group { margin-bottom:0.85rem; }
    .modal-form-group label { display:block; font-size:0.75rem; font-weight:700; color:var(--mid); margin-bottom:0.3rem; }
    .modal-form-group input, .modal-form-group select {
      width:100%; padding:0.55rem 0.85rem; border-radius:8px;
      border:1.5px solid var(--border); font-family:inherit;
      font-size:0.88rem; background:#f4f9fd; outline:none;
    }
    .modal-form-group input:focus, .modal-form-group select:focus {
      border-color:var(--blue); background:#fff; box-shadow:0 0 0 3px rgba(12,123,179,0.1);
    }
    .modal-form-row { display:grid; grid-template-columns:1fr 1fr; gap:0.75rem; }
    .btn-save {
      background:linear-gradient(135deg,#0C7BB3,#7BAED0); color:#fff;
      border:none; border-radius:50px; padding:0.6rem 1.5rem;
      font-family:inherit; font-weight:800; font-size:0.88rem; cursor:pointer;
      transition:opacity 0.2s; margin-right:0.5rem;
    }
    .btn-save:hover { opacity:0.85; }

    @media(max-width:640px) {
      .stats { grid-template-columns:1fr; }
      .table-header { flex-direction:column; align-items:flex-start; }
      .search-form { width:100%; }
      .search-input { flex:1; min-width:0; }
      .modal-grid { grid-template-columns:1fr; }
      .modal-form-row { grid-template-columns:1fr; }
    }
  </style>
</head>
<body>

<div class="topbar">
  <a href="../index.php" class="topbar-logo">Uni<span>Club</span> <small style="font-size:0.7rem;font-weight:600;margin-left:4px;">Admin</small></a>
  <div class="topbar-right">
    <span class="admin-badge">🛡️ <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
    <a href="logout.php" class="btn-logout">🚪 Logout</a>
  </div>
</div>

<div class="container">
  <!-- Stats -->
  <div class="stats">
    <div class="stat-card">
      <div class="stat-icon">👥</div>
      <div><div class="stat-num"><?= $total ?></div><div class="stat-label">Total Students</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">✅</div>
      <div><div class="stat-num"><?= $active ?></div><div class="stat-label">Active Members</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-icon">🏛️</div>
      <div><div class="stat-num"><?= $clubs_q ?></div><div class="stat-label">Clubs Represented</div></div>
    </div>
  </div>

  <!-- Table -->
  <div class="table-card">
    <div class="table-header">
      <h2>📋 Registered Students <?php if ($search): ?><span style="font-size:0.85rem;color:var(--mid);font-family:'Nunito',sans-serif;font-weight:600;">— "<?= $search ?>"</span><?php endif; ?></h2>
      <form class="search-form" method="GET">
        <input class="search-input" type="text" name="search" placeholder="Search name, email, ID, club…" value="<?= $search ?>">
        <button type="submit" class="btn-search">🔍 Search</button>
        <?php if ($search): ?><a href="dashboard.php" class="btn-clear">✕ Clear</a><?php endif; ?>
      </form>
    </div>

    <div class="table-wrap">
      <?php if (empty($students)): ?>
        <div class="no-results">😕 No students found<?= $search ? " matching \"$search\"" : '' ?>.</div>
      <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>#</th>
            <th>Student ID</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Course</th>
            <th>Club</th>
            <th>Status</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($students as $i => $s): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($s['student_id']) ?></td>
            <td><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
            <td><?= htmlspecialchars($s['email']) ?></td>
            <td><?= htmlspecialchars($s['course']) ?></td>
            <td><?= htmlspecialchars($s['club']) ?></td>
            <td>
              <span class="status-chip status-<?= $s['status'] ?>">
                <?= ucfirst($s['status']) ?>
              </span>
            </td>
            <td><?= date('M j, Y', strtotime($s['created_at'])) ?></td>
            <td>
              <div class="action-btns">
                <button class="btn-view" onclick="openView(<?= htmlspecialchars(json_encode($s)) ?>)">👁 View</button>
                <button class="btn-edit-row" onclick="openEdit(<?= htmlspecialchars(json_encode($s)) ?>)">✏️ Edit</button>
                <button class="btn-del" onclick="confirmDelete(<?= $s['id'] ?>, '<?= addslashes($s['full_name']) ?>')">🗑 Delete</button>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- VIEW MODAL -->
<div class="modal-overlay" id="viewModal">
  <div class="modal">
    <h2>👤 Student Details</h2>
    <div class="modal-grid">
      <div class="modal-item"><label>Student ID</label><p id="v_sid"></p></div>
      <div class="modal-item"><label>Full Name</label><p id="v_name"></p></div>
      <div class="modal-item"><label>Email</label><p id="v_email"></p></div>
      <div class="modal-item"><label>Course</label><p id="v_course"></p></div>
      <div class="modal-item"><label>Year Level</label><p id="v_year"></p></div>
      <div class="modal-item"><label>Club</label><p id="v_club"></p></div>
      <div class="modal-item"><label>Status</label><p id="v_status"></p></div>
      <div class="modal-item"><label>Joined</label><p id="v_joined"></p></div>
    </div>
    <button class="modal-close" onclick="closeModal('viewModal')">Close</button>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <h2>✏️ Edit Student</h2>
    <form method="POST" action="update_student.php">
      <input type="hidden" name="id" id="e_id">
      <div class="modal-form-row">
        <div class="modal-form-group">
          <label>First Name</label>
          <input type="text" name="first_name" id="e_first" required>
        </div>
        <div class="modal-form-group">
          <label>Last Name</label>
          <input type="text" name="last_name" id="e_last" required>
        </div>
      </div>
      <div class="modal-form-group">
        <label>Email</label>
        <input type="email" name="email" id="e_email" required>
      </div>
      <div class="modal-form-row">
        <div class="modal-form-group">
          <label>Course</label>
          <select name="course" id="e_course">
            <?php foreach (['BS Computer Science','BS Information Technology','BS Computer Engineering','BS Electrical Engineering','BS Civil Engineering','BS Architecture','BS Nursing','BS Education','BS Business Administration','BS Accountancy'] as $c): ?>
              <option value="<?= $c ?>"><?= $c ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="modal-form-group">
          <label>Year Level</label>
          <select name="year_level" id="e_year">
            <?php foreach (['1st Year','2nd Year','3rd Year','4th Year','5th Year'] as $y): ?>
              <option value="<?= $y ?>"><?= $y ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-form-row">
        <div class="modal-form-group">
          <label>Club</label>
          <select name="club" id="e_club">
            <?php foreach (['Computer Science Society','Fine Arts & Design Club','Debate & Oratory Society','Environmental Advocates','Performing Arts Guild','Campus Journalism Club'] as $cl): ?>
              <option value="<?= $cl ?>"><?= $cl ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="modal-form-group">
          <label>Status</label>
          <select name="status" id="e_status">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
            <option value="pending">Pending</option>
          </select>
        </div>
      </div>
      <button type="submit" class="btn-save">Save Changes</button>
      <button type="button" class="modal-close" onclick="closeModal('editModal')">Cancel</button>
    </form>
  </div>
</div>

<!-- DELETE FORM (hidden) -->
<form method="POST" action="delete_student.php" id="deleteForm">
  <input type="hidden" name="id" id="delete_id">
</form>

<script>
function openView(s) {
  document.getElementById('v_sid').textContent    = s.student_id;
  document.getElementById('v_name').textContent   = s.full_name;
  document.getElementById('v_email').textContent  = s.email;
  document.getElementById('v_course').textContent = s.course;
  document.getElementById('v_year').textContent   = s.year_level;
  document.getElementById('v_club').textContent   = s.club;
  document.getElementById('v_status').textContent = s.status.charAt(0).toUpperCase() + s.status.slice(1);
  document.getElementById('v_joined').textContent = new Date(s.created_at).toLocaleDateString('en-US',{year:'numeric',month:'long',day:'numeric'});
  document.getElementById('viewModal').classList.add('open');
}

function openEdit(s) {
  document.getElementById('e_id').value      = s.id;
  document.getElementById('e_first').value   = s.first_name;
  document.getElementById('e_last').value    = s.last_name;
  document.getElementById('e_email').value   = s.email;
  document.getElementById('e_course').value  = s.course;
  document.getElementById('e_year').value    = s.year_level;
  document.getElementById('e_club').value    = s.club;
  document.getElementById('e_status').value  = s.status;
  document.getElementById('editModal').classList.add('open');
}

function confirmDelete(id, name) {
  if (confirm('Delete student "' + name + '"? This cannot be undone.')) {
    document.getElementById('delete_id').value = id;
    document.getElementById('deleteForm').submit();
  }
}

function closeModal(id) {
  document.getElementById(id).classList.remove('open');
}

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(m => {
  m.addEventListener('click', e => { if (e.target === m) m.classList.remove('open'); });
});
</script>
</body>
</html>