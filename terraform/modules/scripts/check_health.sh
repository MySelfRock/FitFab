#!/bin/bash
#
# check_health.sh
# Check health of FitFab application and services
#

echo "============================================"
echo "FitFab Health Check"
echo "============================================"

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

check_service() {
    if systemctl is-active --quiet $1; then
        echo -e "${GREEN}✓${NC} $1 is running"
        return 0
    else
        echo -e "${RED}✗${NC} $1 is NOT running"
        return 1
    fi
}

check_port() {
    if nc -z localhost $1 2>/dev/null; then
        echo -e "${GREEN}✓${NC} Port $1 ($2) is open"
        return 0
    else
        echo -e "${RED}✗${NC} Port $1 ($2) is NOT accessible"
        return 1
    fi
}

check_disk() {
    USAGE=$(df -h / | awk 'NR==2 {print $5}' | sed 's/%//')
    if [ $USAGE -lt 80 ]; then
        echo -e "${GREEN}✓${NC} Disk usage: ${USAGE}%"
    elif [ $USAGE -lt 90 ]; then
        echo -e "${YELLOW}⚠${NC} Disk usage: ${USAGE}% (warning)"
    else
        echo -e "${RED}✗${NC} Disk usage: ${USAGE}% (critical)"
    fi
}

check_memory() {
    USAGE=$(free | grep Mem | awk '{printf("%.0f", $3/$2 * 100.0)}')
    if [ $USAGE -lt 80 ]; then
        echo -e "${GREEN}✓${NC} Memory usage: ${USAGE}%"
    elif [ $USAGE -lt 90 ]; then
        echo -e "${YELLOW}⚠${NC} Memory usage: ${USAGE}% (warning)"
    else
        echo -e "${RED}✗${NC} Memory usage: ${USAGE}% (critical)"
    fi
}

echo ""
echo "=== Services ==="
check_service nginx
check_service php8.2-fpm
check_service mysql || check_service postgresql
check_service redis-server
check_service supervisor

echo ""
echo "=== Ports ==="
check_port 80 "HTTP"
check_port 3306 "MySQL" || check_port 5432 "PostgreSQL"
check_port 6379 "Redis"

echo ""
echo "=== System Resources ==="
check_disk
check_memory

echo ""
echo "=== Application ==="
if [ -f "/var/www/fitfab/artisan" ]; then
    echo -e "${GREEN}✓${NC} Application files present"

    # Check Laravel
    cd /var/www/fitfab
    if sudo -u www-data php artisan --version > /dev/null 2>&1; then
        VERSION=$(sudo -u www-data php artisan --version)
        echo -e "${GREEN}✓${NC} Laravel: $VERSION"
    else
        echo -e "${RED}✗${NC} Laravel artisan not working"
    fi

    # Check database connection
    if sudo -u www-data php artisan db:show > /dev/null 2>&1; then
        echo -e "${GREEN}✓${NC} Database connection OK"
    else
        echo -e "${RED}✗${NC} Database connection failed"
    fi

    # Check queue workers
    if pgrep -f "queue:work" > /dev/null; then
        WORKERS=$(pgrep -f "queue:work" | wc -l)
        echo -e "${GREEN}✓${NC} Queue workers running: $WORKERS"
    else
        echo -e "${YELLOW}⚠${NC} No queue workers running"
    fi
else
    echo -e "${RED}✗${NC} Application not installed"
fi

echo ""
echo "=== Recent Errors ==="
if [ -f "/var/www/fitfab/storage/logs/laravel.log" ]; then
    ERROR_COUNT=$(tail -n 100 /var/www/fitfab/storage/logs/laravel.log | grep -c "ERROR" || echo "0")
    if [ $ERROR_COUNT -eq 0 ]; then
        echo -e "${GREEN}✓${NC} No recent errors in application logs"
    else
        echo -e "${YELLOW}⚠${NC} Found $ERROR_COUNT errors in last 100 log lines"
        echo "Run: sudo tail -f /var/www/fitfab/storage/logs/laravel.log"
    fi
else
    echo -e "${YELLOW}⚠${NC} Application log file not found"
fi

echo ""
echo "============================================"
echo "Health check complete"
echo "============================================"
