@echo off
SET PHP_PATH=C:\Users\user\Documents\SITstuffs\php
SET PATH=%PHP_PATH%;%PATH%
echo PHP has been added to PATH
echo Testing PHP installation...
php -v
pause 