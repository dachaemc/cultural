<?php
include 'header.php';
if (isset($_SESSION['admin_logged_in'])) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    // Hardcoded credentials for simplicity
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = 'ชื่อผู้ใช้ หรือ รหัสผ่านไม่ถูกต้อง';
    }
}
?>
<div class="login-page">
    <div class="login-card">
        <h2><i class="fa-solid fa-dragon"></i> Admin Login</h2>
        <?php if($error): ?><p style="color:red; margin-bottom:15px;"><?= $error ?></p><?php endif; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username (admin)" required>
            <input type="password" name="password" placeholder="Password (admin123)" required>
            <button type="submit">เข้าสู่ระบบ</button>
        </form>
        <p style="margin-top: 20px;"><a href="../index.php" style="color: #666; text-decoration: none;">กลับหน้าเว็บไซต์</a></p>
    </div>
</div>
<?php include 'footer.php'; ?>
