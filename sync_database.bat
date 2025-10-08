@echo off
echo ==========================================
echo    DATABASE SYNC STARTED
echo ==========================================
echo Date: %date%
echo Time: %time%
echo.

REM Change to the directory where your PHP script is located
cd /d "C:\xampp\htdocs\balaji"

REM Run the PHP script
"C:\xampp\php\php.exe" "incremental_sync.php"

echo.
echo ==========================================
echo    SYNC COMPLETED
echo ==========================================
echo Completed at: %date% %time%
echo.

REM Optional: Keep window open for 30 seconds to see results
timeout /t 30