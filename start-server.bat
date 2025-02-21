@echo off
SET PHP_PATH=C:\Users\user\Documents\SITstuffs\php\php.exe
echo Starting PHP Development Server...
echo Testing PHP configuration...
"%PHP_PATH%" -v
echo.
echo Starting server at http://localhost:8000
"%PHP_PATH%" -c php.ini -S localhost:8000
pause 