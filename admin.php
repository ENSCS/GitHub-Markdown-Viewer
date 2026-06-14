<?php
session_start();
require_once __DIR__ . '/config.php';

$jsonFile = __DIR__ . '/notelist.json';
$error    = '';
$success  = '';

// --- Logout ---
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}

// --- Login ---
if (isset($_POST['password'])) {
    if ($_POST['password'] === ADMIN_PASSWORD) {
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'รหัสผ่านไม่ถูกต้อง';
    }
}

// ต้อง login ก่อน
if (empty($_SESSION['admin'])) {
    // หน้า login
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login</title>
    <link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
    <style>
    .login-wrap { max-width: 360px; margin: 8rem auto; padding: 0 1.5rem; }
    .login-wrap h2 { margin-bottom: 1.5rem; }
    .form-group { margin-bottom: 1rem; }
    .form-group label { display: block; margin-bottom: 0.4rem; font-size: 0.9em; color: var(--muted); }
    .form-group input { width: 100%; padding: 0.6em 0.9em; border: 1px solid var(--border); border-radius: 8px; background: var(--code-bg); color: var(--fg); font-size: 1em; }
    .btn { background: var(--link); color: #fff; border: none; border-radius: 8px; padding: 0.6em 1.4em; font-size: 1em; cursor: pointer; width: 100%; }
    .btn:hover { opacity: 0.85; }
    .err { color: #ff3b30; font-size: 0.9em; margin-top: 0.5rem; }
    </style>
    </head>
    <body>
    <div class="login-wrap">
      <h2>🔐 Admin Login</h2>
      <form method="post">
        <div class="form-group">
          <label>Password</label>
          <input type="password" name="password" autofocus>
        </div>
        <button class="btn" type="submit">เข้าสู่ระบบ</button>
        <?php if ($error): ?><p class="err"><?php echo $error; ?></p><?php endif; ?>
      </form>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// --- โหลด notelist.json ---
$notes = json_decode(file_get_contents($jsonFile), true);
if (!is_array($notes)) $notes = array();

// --- Actions ---

// เพิ่ม / แก้ไข
if (isset($_POST['action']) && $_POST['action'] === 'save') {
    $slug    = trim($_POST['slug']);
    $url     = trim($_POST['url']);
    $oldSlug = trim($_POST['old_slug']);

    if ($slug === '' || $url === '') {
        $error = 'กรุณากรอก Slug และ URL';
    } elseif (!preg_match('/^[a-z0-9_-]+$/', $slug)) {
        $error = 'Slug ใช้ได้แค่ a-z, 0-9, - และ _';
    } else {
        // ถ้าเปลี่ยน slug ให้ลบอันเก่าก่อน
        if ($oldSlug !== '' && $oldSlug !== $slug && isset($notes[$oldSlug])) {
            unset($notes[$oldSlug]);
        }
        $notes[$slug] = $url;
        file_put_contents($jsonFile, json_encode($notes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $success = 'บันทึกเรียบร้อย';
    }
}

// ลบ
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    $slug = trim($_POST['slug']);
    if (isset($notes[$slug])) {
        unset($notes[$slug]);
        file_put_contents($jsonFile, json_encode($notes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $success = 'ลบเรียบร้อย';
    }
}

// reload หลัง save/delete
$notes = json_decode(file_get_contents($jsonFile), true);
if (!is_array($notes)) $notes = array();

// edit mode
$editSlug = isset($_GET['edit']) ? trim($_GET['edit']) : '';
$editUrl  = ($editSlug !== '' && isset($notes[$editSlug])) ? $notes[$editSlug] : '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin — Notes</title>
<link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
<style>
.admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
.logout { font-size: 0.85em; color: var(--muted); text-decoration: none; }
.logout:hover { color: var(--link); }
.card { background: var(--code-bg); border: 1px solid var(--border); border-radius: var(--radius); padding: 1.2rem 1.4rem; margin-bottom: 2rem; }
.form-group { margin-bottom: 1rem; }
.form-group label { display: block; margin-bottom: 0.4rem; font-size: 0.85em; color: var(--muted); }
.form-group input { width: 100%; padding: 0.6em 0.9em; border: 1px solid var(--border); border-radius: 8px; background: var(--bg); color: var(--fg); font-size: 0.95em; }
.btn-row { display: flex; gap: 0.5rem; }
.btn { border: none; border-radius: 8px; padding: 0.5em 1.2em; font-size: 0.9em; cursor: pointer; }
.btn-save { background: var(--link); color: #fff; }
.btn-save:hover { opacity: 0.85; }
.btn-cancel { background: var(--border); color: var(--fg); text-decoration: none; display: inline-flex; align-items: center; }
.btn-cancel:hover { opacity: 0.75; }
.note-list { list-style: none; padding: 0; }
.note-item { display: flex; align-items: flex-start; gap: 0.75rem; padding: 0.75rem 0; border-bottom: 1px solid var(--border); }
.note-item:last-child { border-bottom: none; }
.note-info { flex: 1; min-width: 0; }
.note-slug { font-weight: 700; font-size: 0.95em; }
.note-url { font-size: 0.8em; color: var(--muted); word-break: break-all; }
.note-actions { display: flex; gap: 0.4rem; flex-shrink: 0; }
.btn-edit { background: var(--code-bg); border: 1px solid var(--border); color: var(--fg); border-radius: 6px; padding: 0.3em 0.7em; font-size: 0.8em; text-decoration: none; }
.btn-edit:hover { background: var(--border); }
.btn-del { background: none; border: 1px solid #ff3b30; color: #ff3b30; border-radius: 6px; padding: 0.3em 0.7em; font-size: 0.8em; cursor: pointer; }
.btn-del:hover { background: #ff3b30; color: #fff; }
.msg-ok { color: #30d158; font-size: 0.9em; margin-bottom: 1rem; }
.msg-err { color: #ff3b30; font-size: 0.9em; margin-bottom: 1rem; }
.preview-link { font-size: 0.8em; color: var(--link); }
</style>
</head>
<body>
<div class="container">

  <div class="admin-header">
    <h1 style="margin:0;font-size:1.6em;">📋 Manage Notes</h1>
    <a class="logout" href="admin.php?logout=1">ออกจากระบบ</a>
  </div>

  <?php if ($success): ?><p class="msg-ok">✅ <?php echo $success; ?></p><?php endif; ?>
  <?php if ($error): ?><p class="msg-err">❌ <?php echo $error; ?></p><?php endif; ?>

  <!-- ฟอร์มเพิ่ม / แก้ไข -->
  <div class="card">
    <h2 style="margin-top:0;font-size:1.1em;margin-bottom:1rem;">
      <?php echo $editSlug !== '' ? '✏️ แก้ไข: ' . htmlspecialchars($editSlug) : '➕ เพิ่มรายการใหม่'; ?>
    </h2>
    <form method="post">
      <input type="hidden" name="action" value="save">
      <input type="hidden" name="old_slug" value="<?php echo htmlspecialchars($editSlug); ?>">
      <div class="form-group">
        <label>Slug <span style="color:var(--muted);font-size:0.85em;">(a-z, 0-9, -, _)</span></label>
        <input type="text" name="slug" value="<?php echo htmlspecialchars($editSlug); ?>" placeholder="เช่น mrlikestock" required>
      </div>
      <div class="form-group">
        <label>GitHub Raw URL</label>
        <input type="url" name="url" value="<?php echo htmlspecialchars($editUrl); ?>" placeholder="https://raw.githubusercontent.com/..." required>
      </div>
      <div class="btn-row">
        <button class="btn btn-save" type="submit">💾 บันทึก</button>
        <?php if ($editSlug !== ''): ?>
        <a class="btn btn-cancel" href="admin.php">ยกเลิก</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- รายการ -->
  <h2 style="font-size:1.1em;margin-bottom:1rem;">รายการทั้งหมด (<?php echo count($notes); ?>)</h2>
  <?php if (empty($notes)): ?>
    <p style="color:var(--muted);">ยังไม่มีรายการ</p>
  <?php else: ?>
  <ul class="note-list">
    <?php foreach ($notes as $slug => $url): ?>
    <li class="note-item">
      <div class="note-info">
        <div class="note-slug">
          <?php echo htmlspecialchars($slug); ?>
          <a class="preview-link" href="notes.php?file=<?php echo urlencode($slug); ?>" target="_blank">↗ ดู</a>
        </div>
        <div class="note-url"><?php echo htmlspecialchars($url); ?></div>
      </div>
      <div class="note-actions">
        <a class="btn-edit" href="admin.php?edit=<?php echo urlencode($slug); ?>">✏️</a>
        <form method="post" onsubmit="return confirm('ลบ <?php echo htmlspecialchars($slug); ?> ?')">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="slug" value="<?php echo htmlspecialchars($slug); ?>">
          <button class="btn-del" type="submit">🗑</button>
        </form>
      </div>
    </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>

</div>
</body>
</html>
