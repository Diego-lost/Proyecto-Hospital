# Vigilancia: copia frontend/ → backend/api/public/clinica (artisan frontend:sync)
# y ejecuta optimize:clear al cambiar app/, routes/ o resources/views/.
#
# Uso desde la raíz del repo (ProyectoNuevo):
#   powershell -ExecutionPolicy Bypass -File tools\dev-sync.ps1

$ErrorActionPreference = "Stop"
$RepoRoot = Split-Path -Parent $PSScriptRoot
$ApiRoot = Join-Path $RepoRoot "backend\api"
$Frontend = Join-Path $RepoRoot "frontend"
$PhpWatchDirs = @(
    (Join-Path $ApiRoot "app")
    (Join-Path $ApiRoot "routes")
    (Join-Path $ApiRoot "resources\views")
)

if (-not (Test-Path (Join-Path $ApiRoot "artisan"))) {
    Write-Error "No se encontró backend\api\artisan. Ejecuta el script con la raíz ProyectoNuevo intacta."
}

function Get-TreeHash {
    param([string]$Root)
    if (-not (Test-Path $Root)) { return "0" }
    $sb = New-Object System.Text.StringBuilder
    Get-ChildItem -Path $Root -Recurse -File -ErrorAction SilentlyContinue |
        Sort-Object FullName |
        ForEach-Object {
            [void]$sb.Append($_.FullName)
            [void]$sb.Append([string]$_.Length)
            [void]$sb.Append($_.LastWriteTimeUtc.Ticks)
        }
    return [string]$sb.ToString().GetHashCode()
}

function Get-PhpTreeHash {
    ($PhpWatchDirs | Where-Object { Test-Path $_ } | ForEach-Object { Get-TreeHash $_ }) -join "|"
}

function Invoke-FrontendSync {
    Push-Location $ApiRoot
    try {
        php artisan frontend:sync
    } finally {
        Pop-Location
    }
}

function Invoke-LaravelRefresh {
    Push-Location $ApiRoot
    try {
        php artisan optimize:clear 2>$null
        Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Laravel: optimize:clear" -ForegroundColor DarkGray
    } finally {
        Pop-Location
    }
}

$debounceFrontendMs = 700
$debouncePhpMs = 900

$stableFrontend = Get-TreeHash $Frontend
$stablePhp = Get-PhpTreeHash
$lastActFrontend = [Environment]::TickCount64
$lastActPhp = [Environment]::TickCount64

Write-Host "=== NovaSalud dev-sync ===" -ForegroundColor Cyan
Write-Host "Repo: $RepoRoot"
Write-Host "Sync: $Frontend → public\clinica | PHP: optimize:clear"
Write-Host "Ctrl+C para salir.`n"

Invoke-FrontendSync
$stableFrontend = Get-TreeHash $Frontend

while ($true) {
    Start-Sleep -Milliseconds 350
    $now = [Environment]::TickCount64

    $hf = Get-TreeHash $Frontend
    if ($hf -ne $stableFrontend) {
        $lastActFrontend = $now
    }
    if (($now - $lastActFrontend) -ge $debounceFrontendMs -and (Get-TreeHash $Frontend) -ne $stableFrontend) {
        Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Cambios en frontend → sync" -ForegroundColor Green
        Invoke-FrontendSync
        $stableFrontend = Get-TreeHash $Frontend
    }

    $hp = Get-PhpTreeHash
    if ($hp -ne $stablePhp) {
        $lastActPhp = $now
    }
    if (($now - $lastActPhp) -ge $debouncePhpMs -and (Get-PhpTreeHash) -ne $stablePhp) {
        Write-Host "[$(Get-Date -Format 'HH:mm:ss')] Cambios en PHP / rutas / vistas → optimize:clear" -ForegroundColor Yellow
        Invoke-LaravelRefresh
        $stablePhp = Get-PhpTreeHash
    }
}
