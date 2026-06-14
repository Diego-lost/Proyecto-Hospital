@echo off
setlocal EnableExtensions
cd /d "%~dp0"

REM Usar siempre el PHP de XAMPP si existe ^(evita otro php.exe en PATH sin pdo_pgsql^).
if exist "C:\xampp\php\php.exe" (
  set "PHPBIN=C:\xampp\php\php.exe"
) else (
  set "PHPBIN=php"
  where php >nul 2>&1
  if errorlevel 1 (
    echo [ERROR] No se encontro C:\xampp\php\php.exe ni "php" en el PATH.
    pause
    exit /b 1
  )
)

"%PHPBIN%" -r "if (!extension_loaded('pdo_pgsql')) { fwrite(STDERR, 'ERROR: pdo_pgsql no esta cargado. Abre verificar-pgsql.bat o activa extension=pdo_pgsql en el php.ini que indica --ini'.PHP_EOL); exit(1);}"
if errorlevel 1 (
  echo.
  "%PHPBIN%" --ini
  echo Ejecuta verificar-pgsql.bat en esta carpeta para mas detalle.
  pause
  exit /b 1
)

echo.
echo === NovaSalud - Panel (login) ===
echo URL: http://127.0.0.1:8000/login
echo.
echo Si ya tienes otra ventana con "php artisan serve", cierrala primero ^(evita varios servidores en el puerto 8000^).
echo Con Apache en su lugar usa: http://localhost/ProyectoNuevo/backend/api/public/login
echo.

REM Ventana aparte: servidor (minimizada). Si el puerto esta ocupado, veras el error ahi.
start "Laravel serve (NovaSalud)" /MIN cmd /k pushd "%~dp0" ^&^& "%PHPBIN%" artisan serve --host=127.0.0.1 --port=8000

timeout /t 3 /nobreak >nul
start "" "http://127.0.0.1:8000/login"

echo Se abrio el navegador. Si la pagina no carga, espera unos segundos y recarga, o mira la ventana "Laravel serve".
echo.
pause
