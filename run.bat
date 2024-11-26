@echo off
cd /d "%~dp0"

title FakerWeb Management Script

:menu
cls
echo.
echo FakerWeb Management Menu
echo ----------------------
echo 1. Start Services
echo 2. Stop Services
echo 3. Check Status
echo 4. View Logs
echo 5. Exit
echo.

set /p choice="Select an option (1-5): "

if "%choice%"=="1" goto start_services
if "%choice%"=="2" goto stop_services
if "%choice%"=="3" goto check_status
if "%choice%"=="4" goto view_logs
if "%choice%"=="5" goto end

echo Invalid option. Please try again.
timeout /t 2 >nul
goto menu

:start_services
cls
echo Starting services...
cd config
docker-compose down --volumes 2>nul
docker-compose up -d --build
cd ..
echo.
echo Services started.
timeout /t 3 >nul
goto menu

:stop_services
cls
echo Stopping services...
cd config
docker-compose down --volumes
cd ..
echo.
echo Services stopped.
timeout /t 3 >nul
goto menu

:check_status
cls
echo Checking services status...
docker ps
echo.
echo Press any key to continue...
pause >nul
goto menu

:view_logs
cls
echo Viewing logs (Press Ctrl+C to exit)...
cd config
docker-compose logs -f
cd ..
goto menu

:end
echo.
echo Goodbye!
timeout /t 2 >nul
