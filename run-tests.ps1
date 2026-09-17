$scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Definition
$phpPath = 'C:\xampp\php\php.exe'

if (-not (Test-Path $phpPath)) {
    $phpCommand = Get-Command php -ErrorAction SilentlyContinue | Select-Object -ExpandProperty Source -ErrorAction SilentlyContinue
    if (-not $phpCommand) {
        Write-Error 'PHP CLI not found. Install PHP or add it to PATH, or install XAMPP with PHP.'
        exit 1
    }
    $phpPath = $phpCommand
}

$testScript = Join-Path $scriptRoot 'tests\run-tests.php'
if (-not (Test-Path $testScript)) {
    Write-Error "Test script not found: $testScript"
    exit 1
}

Write-Host "Running tests with PHP: $phpPath"
& $phpPath -f $testScript
exit $LASTEXITCODE
