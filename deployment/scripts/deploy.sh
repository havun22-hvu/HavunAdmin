#!/bin/bash

##############################################
# HavunAdmin Deployment Script
#
# Usage:
#   ./deploy.sh production
#   ./deploy.sh staging
##############################################

set -e  # Exit on error

# Configuration
ENVIRONMENT=${1:-staging}
YELLOW='\033[1;33m'
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m' # No Color

if [ "$ENVIRONMENT" == "production" ]; then
    APP_DIR="/var/www/havunadmin"
    DOMAIN="admin.havun.nl"
elif [ "$ENVIRONMENT" == "staging" ]; then
    APP_DIR="/var/www/havunadmin-staging"
    DOMAIN="staging-admin.havun.nl"
else
    echo -e "${RED}Error: Invalid environment. Use 'production' or 'staging'${NC}"
    exit 1
fi

echo -e "${YELLOW}=====================================${NC}"
echo -e "${YELLOW}HavunAdmin Deployment - $ENVIRONMENT${NC}"
echo -e "${YELLOW}=====================================${NC}"
echo ""

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}Error: Not in Laravel root directory${NC}"
    exit 1
fi

# 1. Enable Maintenance Mode
echo -e "${YELLOW}[1/10] Enabling maintenance mode...${NC}"
php artisan down || true

# 2. Git Pull
echo -e "${YELLOW}[2/10] Pulling latest code from Git...${NC}"
git pull origin main

# 3. Install/Update Composer Dependencies
echo -e "${YELLOW}[3/10] Installing Composer dependencies...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction

# 4. Install/Update NPM Dependencies
echo -e "${YELLOW}[4/10] Installing NPM dependencies...${NC}"
npm ci

# 5. Build Frontend Assets
echo -e "${YELLOW}[5/10] Building frontend assets...${NC}"
npm run build

# 6. Run Database Migrations
echo -e "${YELLOW}[6/10] Running database migrations...${NC}"
php artisan migrate --force

# 7. Clear and Cache Config
echo -e "${YELLOW}[7/10] Clearing and caching configuration...${NC}"
php artisan config:clear
php artisan config:cache

# 8. Clear and Cache Routes
echo -e "${YELLOW}[8/10] Clearing and caching routes...${NC}"
php artisan route:clear
php artisan route:cache

# 9. Clear and Cache Views
echo -e "${YELLOW}[9/10] Clearing and caching views...${NC}"
php artisan view:clear
php artisan view:cache

# 10. Set Permissions
echo -e "${YELLOW}[10/10] Setting file permissions...${NC}"
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# Disable Maintenance Mode
echo -e "${YELLOW}Disabling maintenance mode...${NC}"
php artisan up

echo ""
echo -e "${GREEN}=====================================${NC}"
echo -e "${GREEN}✓ Deployment Successful!${NC}"
echo -e "${GREEN}=====================================${NC}"
echo -e "${GREEN}Environment: $ENVIRONMENT${NC}"
echo -e "${GREEN}URL: https://$DOMAIN${NC}"
echo ""
