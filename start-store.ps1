$ErrorActionPreference = 'Stop'
Set-Location $PSScriptRoot
$phpCommand = Get-Command php -ErrorAction SilentlyContinue
$localPhp = Join-Path $PSScriptRoot '../../work/runtime/php/php.exe'
if ($phpCommand) { $storePhp = $phpCommand.Source }
elseif (Test-Path $localPhp) { $storePhp = (Resolve-Path $localPhp).Path }
else { throw 'Install PHP 8.3+ and Composer; see README.md' }
$env:PATH = (Split-Path $storePhp) + ';' + $env:PATH
& $storePhp artisan serve --host=127.0.0.1 --port=8000 --no-reload
