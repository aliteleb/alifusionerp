#!/bin/bash

# Supervisor Auto-Discovery Setup Script
# هذا السكريبت يجعل Supervisor يكتشف تلقائياً ملفات configuration من مجلد المشروع

APP_PATH="/var/www/alifusionerp"
SUPERVISOR_CONF="/etc/supervisor/supervisord.conf"
SUPERVISOR_PROJECT_PATH="$APP_PATH/supervisor/*.conf"

echo "=========================================="
echo "Supervisor Auto-Discovery Setup"
echo "=========================================="
echo ""

# التحقق من صلاحيات root
if [ "$EUID" -ne 0 ]; then 
    echo "⚠️  يجب تشغيل هذا السكريبت بصلاحيات root"
    echo "استخدم: sudo bash $0"
    exit 1
fi

# التحقق من وجود Supervisor
if ! command -v supervisorctl &> /dev/null; then
    echo "❌ Supervisor غير مثبت على السيرفر"
    echo "لتثبيته: sudo apt install supervisor -y"
    exit 1
fi

# التحقق من وجود مجلد supervisor في المشروع
if [ ! -d "$APP_PATH/supervisor" ]; then
    echo "❌ مجلد supervisor غير موجود في: $APP_PATH"
    exit 1
fi

echo "✅ تم العثور على مجلد supervisor في المشروع"
echo ""

# عمل backup لملف supervisord.conf
echo "📦 عمل backup لملف supervisord.conf..."
cp $SUPERVISOR_CONF ${SUPERVISOR_CONF}.backup.$(date +%Y%m%d_%H%M%S)
echo "✅ تم حفظ Backup في: ${SUPERVISOR_CONF}.backup.$(date +%Y%m%d_%H%M%S)"
echo ""

# التحقق إذا كان المسار موجود بالفعل
if grep -q "$SUPERVISOR_PROJECT_PATH" "$SUPERVISOR_CONF"; then
    echo "ℹ️  المسار موجود بالفعل في supervisord.conf"
    echo "   لا حاجة لإضافته مرة أخرى"
else
    echo "📝 إضافة المسار إلى supervisord.conf..."
    
    # البحث عن [include] section
    if grep -q "^\[include\]" "$SUPERVISOR_CONF"; then
        # إذا كان موجود، نضيف المسار للـ files line
        sed -i "/^\[include\]/,/^files = / s|^files = \(.*\)|files = \1 $SUPERVISOR_PROJECT_PATH|" "$SUPERVISOR_CONF"
        echo "✅ تم إضافة المسار إلى [include] section الموجود"
    else
        # إذا لم يكن موجود، نضيفه في نهاية الملف
        echo "" >> "$SUPERVISOR_CONF"
        echo "[include]" >> "$SUPERVISOR_CONF"
        echo "files = /etc/supervisor/conf.d/*.conf $SUPERVISOR_PROJECT_PATH" >> "$SUPERVISOR_CONF"
        echo "✅ تم إنشاء [include] section جديد"
    fi
fi

echo ""
echo "🔄 إعادة تشغيل Supervisor..."
systemctl restart supervisor

# الانتظار قليلاً حتى يعيد التشغيل
sleep 2

echo ""
echo "📋 إعادة تحميل الإعدادات..."
supervisorctl reread
supervisorctl update

echo ""
echo "=========================================="
echo "✨ تم الإعداد بنجاح!"
echo "=========================================="
echo ""
echo "📊 حالة الـ Workers الحالية:"
supervisorctl status

echo ""
echo "📝 لبدء الـ Workers:"
echo "   sudo supervisorctl start laravel-worker-default:*"
echo "   sudo supervisorctl start laravel-worker-notifications:*"
echo ""
echo "📝 للتحقق من الحالة:"
echo "   sudo supervisorctl status"
echo ""
echo "✅ الآن أي ملف .conf تضيفه في $APP_PATH/supervisor سيتم اكتشافه تلقائياً!"
echo "   بعد إضافة ملفات جديدة، نفذ: sudo supervisorctl reread && sudo supervisorctl update"
echo ""

