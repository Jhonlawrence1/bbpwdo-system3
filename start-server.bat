@echo off
cd /d "C:\Users\carac\OneDrive\Pictures\Documents\PWD_website"
start /b node server.js > server.log 2>&1
echo Server started
timeout /t 3