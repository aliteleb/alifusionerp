@echo off
echo ============================================
echo Chat Broadcasting Setup Script
echo ============================================
echo.

echo Step 1: Installing npm packages...
call npm install --save laravel-echo pusher-js
echo.

echo Step 2: Building assets...
call npm run build
echo.

echo Step 3: Clearing Laravel cache...
call php artisan config:clear
call php artisan cache:clear
call php artisan route:clear
echo.

echo ============================================
echo Setup Complete!
echo ============================================
echo.
echo Next steps:
echo 1. Make sure you added Pusher credentials to .env file
echo    (see BROADCASTING_CONFIG.txt)
echo.
echo 2. Start queue worker in a separate terminal:
echo    php artisan queue:work
echo.
echo 3. Access your admin panel and go to Chat
echo.
echo 4. Start chatting in real-time!
echo.
echo ============================================
pause
