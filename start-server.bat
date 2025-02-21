@echo off
setlocal enabledelayedexpansion

:: Set PHP path
set "PHP_PATH=C:\Users\user\Documents\SITstuffs\php\php.exe"

:: Check if PHP exists
if not exist "%PHP_PATH%" (
    echo PHP not found at: %PHP_PATH%
    echo Please check your PHP installation
    pause
    exit /b 1
)

:: Add PHP to PATH
set "PATH=%PATH%;C:\Users\user\Documents\SITstuffs\php"

:: Create sessions directory if it doesn't exist
if not exist "sessions" mkdir sessions

:: Display PHP version
echo Testing PHP installation...
"%PHP_PATH%" -v
if !errorlevel! neq 0 (
    echo Failed to run PHP
    pause
    exit /b 1
)

:: Check if port 8000 is in use
netstat -ano | findstr ":8000" > nul
if !errorlevel! equ 0 (
    echo Port 8000 is already in use
    echo Please close any other servers and try again
    pause
    exit /b 1
)

echo.
echo Starting PHP Development Server...
echo Server will be available at http://localhost:8000
echo Press Ctrl+C to stop the server
echo.

:: Start PHP server with router and specific configuration
"%PHP_PATH%" -c "%~dp0php.ini" -S localhost:8000 -t "%~dp0" "%~dp0router.php"

pause 