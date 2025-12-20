@echo off
title Hotel Cecil - Sistema de Gestion
color 0A

echo.
echo ========================================
echo   Hotel Cecil - Sistema de Gestion
echo ========================================
echo.
echo Iniciando sistema...
echo.

REM Verificar si Apache esta corriendo
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] Apache esta corriendo
) else (
    echo [!] Iniciando Apache...
    start "" "C:\xampp\xampp_start.exe"
    timeout /t 3 /nobreak >nul
)

echo.
echo Abriendo Hotel Cecil...
echo.

REM Abrir en el navegador predeterminado
start "" "http://localhost/Sistem Hotel Cecil"

REM Esperar 2 segundos y cerrar la ventana
timeout /t 2 /nobreak >nul
exit
