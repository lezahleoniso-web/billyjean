<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: dashboard.php'); exit; }

$id         = (int)($_POST['id'] ?? 0);
$first_name = htmlspecialchars(trim($_POST['first_name'] ?? ''), ENT_QUOTES, 'UTF-8');
$last_name  = htmlspecialchars(trim($_POST['last_name']  ?? ''), ENT_QUOTES, 'UTF-8');
$email      = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$course     = htmlspecialchars(trim($_POST['course']     ?? ''), ENT_QUOTES, 'UTF-8');
$year_level = htmlspecialchars(trim($_POST['year_level'] ?? ''), ENT_QUOTES, 'UTF-8');
$club       = htmlspecialchars(trim($_POST['club']       ?? ''), ENT_QUOTES, 'UTF-8');
$status     = in_array($_POST['status'] ?? '', ['active','inactive','pending']) ? $_POST['status'] : 'active';
$full_name  = $first_name . ' ' . $last_name;

$stmt = $conn->prepare("UPDATE students SET first_name=?, last_name=?, full_name=?, email=?, course=?, year_level=?, club=?, status=? WHERE id=?");
$stmt->bind_param("ssssssssi", $first_name, $last_name, $full_name, $email, $course, $year_level, $club, $status, $id);
$stmt->execute();
$stmt->close();

header('Location: dashboard.php?updated=1');
exit;