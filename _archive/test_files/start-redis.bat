@echo off
REM Start Redis Server for Laragon
echo Starting Redis Server...
start "" /MIN "C:\laragon\bin\redis\redis-x64-5.0.14.1\redis-server.exe" "C:\laragon\bin\redis\redis-x64-5.0.14.1\redis.windows.conf"
echo Redis Server started!
timeout /t 2 /nobreak >nul
exit
