@echo off
setlocal

set "SSH_USER=u187238888"
set "SSH_HOST=82.29.186.101"
set "SSH_PORT=65002"
set "REMOTE_APP=/home/u187238888/domains/rapidservice.org/public_html/emarket"

echo Deploying eMarket to emarket.rapidservice.org...
ssh -p %SSH_PORT% %SSH_USER%@%SSH_HOST% "set -e; cd %REMOTE_APP%; git pull --ff-only origin main; cd core; composer install --no-dev --no-scripts --optimize-autoloader --no-interaction; php artisan config:clear; php artisan cache:clear; php artisan view:clear; php artisan config:cache; cd ..; if [ ! -e storage ] && [ ! -L storage ]; then ln -s core/storage/app/public storage; fi; if [ ! -e core/public/storage ] && [ ! -L core/public/storage ]; then ln -s ../storage/app/public core/public/storage; fi"

if errorlevel 1 (
    echo Deployment failed.
    exit /b 1
)

echo Deployment completed.
endlocal
