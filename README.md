# ENS Notes — GitHub Markdown Viewer

แสดงไฟล์ Markdown จาก GitHub ในรูปแบบเว็บที่อ่านง่าย รองรับมือถือ มี Dark mode และปรับขนาดตัวอักษรได้

---

## ไฟล์ในระบบ

| ไฟล์ | หน้าที่ |
|------|---------|
| `notes.php` | หน้าแสดงผล Markdown |
| `admin.php` | หน้าจัดการรายการ (เพิ่ม/แก้ไข/ลบ) |
| `config.php` | เก็บ password admin |
| `notelist.json` | รายการ slug → GitHub Raw URL |
| `style.css` | ธีมและการแสดงผล |
| `parsedown.php` | แปลง Markdown เป็น HTML |

---

## การใช้งาน

### เปิดดูหน้า Note
```
notes.php?file=SLUG
```
ตัวอย่าง:
```
notes.php?file=readfs
notes.php?file=claudeai
```

### เข้าหน้า Admin
```
admin.php
```
ใส่ password จาก `config.php` แล้วสามารถ:
- **เพิ่ม** รายการใหม่
- **แก้ไข** slug หรือ URL
- **ลบ** รายการ
- **ดูตัวอย่าง** กด ↗ เปิด tab ใหม่

---

## การเพิ่ม Note ใหม่

### วิธีที่ 1: ผ่านหน้า Admin
1. เปิด `admin.php`
2. กรอก Slug และ GitHub Raw URL
3. กด บันทึก

### วิธีที่ 2: แก้ไฟล์ notelist.json โดยตรง
```json
{
  "slug-ที่ต้องการ": "https://raw.githubusercontent.com/..."
}
```

**หมายเหตุ:** Slug ใช้ได้แค่ `a-z`, `0-9`, `-` และ `_` เท่านั้น

---

## การเปลี่ยน Password Admin

แก้ไขใน `config.php`:
```php
define('ADMIN_PASSWORD', 'รหัสผ่านใหม่');
```

---

## ฟีเจอร์

- 🌙 Dark / ☀️ Light mode — จำค่าไว้ใน localStorage
- **A+ / A−** ปรับขนาดตัวอักษร — จำค่าไว้ใน localStorage
- 📱 Responsive รองรับมือถือ
- 🔗 Link ทุกอันเปิด tab ใหม่อัตโนมัติ
- ตารางเลื่อนซ้าย-ขวาได้บนมือถือ
- ถ้าเปิด slug ที่ไม่มีใน notelist.json จะ redirect กลับ `/` ทันที

---

## การเปลี่ยนธีม

แก้ไขใน `style.css` ได้เลย — ใช้ CSS variables ทำให้เปลี่ยนสีทั้งระบบได้จากจุดเดียว:

```css
:root {
  --bg: #ffffff;       /* พื้นหลัง */
  --fg: #1d1d1f;       /* ตัวอักษร */
  --link: #0071e3;     /* สี link */
  --border: #e5e5e7;   /* เส้นขอบ */
  --code-bg: #f5f5f7;  /* พื้นหลัง code/card */
}
```
