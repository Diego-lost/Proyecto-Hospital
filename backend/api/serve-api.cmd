@echo off
REM Arranca la API con el PHP de XAMPP (evita otro php.exe del PATH sin pdo_pgsql).
cd /d "%~dp0"
if exist "C:\xampp\php\php.exe" (
  "C:\xampp\php\php.exe" artisan serve --host=127.0.0.1 --port=8000
) else (
  php artisan serve --host=127.0.0.1 --port=8000
)
