$ErrorActionPreference = "SilentlyContinue"
$proc = Start-Process "node" -ArgumentList "C:\Users\carac\OneDrive\Pictures\Documents\PWD_website\server.js" -WindowStyle Hidden -PassThru
Start-Sleep 2
if (!$proc.HasExited) { Write-Host "Server running PID: $($proc.Id)" }