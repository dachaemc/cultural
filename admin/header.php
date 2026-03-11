<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการหลังบ้าน | ชมรมรอยอาระยะ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<?php if (isset($_SESSION['admin_logged_in'])): ?>
    <div class="sidebar">
        <h3 class="sidebar-title"><i class="fa-solid fa-dragon"></i> Admin Panel</h3>
        <ul class="sidebar-menu">
            <li><a href="index.php"><i class="fa-solid fa-users"></i> จัดการสมาชิก</a></li>
            <li><a href="activities.php"><i class="fa-solid fa-calendar-alt"></i> จัดการกิจกรรม</a></li>
            <li><a href="committee.php"><i class="fa-solid fa-user-tie"></i> จัดการกรรมการ</a></li>
            <li><a href="../index.php" target="_blank"><i class="fa-solid fa-home"></i> ดูหน้าเว็บไซต์</a></li>
            <li><a href="logout.php"><i class="fa-solid fa-sign-out-alt"></i> ออกจากระบบ</a></li>
        </ul>
    </div>
    <div class="main-content">
<?php endif; ?>
