@echo off
title BizTrack System Starter

echo Starting Laravel Backend...
start cmd /k "cd /d D:\biztrack\backend && php artisan serve"

echo Starting Vite Frontend...
start cmd /k "cd /d D:\biztrack\frontend && npm run dev"

echo Starting Ngrok Static Tunnel...
start cmd /k "ngrok http 8000 --url=superior-claim-guiding.ngrok-free.dev"

echo All services are running!