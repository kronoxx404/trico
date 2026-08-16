# subir.ps1 — Script de Despliegue Rápido a Cualquier Repositorio de GitHub
param (
    [string]$RepoUrl = "",
    [string]$AuthorName = "kronoxx404",
    [string]$AuthorEmail = "jadercastillo795@gmail.com"
)

Write-Host "==================================================" -ForegroundColor Yellow
Write-Host " 🚀 SCRIPT DE DESPLIEGUE RÁPIDO A NUEVO REPOSITORIO" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Yellow

if ([string]::IsNullOrWhiteSpace($RepoUrl)) {
    $RepoUrl = Read-Host "👉 Ingresa la URL del repositorio de GitHub (ejemplo: https://github.com/kronoxx404/tricoca-n.git)"
}

if ([string]::IsNullOrWhiteSpace($RepoUrl)) {
    Write-Host "❌ Error: La URL del repositorio no puede estar vacía." -ForegroundColor Red
    exit 1
}

Write-Host "`n1. Configurando autor del commit ($AuthorEmail)..." -ForegroundColor Green
git config user.name "$AuthorName"
git config user.email "$AuthorEmail"

Write-Host "`n2. Configurando destino de Git: $RepoUrl ..." -ForegroundColor Green
git remote set-url origin $RepoUrl
if ($LASTEXITCODE -ne 0) {
    git remote add origin $RepoUrl
}

Write-Host "`n3. Preparando archivos y empaquetando proyecto..." -ForegroundColor Green
git checkout --orphan clean_push_branch
git add -A

Write-Host "`n4. Creando commit con el autor oficial de GitHub..." -ForegroundColor Green
git commit --author="$AuthorName <$AuthorEmail>" -m "initial clean commit for new repository deployment"
git branch -D main
git branch -m main

Write-Host "`n5. Subiendo cambios a GitHub ($RepoUrl)..." -ForegroundColor Green
git push -u origin main --force

if ($LASTEXITCODE -eq 0) {
    Write-Host "`n==================================================" -ForegroundColor Yellow
    Write-Host " ✅ ¡PROYECTO DESPLEGADO EXITOSAMENTE!" -ForegroundColor Green
    Write-Host " 👤 Autor: $AuthorName <$AuthorEmail>" -ForegroundColor Green
    Write-Host " 🌐 Repositorio: $RepoUrl" -ForegroundColor Cyan
    Write-Host "==================================================" -ForegroundColor Yellow
} else {
    Write-Host "`n❌ Error durante el push. Verifica tus permisos en GitHub." -ForegroundColor Red
}
