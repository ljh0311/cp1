@echo off
echo Starting PHP Development Server...

:: Set PHP path
set PHP_PATH=C:\Users\user\Documents\SITstuffs\php\php.exe

:: Check if PHP exists
if not exist "%PHP_PATH%" (
    echo PHP not found at: %PHP_PATH%
    echo Please check your PHP installation
    pause
    exit /b 1
)

:: Kill any existing PHP processes on port 8000
for /f "tokens=5" %%a in ('netstat -aon ^| findstr ":8000"') do (
    taskkill /F /PID %%a 2>NUL
)

:: Start PHP server
echo Server starting at http://localhost:8000
echo Press Ctrl+C to stop the server
"%PHP_PATH%" -S localhost:8000

pause 