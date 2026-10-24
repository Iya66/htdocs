@echo off
echo Starting Cloudflare Public Tunnel for XAMPP (http://localhost:80)...
echo.
C:\xampp\cloudflared.exe tunnel --url http://localhost:80
pause
