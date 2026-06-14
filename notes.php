<?php
require_once __DIR__ . '/parsedown.php';

// โหลด notelist.json
$files = json_decode(file_get_contents(__DIR__ . '/notelist.json'), true);

// เช็ค slug
$slug = isset($_GET['file']) ? trim($_GET['file']) : '';
if ($slug === '' || !isset($files[$slug])) {
    header('Location: /');
    exit;
}

$url = $files[$slug];
$content = @file_get_contents($url);
$p = new parsedown();
$p->setBreaksEnabled(true);
$html = $p->text($content);

$html = preg_replace(
    '/(?<!href=")(?<!href=\'\')(https?:\/\/[^\s<>"\')\]]+)(?![^<]*<\/a>)/i',
    '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
    $html
);

// เพิ่ม id ให้ heading ทุกตัว สำหรับ anchor link
$html = preg_replace_callback(
    '/<(h[1-6])>(.*?)<\/h[1-6]>/is',
    function($m) {
        $id = strip_tags($m[2]);
        $id = preg_replace('/\s*—\s*/', '--', $id);
        $id = preg_replace('/[.?!,()\[\]{}"\']/u', '', $id);
        $id = strtolower($id);
        $id = preg_replace('/\s+/', '-', trim($id));
        return '<' . $m[1] . ' id="' . $id . '">' . $m[2] . '</' . $m[1] . '>';
    },
    $html
);

// ดึงบรรทัดแรกที่ไม่ว่างจาก markdown มาเป็น title
$pageTitle = 'ENS Notes';
foreach (explode("\n", $content) as $line) {
    $line = trim($line);
    if ($line !== '') {
        $pageTitle = trim(ltrim($line, '#'));
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo htmlspecialchars($pageTitle); ?></title>
<link rel="stylesheet" href="style.css?v=<?php echo filemtime(__DIR__ . '/style.css'); ?>">
</head>
<body>
<div class="container">
<div class="theme-wrap">
  <button class="font-btn" id="fontDown">A−</button>
  <button class="font-btn" id="fontUp">A+</button>
  <button class="theme-toggle" id="themeToggle">🌙 Dark</button>
</div>
<?php echo $html; ?>
</div>
<script>
(function() {
  var btn = document.getElementById('themeToggle');
  var fontUp = document.getElementById('fontUp');
  var fontDown = document.getElementById('fontDown');

  // --- Theme ---
  var theme = localStorage.getItem('theme');
  if (!theme) {
    theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }
  function applyTheme(t) {
    document.documentElement.setAttribute('data-theme', t);
    btn.textContent = t === 'dark' ? '☀️ Light' : '🌙 Dark';
    localStorage.setItem('theme', t);
  }
  applyTheme(theme);
  btn.addEventListener('click', function() {
    var current = document.documentElement.getAttribute('data-theme');
    applyTheme(current === 'dark' ? 'light' : 'dark');
  });

  // --- Font Size ---
  var minSize = 8;
  var maxSize = 26;
  var fontSize = parseInt(localStorage.getItem('fontSize')) || 18;
  function applyFontSize(size) {
    fontSize = Math.min(maxSize, Math.max(minSize, size));
    document.body.style.fontSize = fontSize + 'px';
    localStorage.setItem('fontSize', fontSize);
  }
  applyFontSize(fontSize);
  fontUp.addEventListener('click', function() { applyFontSize(fontSize + 2); });
  fontDown.addEventListener('click', function() { applyFontSize(fontSize - 2); });

  // --- Links ---
  document.querySelectorAll('.container a').forEach(function(a) {
    if (a.getAttribute('href') && a.getAttribute('href').indexOf('#') === 0) return;
    a.setAttribute('target', '_blank');
    a.setAttribute('rel', 'noopener noreferrer');
  });
})();
</script>
</body>
</html>
