@echo off
setlocal
echo === Comprobar PostgreSQL en PHP (pdo_pgsql) ===
echo.

if exist "C:\xampp\php\php.exe" (
  set "PHPBIN=C:\xampp\php\php.exe"
  echo Usando: %PHPBIN%
) else (
  set "PHPBIN=php"
  echo Usando: php del PATH ^(where^)
  where php
)

echo.
"%PHPBIN%" --ini
echo.
"%PHPBIN%" -r "echo extension_loaded('pdo_pgsql') ? 'pdo_pgsql: CARGADO' : 'pdo_pgsql: NO CARGADO (descomenta extension=pdo_pgsql en el php.ini de arriba)'; echo PHP_EOL;"
echo.
"%PHPBIN%" -r "echo extension_loaded('pgsql') ? 'pgsql: CARGADO' : 'pgsql: no'; echo PHP_EOL;"
echo.
pause
