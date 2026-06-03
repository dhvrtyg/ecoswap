# EcoSwap Startup Script
# This script downloads portable PHP, configures php.ini, and starts the development server.

$phpDir = Join-Path $PSScriptRoot "php_runtime"
$zipFile = Join-Path $PSScriptRoot "php-8.2.31-nts-Win32-vs16-x64.zip"
$phpExe = Join-Path $phpDir "php.exe"

if (-not (Test-Path $phpExe)) {
    Write-Host "Portable PHP not found. Downloading PHP 8.2.31..." -ForegroundColor Cyan
    if (-not (Test-Path $phpDir)) {
        New-Item -ItemType Directory -Path $phpDir | Out-Null
    }
    
    # Download PHP using curl
    $url = "https://windows.php.net/downloads/releases/php-8.2.31-nts-Win32-vs16-x64.zip"
    Write-Host "Running: curl.exe -L -o `"$zipFile`" `"$url`"" -ForegroundColor Yellow
    curl.exe -L -o $zipFile $url
    
    if (-not (Test-Path $zipFile) -or (Get-Item $zipFile).Length -lt 1000000) {
        Write-Error "Failed to download PHP zip package."
        exit 1
    }
    
    Write-Host "Extracting PHP..." -ForegroundColor Cyan
    Expand-Archive -Path $zipFile -DestinationPath $phpDir -Force
    
    # Clean up zip file
    Remove-Item $zipFile -Force
}

# Configure php.ini if not exists
$phpIni = Join-Path $phpDir "php.ini"
if (-not (Test-Path $phpIni)) {
    Write-Host "Configuring php.ini..." -ForegroundColor Cyan
    $devIni = Join-Path $phpDir "php.ini-development"
    if (Test-Path $devIni) {
        Copy-Item $devIni $phpIni
    } else {
        # Create a basic ini if not found
        New-Item -ItemType File -Path $phpIni | Out-Null
    }
    
    # Enable extensions
    $iniContent = Get-Content $phpIni
    
    # Enable extension_dir
    $iniContent = $iniContent -replace ';extension_dir = "ext"', 'extension_dir = "ext"'
    # Enable standard extensions
    $iniContent = $iniContent -replace ';extension=pdo_sqlite', 'extension=pdo_sqlite'
    $iniContent = $iniContent -replace ';extension=sqlite3', 'extension=sqlite3'
    $iniContent = $iniContent -replace ';extension=mbstring', 'extension=mbstring'
    $iniContent = $iniContent -replace ';extension=openssl', 'extension=openssl'
    $iniContent = $iniContent -replace ';extension=curl', 'extension=curl'
    $iniContent = $iniContent -replace ';extension=fileinfo', 'extension=fileinfo'
    $iniContent = $iniContent -replace ';extension=gd', 'extension=gd'
    
    # Set upload size limits
    $iniContent = $iniContent -replace 'upload_max_filesize = 2M', 'upload_max_filesize = 10M'
    $iniContent = $iniContent -replace 'post_max_size = 8M', 'post_max_size = 12M'
    
    Set-Content $phpIni -Value $iniContent
}

Write-Host "Starting EcoSwap on http://localhost:8000 ..." -ForegroundColor Green
Start-Process -FilePath $phpExe -ArgumentList "-S", "localhost:8000" -NoNewWindow
