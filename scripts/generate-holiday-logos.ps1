param(
    [string]$SourceDirectory = (Join-Path $PSScriptRoot '..\public\images\holiday\logos'),
    [string]$Python = (Join-Path $env:USERPROFILE '.cache\codex-runtimes\codex-primary-runtime\dependencies\python\python.exe')
)

$ErrorActionPreference = 'Stop'

$ProjectRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$Extractor = Join-Path $PSScriptRoot 'extract-reference-holiday-logos.py'
$VTracerPath = Join-Path $ProjectRoot 'storage\app\python-tools'
$OutputDirectory = Join-Path $ProjectRoot 'public\images\holiday\logos'
$ArtifactDirectory = Join-Path $ProjectRoot 'storage\app\holiday-reference-crops'

if (-not (Test-Path -LiteralPath $Python -PathType Leaf)) {
    throw "Python runtime not found: $Python"
}

if (-not (Test-Path -LiteralPath (Join-Path $VTracerPath 'vtracer') -PathType Container)) {
    throw "VTracer is missing. Install it with: $Python -m pip install --target `"$VTracerPath`" vtracer==0.6.15"
}

& $Python $Extractor `
    --source-dir $SourceDirectory `
    --out-dir $OutputDirectory `
    --artifact-dir $ArtifactDirectory `
    --vtracer-path $VTracerPath

if ($LASTEXITCODE -ne 0) {
    throw "Holiday logo generation failed with exit code $LASTEXITCODE"
}

Write-Host 'Generated six path-only SVG holiday logos from the approved reference crops.'
