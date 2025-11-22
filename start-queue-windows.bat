@echo off
REM Set OpenSSL Config for Windows
set OPENSSL_CONF=C:\Users\Ali\.config\herd\bin\php83\extras\ssl\openssl.cnf

echo ====================================
echo Starting Queue Worker with OpenSSL
echo ====================================
echo OpenSSL Config: %OPENSSL_CONF%
echo ====================================
echo.

REM Start Queue Worker
php artisan queue:work --queue=notifications --verbose --tries=3

