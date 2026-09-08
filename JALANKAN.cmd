@echo off
setlocal enabledelayedexpansion
title SIAKAD SMA IT Arafah (Lokal)
cd /d D:\SIAKAD-SMAITA

rem ---- pastikan MySQL XAMPP hidup ----
"C:\xampp\mysql\bin\mysqladmin.exe" -uroot ping >nul 2>&1
if errorlevel 1 (
  echo Menyalakan MySQL XAMPP...
  start "" /B "C:\xampp\mysql\bin\mysqld.exe"
  set /a n=0
  :tunggu
  timeout /t 1 /nobreak >nul
  "C:\xampp\mysql\bin\mysqladmin.exe" -uroot ping >nul 2>&1
  if errorlevel 1 (
    set /a n+=1
    if !n! lss 20 goto tunggu
  )
)

start "" http://localhost:8070
php artisan serve --host=0.0.0.0 --port=8070
pause
