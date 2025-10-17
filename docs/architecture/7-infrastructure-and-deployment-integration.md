# 7. Infrastructure and Deployment Integration

## 7.1 Enhanced docker-compose.yml

**New Containers:**
- `literature_queue` - Queue worker (`php artisan queue:work --tries=3 --timeout=600`)
- `literature_scheduler` - Cron scheduler (runs `schedule:run` every 60 seconds)

**Volume Mounts:**
- `/mnt/library-storage:/var/www/html/storage/app/content` - Local disk (1.1TB)

**Healthcheck Directives:**
```yaml
healthcheck:
  test: ["CMD", "php", "artisan", "health:check"]
  interval: 30s
  timeout: 10s
  retries: 3
```

## 7.2 Deployment Strategy

**Deployment Script (deploy.sh):**
```bash
# Stop containers
docker compose down

# Database backup (automated)
docker compose exec literature_db mysqldump -u root -p$DB_PASSWORD literature > backup_$(date +%Y%m%d_%H%M%S).sql

# Update dependencies
docker compose run --rm literature_web composer install --optimize-autoloader --no-dev
docker compose run --rm literature_web npm ci && npm run build

# Run migrations
docker compose run --rm literature_web php artisan migrate --force

# Clear caches
docker compose run --rm literature_web php artisan optimize:clear
docker compose run --rm literature_web php artisan config:cache

# Restart
docker compose up -d
```

## 7.3 Monitoring

**Health Check Endpoint (`/health`):**
- Checks database connection, storage accessibility, queue worker heartbeat
- Returns 200 (healthy) or 503 (unhealthy)

**Colored Logs:**
- Custom `ColoredLineFormatter` with ANSI codes
- `folder-scan.log` - Green (info), Yellow (warning), Red (error)
- `file-sync.log` - Cyan (info), Yellow (warning), Red (error)

**Queue Monitoring:**
- Queue worker heartbeat via cache every 60s
- Admin dashboard shows failed jobs count

**Scheduled Tasks:**
- File integrity check (configurable: daily, weekly, hourly)
- Cleanup old logs (keep 90 days)
- Engagement metrics reconciliation (daily)
- Database backup (weekly)

---
