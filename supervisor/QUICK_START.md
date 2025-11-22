# 🚀 Quick Start - Supervisor Configuration

## طريقة التثبيت السريعة (موصى بها)

### استخدام السكريبت التلقائي:

```bash
# على السيرفر، نفذ:
cd /var/www/DWS_Requisition_Management_System
sudo bash supervisor/setup.sh
```

هذا السكريبت سيقوم بـ:
- ✅ عمل backup لملف supervisord.conf
- ✅ إضافة مسار مجلد supervisor إلى الإعدادات
- ✅ إعادة تشغيل Supervisor
- ✅ عرض حالة الـ Workers

---

## بدء الـ Workers

```bash
sudo supervisorctl start laravel-worker-default:*
sudo supervisorctl start laravel-worker-notifications:*
```

---

## التحقق من الحالة

```bash
sudo supervisorctl status
```

---

## إضافة Workers جديدة

1. أضف ملف `.conf` جديد في مجلد `supervisor/`
2. نفذ:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start اسم_البرنامج:*
```

✨ **هذا كل شيء!** - الـ Auto-discovery يعمل تلقائياً

---

## الأوامر الأساسية

| الأمر | الوصف |
|-------|--------|
| `sudo supervisorctl status` | عرض حالة جميع الـ Workers |
| `sudo supervisorctl start all` | بدء جميع الـ Workers |
| `sudo supervisorctl stop all` | إيقاف جميع الـ Workers |
| `sudo supervisorctl restart all` | إعادة تشغيل جميع الـ Workers |
| `sudo supervisorctl tail -f برنامج` | متابعة logs الـ Worker |
| `sudo supervisorctl reread` | قراءة الإعدادات الجديدة |
| `sudo supervisorctl update` | تطبيق الإعدادات الجديدة |

---

## Troubleshooting

### إذا لم تظهر الـ Workers:

```bash
# تحقق من صحة ملفات الـ configuration
sudo supervisorctl reread

# إذا ظهرت أخطاء، تحقق من syntax الملفات
cat supervisor/laravel-worker-default.conf

# إعادة تشغيل Supervisor
sudo systemctl restart supervisor
sudo supervisorctl status
```

### للتحقق من الـ Logs:

```bash
# Default queue
tail -f storage/logs/queue-default.log

# Notifications queue  
tail -f storage/logs/queue-notifications.log

# Supervisor logs
tail -f /var/log/supervisor/supervisord.log
```

