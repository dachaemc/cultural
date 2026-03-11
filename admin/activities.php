<?php
include 'header.php';
$file_path = '../data/activities.json';
$activities_json = file_exists($file_path) ? file_get_contents($file_path) : '[]';
$activities = json_decode($activities_json, true) ?: [];

include_once '../utils.php';

// Check if we're editing
$edit_mode = false;
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int)$_GET['edit'];
    $edit_mode = true;
    foreach ($activities as $a) {
        if ($a['id'] === $edit_id) {
            $edit_data = $a;
            break;
        }
    }
}

// Remove single gallery image
if (isset($_GET['delete_gallery']) && isset($_GET['act_id'])) {
    $act_id = (int)$_GET['act_id'];
    $img_to_delete = $_GET['delete_gallery'];
    foreach ($activities as $key => $a) {
        if ($a['id'] === $act_id && isset($a['gallery'])) {
            $activities[$key]['gallery'] = array_filter($a['gallery'], function($g) use ($img_to_delete) {
                return $g !== $img_to_delete;
            });
            $activities[$key]['gallery'] = array_values($activities[$key]['gallery']); // re-index
            break;
        }
    }
    file_put_contents($file_path, json_encode(array_values($activities), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('Location: activities.php?edit=' . $act_id);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $date = $_POST['date'];
    $description = $_POST['description'];
    $content = $_POST['content'] ?? '';
    $image = $_POST['image'] ?? '';
    $gallery = [];
    
    // Retrieve old data if editing
    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        $update_id = (int)$_POST['edit_id'];
        foreach ($activities as $a) {
            if ($a['id'] === $update_id) {
                if (empty($image)) $image = $a['image'];
                if (isset($a['gallery']) && is_array($a['gallery'])) {
                    $gallery = $a['gallery'];
                }
                break;
            }
        }
    }
    
    // Handle Main Image File Upload
    $upload_dir = '../uploads/activities/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] === UPLOAD_ERR_OK) {
        $filename = time() . '_main_' . basename($_FILES['image_file']['name']);
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['image_file']['tmp_name'], $target_path)) {
            $image = 'uploads/activities/' . $filename;
        }
    }
    
    // Handle Gallery File Uploads
    if (isset($_FILES['gallery_files'])) {
        $countfiles = count($_FILES['gallery_files']['name']);
        for($i = 0; $i < $countfiles; $i++){
            if($_FILES['gallery_files']['error'][$i] === UPLOAD_ERR_OK) {
                $filename = time() . '_' . $i . '_gal_' . basename($_FILES['gallery_files']['name'][$i]);
                $target_path = $upload_dir . $filename;
                if(move_uploaded_file($_FILES['gallery_files']['tmp_name'][$i], $target_path)){
                    $gallery[] = 'uploads/activities/' . $filename;
                }
            }
        }
    }
    
    if (empty($image)) {
        $image = 'https://via.placeholder.com/600x400?text=No+Image';
    }

    if (isset($_POST['edit_id']) && !empty($_POST['edit_id'])) {
        $update_id = (int)$_POST['edit_id'];
        foreach ($activities as $key => $a) {
            if ($a['id'] === $update_id) {
                $activities[$key]['title'] = htmlspecialchars($title);
                $activities[$key]['date'] = htmlspecialchars($date);
                $activities[$key]['description'] = htmlspecialchars($description);
                $activities[$key]['content'] = htmlspecialchars($content);
                $activities[$key]['image'] = htmlspecialchars($image);
                $activities[$key]['gallery'] = $gallery;
                break;
            }
        }
    } else {
        $new_id = empty($activities) ? 1 : max(array_column($activities, 'id')) + 1;
        $new_item = [
            'id' => $new_id,
            'title' => htmlspecialchars($title),
            'date' => htmlspecialchars($date),
            'description' => htmlspecialchars($description),
            'content' => htmlspecialchars($content),
            'image' => htmlspecialchars($image),
            'gallery' => $gallery
        ];
        $activities[] = $new_item;
    }
    
    file_put_contents($file_path, json_encode(array_values($activities), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('Location: activities.php');
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $activities = array_filter($activities, function($a) use ($id) { return $a['id'] !== $id; });
    file_put_contents($file_path, json_encode(array_values($activities), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    header('Location: activities.php');
    exit;
}
?>
<div class="page-header">
    <h2><i class="fa-solid fa-calendar-alt"></i> จัดการกิจกรรมและประชาสัมพันธ์</h2>
</div>

<div class="form-card">
    <h3 style="margin-bottom: 20px;"><?= $edit_mode ? 'แก้ไขกิจกรรม' : 'เพิ่มกิจกรรมใหม่' ?></h3>
    <form method="POST" action="activities.php" enctype="multipart/form-data">
        <?php if ($edit_mode): ?>
            <input type="hidden" name="edit_id" value="<?= $edit_data['id'] ?>">
        <?php endif; ?>
        <div class="form-group">
            <label>ชื่อกิจกรรม</label>
            <input type="text" name="title" class="form-control" value="<?= $edit_mode ? htmlspecialchars($edit_data['title']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label>วันที่จัดกิจกรรม</label>
            <input type="date" name="date" class="form-control" value="<?= $edit_mode ? htmlspecialchars($edit_data['date']) : '' ?>" required>
        </div>
        <div class="form-group">
            <label>คำอธิบายแบบสั้น (แสดงหน้าแรก)</label>
            <textarea name="description" class="form-control" rows="2" style="width: 100%; border: 1px solid #ddd; border-radius: 4px; padding: 10px; font-family: 'Prompt';" required><?= $edit_mode ? htmlspecialchars($edit_data['description']) : '' ?></textarea>
        </div>
        <div class="form-group">
            <label>เนื้อหา/รายละเอียดทั้งหมด (แสดงในหน้ารายละเอียด)</label>
            <textarea name="content" class="form-control" rows="5" style="width: 100%; border: 1px solid #ddd; border-radius: 4px; padding: 10px; font-family: 'Prompt';"><?= $edit_mode && isset($edit_data['content']) ? htmlspecialchars($edit_data['content']) : '' ?></textarea>
        </div>
        
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
        <h4 style="margin-bottom: 15px; color: var(--primary);">รูปภาพปกหลัก (หน้าแรก)</h4>
        
        <?php if ($edit_mode && !empty($edit_data['image'])): ?>
            <div style="margin-bottom: 15px;">
                <p>รูปภาพหน้าปกปัจจุบัน:</p>
                <img src="<?= htmlspecialchars(format_image_url($edit_data['image'], true)) ?>" style="max-height: 150px; border-radius: 4px;">
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label>อัปโหลดรูปหน้าปกใหม่ (จะแทนที่รูปเดิม)</label>
            <input type="file" name="image_file" class="form-control" accept="image/*">
        </div>
        
        <hr style="margin: 20px 0; border: none; border-top: 1px solid #eee;">
        <h4 style="margin-bottom: 15px; color: var(--primary);">แกลอรี่รูปภาพกิจกรรมเพิ่มเติม (เลือกได้ 3-5 ภาพหรือมากกว่านั้น)</h4>
        
        <?php if ($edit_mode && !empty($edit_data['gallery'])): ?>
            <div style="margin-bottom: 15px;">
                <p>รูปภาพในแกลอรี่ปัจจุบัน:</p>
                <div style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;">
                    <?php foreach($edit_data['gallery'] as $gal_img): ?>
                        <div style="position: relative; display: inline-block;">
                            <img src="<?= htmlspecialchars(format_image_url($gal_img, true)) ?>" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
                            <a href="?delete_gallery=<?= urlencode($gal_img) ?>&act_id=<?= $edit_data['id'] ?>" style="position: absolute; top: 5px; right: 5px; background: red; color: white; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; border-radius: 50%; text-decoration: none; font-size: 10px;" onclick="return confirm('ลบภาพนี้?');"><i class="fa-solid fa-times"></i></a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="form-group">
            <label>เพิ่มรูปภาพแกลอรี่ (เลือกหลายไฟล์ได้พร้อมกัน)</label>
            <input type="file" name="gallery_files[]" class="form-control" accept="image/*" multiple>
            <small style="color: #666;">กด <kbd>Ctrl</kbd> (หรือ <kbd>Cmd</kbd> บน Mac) ค้างไว้เพื่อเลือกหลายรูปตอนอัปโหลด</small>
        </div>

        <button type="submit" class="btn <?= $edit_mode ? 'btn-success' : 'btn-primary' ?>" style="margin-top:20px; <?= $edit_mode ? 'background: #28a745; color: white;' : '' ?>">
            <i class="fa-solid <?= $edit_mode ? 'fa-save' : 'fa-plus' ?>"></i> <?= $edit_mode ? 'บันทึกการแก้ไข' : 'เพิ่มกิจกรรม' ?>
        </button>
        <?php if ($edit_mode): ?>
            <a href="activities.php" class="btn btn-secondary" style="margin-top:20px; background: #6c757d; color: white;">ยกเลิก</a>
        <?php endif; ?>
    </form>
</div>

<div class="form-card" style="overflow-x:auto;">
    <h3 style="margin-bottom: 20px;">รายชื่อกิจกรรมทั้งหมด</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>รูปภาพ</th>
                <th>ชื่อกิจกรรม</th>
                <th>วันที่</th>
                <th>ภาพแกลอรี่</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($activities as $a): ?>
            <tr>
                <td><img src="<?= htmlspecialchars(format_image_url($a['image'], true)) ?>" width="80" height="50" style="object-fit:cover; border-radius:4px;"></td>
                <td style="font-weight: 500;"><?= htmlspecialchars($a['title']) ?></td>
                <td><?= htmlspecialchars($a['date']) ?></td>
                <td><?= isset($a['gallery']) ? count($a['gallery']) : 0 ?> ภาพ</td>
                <td>
                    <a href="?edit=<?= $a['id'] ?>" class="btn btn-sm" style="background: #ffc107; color: #333; margin-right: 5px;"><i class="fa-solid fa-edit"></i> แก้ไข</a>
                    <a href="?delete=<?= $a['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('ยืนยันลบกิจกรรมนี้?');"><i class="fa-solid fa-trash"></i> ลบ</a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($activities)): ?>
                <tr><td colspan="5" style="text-align: center;">ไม่มีกิจกรรม</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
