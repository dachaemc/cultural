<?php
include 'header.php';
$file_path = '../data/members.json';

// Delete member
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $members_json = file_exists($file_path) ? file_get_contents($file_path) : '[]';
    $members = json_decode($members_json, true) ?: [];
    $members = array_filter($members, function($m) use ($id) { return $m['id'] !== $id; });
    file_put_contents($file_path, json_encode(array_values($members), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('Location: index.php');
    exit;
}

$members_json = file_exists($file_path) ? file_get_contents($file_path) : '[]';
$members = json_decode($members_json, true) ?: [];
?>
<div class="page-header">
    <h2><i class="fa-solid fa-users"></i> จัดการสมาชิกชมรม (ทั้งหมด <?= count($members) ?> คน)</h2>
</div>

<div class="form-card" style="overflow-x:auto;">
    <table class="data-table">
        <thead>
            <tr>
                <th>ลำดับ</th>
                <th>ชื่อ - นามสกุล</th>
                <th>เพศ</th>
                <th>ระดับชั้น</th>
                <th>เหตุผล</th>
                <th>วันที่สมัคร</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($members as $index => $m): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($m['fullname']) ?></td>
                <td><?= htmlspecialchars($m['gender']) ?></td>
                <td><?= htmlspecialchars($m['grade']) ?></td>
                <td><?= htmlspecialchars($m['motivation']) ?></td>
                <td><?= date('d/m/Y H:i', strtotime($m['registered_at'])) ?></td>
                <td>
                    <a href="?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('ยืนยันลบสมาชิกท่านนี้?');"><i class="fa-solid fa-trash"></i> ลบ</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
