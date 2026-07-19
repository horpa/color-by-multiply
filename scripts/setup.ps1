$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

Write-Host "Color by Multiply - local setup" -ForegroundColor Cyan
Write-Host ""

$dirs = @(
    "uploads",
    "storage/worksheets"
)

foreach ($dir in $dirs) {
    $path = Join-Path $Root $dir
    if (-not (Test-Path $path)) {
        New-Item -ItemType Directory -Path $path | Out-Null
        Write-Host "Created $dir/"
    } else {
        Write-Host "Exists  $dir/"
    }
}

$php = Get-Command php -ErrorAction SilentlyContinue

if (-not $php) {
    Write-Host ""
    Write-Host "PHP is not installed yet." -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Recommended install on Windows:"
    Write-Host "  winget install PHP.PHP.8.3"
    Write-Host ""
    Write-Host "Then enable extensions in php.ini:"
    Write-Host "  extension=gd"
    Write-Host "  extension=fileinfo"
    Write-Host ""
    Write-Host "Restart the terminal and run:"
    Write-Host "  .\scripts\serve.ps1"
    Write-Host ""
    Write-Host "Alternative: Docker"
    Write-Host "  docker compose up --build"
    exit 0
}

Write-Host ""
Write-Host "Running environment check..."
& php scripts/check.php

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "Setup complete. Start the app with:"
    Write-Host "  .\scripts\serve.ps1"
}
