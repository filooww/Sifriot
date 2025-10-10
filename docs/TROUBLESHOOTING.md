# Troubleshooting Guide

## Changes Not Visible in Browser?

If you don't see the changes at http://localhost:8080, follow these steps:

### 1. Clear Browser Cache

**Method 1: Hard Refresh**
- **Windows/Linux**: `Ctrl + Shift + R` or `Ctrl + F5`
- **Mac**: `Cmd + Shift + R`

**Method 2: Clear Browser Cache**
- Open browser DevTools (`F12`)
- Right-click on reload button
- Select "Empty Cache and Hard Reload"

**Method 3: Incognito/Private Mode**
- Open new incognito/private window
- Visit http://localhost:8080

### 2. Clear Laravel Caches

```bash
# Clear all caches at once
docker compose exec web php artisan optimize:clear

# Or clear individually
docker compose exec web php artisan config:clear
docker compose exec web php artisan cache:clear
docker compose exec web php artisan view:clear
docker compose exec web php artisan route:clear
```

### 3. Restart Containers

```bash
# Restart web container
docker compose restart web

# Or restart all containers
docker compose restart

# If nothing works, full rebuild
docker compose down
docker compose up -d --build
```

### 4. Verify Changes Were Applied

```bash
# Check if language switcher is in navigation
docker compose exec web cat /var/www/html/resources/views/livewire/layout/navigation.blade.php | grep "language"

# Check if translation files exist
ls -la lang/

# Check if routes are registered
docker compose exec web php artisan route:list | grep language

# Test in terminal
curl -s http://localhost:8080/publications | grep "EN\|RU"
```

### 5. Check Logs

```bash
# Web server logs
docker compose logs web --tail=50

# Laravel logs
docker compose exec web tail -f storage/logs/laravel.log

# All logs in real-time
docker compose logs -f
```

## Common Issues

### Issue: Still seeing old layout

**Solution:**
1. Hard refresh browser (Ctrl+Shift+R)
2. Clear browser cache completely
3. Try incognito mode

### Issue: Language buttons not visible

**Check:**
```bash
# Verify navigation file
docker compose exec web cat /var/www/html/resources/views/livewire/layout/navigation.blade.php | grep -A 5 "Language Switcher"
```

**Fix:**
```bash
docker compose exec web php artisan view:clear
docker compose restart web
```

### Issue: Clicking EN/RU doesn't change language

**Check:**
```bash
# Verify route exists
docker compose exec web php artisan route:list | grep language

# Check middleware is registered
docker compose exec web cat /var/www/html/bootstrap/app.php | grep SetLocale
```

**Fix:**
```bash
docker compose exec web php artisan config:clear
docker compose exec web php artisan route:clear
docker compose restart web
```

### Issue: Page shows error 500

**Check logs:**
```bash
docker compose logs web --tail=100
docker compose exec web cat storage/logs/laravel.log
```

**Common fixes:**
```bash
# Fix permissions
docker compose exec web chmod -R 775 storage bootstrap/cache

# Regenerate key
docker compose exec web php artisan key:generate

# Clear everything
docker compose exec web php artisan optimize:clear
```

### Issue: Translations not working

**Verify files exist:**
```bash
ls -la lang/
cat lang/en.json | head -5
cat lang/ru.json | head -5
```

**Test in Tinker:**
```bash
docker compose exec web php artisan tinker
>>> app()->setLocale('ru');
>>> __('Publications')
# Should output: "Публикации"
```

### Issue: Database connection error

**Check database container:**
```bash
docker compose ps db
```

**Test connection:**
```bash
docker compose exec db mysql -u dbuser -pdbpass db_manager -e "SELECT 1;"
```

**Check .env settings:**
```bash
cat .env | grep DB_
```

## Quick Reset (Nuclear Option)

If nothing else works:

```bash
# Stop everything
docker compose down -v

# Remove old containers
docker system prune -a

# Rebuild from scratch
docker compose up -d --build

# Clear all caches
docker compose exec web php artisan optimize:clear

# Rebuild frontend
npm run build
```

## Verification Checklist

After applying fixes, verify:

- [ ] Containers are running: `docker compose ps`
- [ ] Web server responds: `curl http://localhost:8080`
- [ ] Routes are registered: `php artisan route:list`
- [ ] Language files exist: `ls -la lang/`
- [ ] Navigation has language switcher: View page source
- [ ] Browser cache cleared: Hard refresh
- [ ] Translations work: Click EN/RU buttons

## Getting Help

If issues persist:

1. **Check logs**: `docker compose logs web --tail=100`
2. **Laravel logs**: `storage/logs/laravel.log`
3. **Check documentation**: [DEVELOPER_GUIDE_RU.md](DEVELOPER_GUIDE_RU.md)
4. **Verify files**: Make sure all files from the implementation are present

## Contact Points

- **Application**: http://localhost:8080
- **phpMyAdmin**: http://localhost:8081
- **Logs Location**: `storage/logs/laravel.log`
- **Docker Logs**: `docker compose logs`

---

## What You Should See

### Navigation Bar (Top Right)

```
[Logo] [Publications] [Dashboard] ... [EN] [RU] [Login] [Register]
```

Or when logged in:

```
[Logo] [Publications] [Dashboard] ... [EN] [RU] [Username ▼]
```

### After Clicking RU

All text should change to Russian:
- "Publications" → "Публикации"
- "Dashboard" → "Панель управления"
- "Search by title..." → "Поиск по названию..."
- etc.

### After Clicking EN

All text should change back to English.

---

**If you still don't see changes after following all steps above, please provide:**
1. Browser console errors (F12 → Console)
2. Output of `docker compose logs web --tail=50`
3. Screenshot of what you see
