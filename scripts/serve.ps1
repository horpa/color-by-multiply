$ErrorActionPreference = "Stop"

$Root = Split-Path -Parent $PSScriptRoot
Set-Location $Root

function Find-Php {
    $php = Get-Command php -ErrorAction SilentlyContinue
    if ($php) {
        return $php.Source
    }

    $candidates = @(
        "$env:LOCALAPPDATA\Programs\PHP\php.exe",
        "C:\php\php.exe",
        "C:\tools\php\php.exe"
    )

    foreach ($candidate in $candidates) {
        if (Test-Path $candidate) {
            return $candidate
        }
    }

    return $null
}

$phpExe = Find-Php

if (-not $phpExe) {
    Write-Host "PHP was not found on PATH." -ForegroundColor Red
    Write-Host ""
    Write-Host "Install PHP 8.1+ with gd and fileinfo, then run this script again."
    Write-Host ""
    Write-Host "Options:"
    Write-Host "  winget install PHP.PHP.8.3"
    Write-Host "  scoop install php"
    Write-Host ""
    Write-Host "Or use Docker instead:"
    Write-Host "  docker compose up --build"
    exit 1
}

Write-Host "Using PHP: $phpExe"
& $phpExe scripts/check.php
if ($LASTEXITCODE -ne 0) {
    exit $LASTEXITCODE
}

Write-Host ""
Write-Host "Starting server at http://localhost:8080" -ForegroundColor Green
Write-Host "Press Ctrl+C to stop."
Write-Host ""

& $phpExe -S localhost:8080 -t public
