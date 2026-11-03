@echo off
REM Sinkronisasi data wisata dari OpenStreetMap.
REM Jalankan manual: sync_osm.bat
REM Batasi jumlah objek untuk uji coba: sync_osm.bat 50
cd /d "%~dp0"
"C:\xampp\php\php.exe" index.php sync run %1 >> "application\logs\osm_sync.log" 2>&1
