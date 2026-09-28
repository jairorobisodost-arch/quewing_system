@echo off
REM ============================================
REM  SFI Queuing System - Direct Print Launcher
REM  Opens the admin dashboard in Chrome with
REM  kiosk printing enabled (no print dialog).
REM  Prints go straight to the DEFAULT printer.
REM ============================================

set CHROME="C:\Program Files\Google\Chrome\Application\chrome.exe"
if not exist %CHROME% set CHROME="C:\Program Files (x86)\Google\Chrome\Application\chrome.exe"
if not exist %CHROME% set CHROME="C:\Program Files\Microsoft\Edge\Application\msedge.exe"
if not exist %CHROME% set CHROME="C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe"

if not exist %CHROME% (
    echo Chrome or Edge not found. Please install Google Chrome first.
    pause
    exit /b 1
)

start "" %CHROME% --kiosk-printing --new-window "http://localhost/quewing_system/admin/dashboard.php"
exit /b 0
