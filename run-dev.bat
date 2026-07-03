@echo off
setlocal enabledelayedexpansion
set PATH=c:\laragon\www\rndmanagement\node-temp\node-v22.14.0-win-x64;!PATH!
cd /d c:\laragon\www\rndmanagement
npm run dev
