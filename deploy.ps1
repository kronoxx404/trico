param (
    [string]$msg = "Update"
)

Write-Host "Deploying with message: $msg" -ForegroundColor Cyan

git add .
if ($LASTEXITCODE -eq 0) {
    git commit -m "$msg"
    if ($LASTEXITCODE -eq 0) {
        git push origin main
        if ($LASTEXITCODE -eq 0) {
             Write-Host "Deployment Successful!" -ForegroundColor Green
        } else {
             Write-Host "Push Failed" -ForegroundColor Red
        }
    } else {
         Write-Host "Commit Failed (maybe nothing to commit)" -ForegroundColor Yellow
         # Try push anyway just in case
         git push origin main
    }
} else {
    Write-Host "Git Add Failed" -ForegroundColor Red
}
