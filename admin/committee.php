<?php
include 'header.php';
$file_path = '../data/committee.json';
$committee_json = file_exists($file_path) ? file_get_contents($file_path) : '[]';
$committee = json_decode($committee_json, true) ?: [];

include_once '../utils.php';

// Check if we're editing
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_mode = true;
    foreach ($committee as $c) {
        if ($c['id'] === $edit_id) {
            $edit_data = $c;
            break;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $position = $_POST['position'];
    $grade = $_POST['grade'];
    $image = $_POST['image'] ?? '';
    
    // Handle File Upload
    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/committee/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $filename = time() . '_' . basename($_FILES['image_file']['name']);
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_path)) {
            $image = 'uploads/committee/' . $filename;
        }
    } else if (empty($image) && isset($_POST['edit_id'])) {
        // If no file uploaded and url is empty, try to keep the old image
        $update_id = (int)$_POST['edit_id'];
        foreach ($committee as $c) {
            if ($c['id'] === $update_id) {
                $image = $c['image'];
                break;
            }
        }
    }
    
    // Fallback if still empty
    if (empty($image)) {
        $image = 'https://via.placeholder.com/300x300?text=No+Image';
    }

    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        // Update existing committee member
        $update_id = (int)$_POST['edit_id'];
        foreach ($committee as $key => $c) {
            if ($c['id'] === $update_id) {
                $committee[$key]['name'] = htmlspecialchars($name);
                $committee[$key]['position'] = htmlspecialchars($position);
                $committee[$key]['grade'] = htmlspecialchars($grade);
                $committee[$key]['image'] = htmlspecialchars($image);
                break;
            }
        }
    } else {
        // Add new member
        $new_id = empty($committee) ? 1 : max(array_column($committee, 'id')) + 1;
        $new_item = [
            'id' => $new_id,
            'name' => htmlspecialchars($name),
            'position' => htmlspecialchars($position),
            'grade' => htmlspecialchars($grade),
            'image' => htmlspecialchars($image)
        ];
        $committee[] = $new_item;
    }
    
    file_put_contents($file_path, json_encode(array_values($committee), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('Location: committee.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $committee = array_filter($committee, function($c) use ($id) { return isset($c['id']) && $c['id'] !== $id; });
    file_put_contents($file_path, json_encode(array_values($committee), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('Location: committee.php');
    exit;
}
?>
<div class="page-header">
    <h2><i class="fa-solid fa-user-tie"></i> จัดการกรรมการชมรม</h2>
</div>

<div class="form-card">
    <h3 style="margin-bottom: 20px;"><?= $edit_mode ? 'แก้ไขข้อมูลกรรมการ' : 'เพิ่มกรรมการใหม่' ?></h3>
    <form method="POST" action="committee.php" enctype="multipart/form-data">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="edit_id" value="<?= htmlspecialchars($edit_data['id']) ?>">
        <?php endif; ?>
        <div class="form-group">
            <label>ชื่อ-นามสกุล</label>
            <input type="text" name="name" class="form-control" value="<?= $edit_mode ? htmlspecialchars($edit_data['name']) : '' ?>" required placeholder="เช่น นายสมชาย แซ่หวัง">
        </div>
        <div class="form-group">
            <label>ตำแหน่งในชมรม</label>
            <input type="text" name="position" class="form-control" value="<?= $edit_mode ? htmlspecialchars($edit_data['position']) : '' ?>" required placeholder="เช่น ประธานชมรม">
        </div>
        <div class="form-group">
            <label>ระดับชั้น</label>
            <input type="text" name="grade" class="form-control" value="<?= $edit_mode ? htmlspecialchars($edit_data['grade']) : '' ?>" required placeholder="เช่น ม.6/1">
        </div>
        
        <?php if ($edit_mode && !empty($edit_data['image'])): ?>
            <div style="margin-bottom: 15px;">
                <p>รูปภาพปัจจุบัน:</p>
                <img src="<?= htmlspecialchars(format_image_url($edit_data['image'], true)) ?>" style="max-width: 150px; border-radius: 50%;">
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label>1. อัปโหลดไฟล์รูปภาพโปรไฟล์ (ครึ่งตัว แนะนำภาพจัตุรัส)</label>
            <input type="file" name="image_file" class="form-control" accept="image/*">
            <small style="color: #666;">(รองรับไฟล์ .jpg, .png)</small>
        </div>
        <div class="form-group">
            <label>2. หรือใส่ URL รูปภาพโปรไฟล์ออนไลน์</label>
            <input type="url" name="image" class="form-control" value="<?= $edit_mode ? (($edit_data['image'] && strpos($edit_data['image'], 'http') === 0) ? htmlspecialchars($edit_data['image']) : '') : '' ?>" placeholder="https://...">
            <small style="color: #666;">* หากไม่กรอกหรือเลือกไฟล์ ระบบจะพยายามใช้ภาพเดิม</small>
        </div>

        <button type="submit" class="btn <?= $edit_mode ? 'btn-success' : 'btn-primary' ?>" style="<?= $edit_mode ? 'background: #28a745; color: white;' : '' ?>">
            <i class="fa-solid <?= $edit_mode ? 'fa-save' : 'fa-user-plus' ?>"></i> <?= $edit_mode ? 'บันทึกการแก้ไข' : 'เพิ่มกรรมการ' ?>
        </button>
        <?php if ($edit_mode): ?>
            <a href="committee.php" class="btn btn-secondary" style="background: #6c757d; color: white;">ยกเลิก</a>
        <?php endif; ?>
    </form>
</div>

<div class="form-card" style="overflow-x:auto;">
    <h3 style="margin-bottom: 20px;">รายชื่อคณะกรรมการทั้งหมด</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>รูปภาพ</th>
                <th>ชื่อ-นามสกุล</th>
                <th>ตำแหน่ง</th>
                <th>ระดับชั้น</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($committee as $c): ?>
            <tr>
                <td><img src="<?= htmlspecialchars(format_image_url($c['image'], true)) ?>" width="50" height="50" style="object-fit:cover; border-radius:50%;"></td>
                <td style="font-weight: 500;"><?= htmlspecialchars($c['name']) ?></td>
                <td><?= htmlspecialchars($c['position']) ?></td>
                <td><?= htmlspecialchars($c['grade']) ?></td>
                <td>
                    <a href="?edit=<?= $c['id'] ?>" class="btn btn-sm" style="background: #ffc107; color: #333; margin-right: 5px;"><i class="fa-solid fa-edit"></i> แก้ไข</a>
                    <a href="?delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('ยืนยันลบกรรมการท่านนี้?');"><i class="fa-solid fa-trash"></i> ลบ</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($committee)): ?>
                <tr><td colspan="5" style="text-align: center;">ไม่มีข้อมูลคณะกรรมการ</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
