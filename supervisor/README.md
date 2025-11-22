# Supervisor Configuration Files

هذا المجلد يحتوي على ملفات الكونفيجريشن الخاصة بـ Supervisor لإدارة عمليات الـ Queue Workers.

## الملفات المتوفرة

1. **laravel-worker-default.conf** - Worker لـ default queue
2. **laravel-worker-notifications.conf** - Worker لـ notifications queue

## التثبيت - اختر إحدى الطرق

### الطريقة 1: إضافة المجلد إلى Supervisor (موصى بها) ✅

هذه الطريقة تجعل Supervisor يكتشف تلقائياً أي ملفات configuration جديدة في المجلد.

#### الخطوة 1: تعديل ملف supervisord.conf

```bash
sudo nano /etc/supervisor/supervisord.conf
```

#### الخطوة 2: أضف السطر التالي في نهاية الملف (قبل أو بعد section [include])

```ini
[include]
files = /etc/supervisor/conf.d/*.conf /var/www/DWS_Requisition_Management_System/supervisor/*.conf
```

أو إذا كان الـ section موجود، عدّل السطر `files` ليكون:

```ini
[include]
files = /etc/supervisor/conf.d/*.conf /var/www/DWS_Requisition_Management_System/supervisor/*.conf
```

#### الخطوة 3: إعادة تشغيل Supervisor

```bash
sudo systemctl restart supervisor
# أو
sudo service supervisor restart
```

#### الخطوة 4: التحقق من التحميل

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl status
```

---

### الطريقة 2: Symbolic Links

إنشاء symbolic links من مجلد المشروع إلى `/etc/supervisor/conf.d/`

```bash
# إنشاء symlinks لكل ملف
sudo ln -s /var/www/DWS_Requisition_Management_System/supervisor/laravel-worker-default.conf /etc/supervisor/conf.d/
sudo ln -s /var/www/DWS_Requisition_Management_System/supervisor/laravel-worker-notifications.conf /etc/supervisor/conf.d/

# إعادة تحميل Supervisor
sudo supervisorctl reread
sudo supervisorctl update
```

**ميزة**: أي تعديل في المشروع يظهر مباشرة بدون نسخ الملفات.

---

### الطريقة 3: نسخ الملفات (الطريقة التقليدية)

```bash
# نسخ جميع الملفات
sudo cp /var/www/DWS_Requisition_Management_System/supervisor/*.conf /etc/supervisor/conf.d/

# إعادة تحميل Supervisor
sudo supervisorctl reread
sudo supervisorctl update
```

**ملاحظة**: تحتاج لإعادة النسخ عند كل تعديل.

---

## بدء الـ Workers

بعد إتمام التثبيت بأي طريقة:

```bash
sudo supervisorctl start laravel-worker-default:*
sudo supervisorctl start laravel-worker-notifications:*
```

## الأوامر الشائعة

### عرض حالة الـ Workers
```bash
sudo supervisorctl status
```

### إعادة تشغيل الـ Workers
```bash
sudo supervisorctl restart laravel-worker-default:*
sudo supervisorctl restart laravel-worker-notifications:*
```

### إيقاف الـ Workers
```bash
sudo supervisorctl stop laravel-worker-default:*
sudo supervisorctl stop laravel-worker-notifications:*
```

### عرض Logs
```bash
# Default queue logs
tail -f /var/www/DWS_Requisition_Management_System/storage/logs/queue-default.log

# Notifications queue logs
tail -f /var/www/DWS_Requisition_Management_System/storage/logs/queue-notifications.log
```

## الإعدادات

### عدد الـ Processes (numprocs)
حالياً كل worker له 2 processes. يمكن زيادة أو تقليل العدد حسب الحاجة.

### المستخدم (user)
الـ Workers تعمل تحت مستخدم `www-data`. تأكد من أن هذا المستخدم له صلاحيات على مجلد المشروع.

### Timeout
الـ timeout الحالي 300 ثانية (5 دقائق). يمكن تعديله حسب طبيعة الـ Jobs.

### Tries
عدد المحاولات 3 مرات. يمكن تعديله في ملفات الكونفيجريشن.

## ملاحظات

- تأكد من وجود مجلد `storage/logs` وأن له صلاحيات الكتابة
- بعد تعديل أي ملف configuration، نفذ:
  ```bash
  sudo supervisorctl reread
  sudo supervisorctl update
  sudo supervisorctl restart laravel-worker-default:*
  sudo supervisorctl restart laravel-worker-notifications:*
  ```
- لمراقبة الـ Workers في الوقت الفعلي: `sudo supervisorctl tail -f laravel-worker-default`

