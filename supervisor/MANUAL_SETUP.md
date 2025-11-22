# 📝 الإعداد اليدوي لـ Supervisor - خطوة بخطوة

## الخطوة 1: عمل Backup لملف supervisord.conf ⚠️

```bash
sudo cp /etc/supervisor/supervisord.conf /etc/supervisor/supervisord.conf.backup
```

**مهم**: هذه خطوة احترازية للرجوع إذا حدث أي خطأ.

---

## الخطوة 2: فتح ملف supervisord.conf للتعديل

```bash
sudo nano /etc/supervisor/supervisord.conf
```

أو استخدم أي محرر نصوص تفضله:
```bash
sudo vim /etc/supervisor/supervisord.conf
# أو
sudo vi /etc/supervisor/supervisord.conf
```

---

## الخطوة 3: إضافة مسار مجلد المشروع

### ابحث عن القسم `[include]` في نهاية الملف

سيبدو شكله هكذا:

```ini
[include]
files = /etc/supervisor/conf.d/*.conf
```

### عدّل السطر ليصبح:

```ini
[include]
files = /etc/supervisor/conf.d/*.conf /var/www/DWS_Requisition_Management_System/supervisor/*.conf
```

### ⚠️ إذا لم تجد القسم `[include]`:

أضف هذه الأسطر في نهاية الملف:

```ini
[include]
files = /etc/supervisor/conf.d/*.conf /var/www/DWS_Requisition_Management_System/supervisor/*.conf
```

---

## الخطوة 4: حفظ الملف والخروج

### في nano:
- اضغط `Ctrl + X`
- اضغط `Y` للتأكيد
- اضغط `Enter` للحفظ

### في vim/vi:
- اضغط `Esc`
- اكتب `:wq`
- اضغط `Enter`

---

## الخطوة 5: التحقق من صحة الإعدادات

```bash
# التحقق من syntax الملف
sudo supervisord -c /etc/supervisor/supervisord.conf
```

إذا لم تظهر أخطاء، المتابعة للخطوة التالية.

---

## الخطوة 6: إعادة تشغيل Supervisor

```bash
sudo systemctl restart supervisor
```

أو:

```bash
sudo service supervisor restart
```

---

## الخطوة 7: التحقق من تشغيل Supervisor

```bash
sudo systemctl status supervisor
```

يجب أن ترى:
```
● supervisor.service - Supervisor process control system
   Loaded: loaded (/lib/systemd/system/supervisor.conf; enabled)
   Active: active (running)
```

---

## الخطوة 8: إعادة قراءة الإعدادات

```bash
sudo supervisorctl reread
```

يجب أن ترى:
```
laravel-worker-default: available
laravel-worker-notifications: available
```

---

## الخطوة 9: تطبيق الإعدادات الجديدة

```bash
sudo supervisorctl update
```

يجب أن ترى:
```
laravel-worker-default: added process group
laravel-worker-notifications: added process group
```

---

## الخطوة 10: بدء الـ Workers

```bash
sudo supervisorctl start laravel-worker-default:*
sudo supervisorctl start laravel-worker-notifications:*
```

أو لبدء الكل مرة واحدة:

```bash
sudo supervisorctl start all
```

---

## الخطوة 11: التحقق من الحالة ✅

```bash
sudo supervisorctl status
```

يجب أن ترى شيء مثل:

```
laravel-worker-default:laravel-worker-default_00   RUNNING   pid 12345, uptime 0:00:05
laravel-worker-default:laravel-worker-default_01   RUNNING   pid 12346, uptime 0:00:05
laravel-worker-notifications:laravel-worker-notifications_00   RUNNING   pid 12347, uptime 0:00:05
laravel-worker-notifications:laravel-worker-notifications_01   RUNNING   pid 12348, uptime 0:00:05
```

---

## ✅ تمام! الإعداد اكتمل

الآن Supervisor يراقب مجلد `supervisor/` تلقائياً.

### عند إضافة worker جديد:

1. أضف ملف `.conf` في مجلد `supervisor/`
2. نفذ:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start اسم_البرنامج_الجديد:*
```

---

## 🔧 Troubleshooting - حل المشاكل

### Problem 1: الـ Workers لا تظهر

```bash
# تحقق من المسار الصحيح
ls -la /var/www/DWS_Requisition_Management_System/supervisor/

# تحقق من صلاحيات الملفات
sudo chmod 644 /var/www/DWS_Requisition_Management_System/supervisor/*.conf

# أعد تشغيل Supervisor
sudo systemctl restart supervisor
sudo supervisorctl reread
sudo supervisorctl update
```

---

### Problem 2: خطأ "unix:///var/run/supervisor.sock no such file"

```bash
# أعد تشغيل Supervisor
sudo systemctl restart supervisor

# أو أعد تشغيل السيرفر
sudo reboot
```

---

### Problem 3: الـ Worker في حالة FATAL

```bash
# اعرض الـ logs
sudo supervisorctl tail -f laravel-worker-default

# أو اعرض آخر 100 سطر
sudo supervisorctl tail -100 laravel-worker-default

# تحقق من ملف الـ log
tail -f /var/www/DWS_Requisition_Management_System/storage/logs/queue-default.log
```

**الأسباب الشائعة**:
- المسار غير صحيح في ملف `.conf`
- المستخدم `www-data` ليس له صلاحيات
- مجلد `storage/logs` غير موجود أو ليس له صلاحيات كتابة

**الحل**:
```bash
# إعطاء صلاحيات لمجلد storage
sudo chown -R www-data:www-data /var/www/DWS_Requisition_Management_System/storage
sudo chmod -R 775 /var/www/DWS_Requisition_Management_System/storage
```

---

### Problem 4: الـ Worker يتوقف باستمرار

```bash
# زيادة الـ timeout في ملف .conf
# غيّر timeout=300 إلى timeout=600 أو أكثر

# أعد تحميل الإعدادات
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl restart laravel-worker-default:*
```

---

## 📊 الأوامر المفيدة

```bash
# عرض الحالة
sudo supervisorctl status

# إعادة تشغيل worker معين
sudo supervisorctl restart laravel-worker-default:*

# إيقاف worker
sudo supervisorctl stop laravel-worker-default:*

# بدء worker
sudo supervisorctl start laravel-worker-default:*

# عرض logs مباشرة
sudo supervisorctl tail -f laravel-worker-default

# إعادة تشغيل الكل
sudo supervisorctl restart all

# عرض معلومات عن برنامج معين
sudo supervisorctl status laravel-worker-default:*
```

---

## 🔄 عند نشر تحديثات جديدة (Deployment)

```bash
# إعادة تشغيل جميع الـ Workers لتحميل الكود الجديد
sudo supervisorctl restart all
```

أو أضف هذا إلى deployment script:

```bash
#!/bin/bash
cd /var/www/DWS_Requisition_Management_System
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart all
```

---

## 📚 مصادر إضافية

- [Supervisor Documentation](http://supervisord.org/)
- [Laravel Queue Documentation](https://laravel.com/docs/queues)
- ملف `README.md` - للتفاصيل الكاملة
- ملف `QUICK_START.md` - للبداية السريعة

