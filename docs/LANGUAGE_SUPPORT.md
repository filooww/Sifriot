# Multi-Language Support Documentation

## Overview

The Literature Database application now supports **English** and **Russian** languages with seamless switching capabilities.

---

## Features

✅ **Dual Language Support**: English and Russian
✅ **Language Switcher**: Available in navigation bar
✅ **Persistent Selection**: Language choice saved in session
✅ **Full Translation**: All UI elements translated
✅ **Responsive Design**: Language switcher works on mobile and desktop

---

## How It Works

### Architecture

```
User clicks language → Route updates session → Middleware sets locale → UI displays in selected language
```

### Components

1. **Translation Files**: `lang/en.json` and `lang/ru.json`
2. **Middleware**: `app/Http/Middleware/SetLocale.php`
3. **Route**: `/language/{locale}` in `routes/web.php`
4. **UI**: Language switcher in navigation
5. **Session**: Language stored in `session('locale')`

---

## Files Modified/Created

### Created Files

- **lang/en.json** - English translations
- **lang/ru.json** - Russian translations (Русские переводы)
- **app/Http/Middleware/SetLocale.php** - Locale middleware
- **docs/DEVELOPER_GUIDE_RU.md** - Comprehensive Russian documentation for developers

### Modified Files

- **bootstrap/app.php** - Registered SetLocale middleware
- **routes/web.php** - Added language switch route
- **resources/views/livewire/layout/navigation.blade.php** - Added language switcher UI

---

## Usage

### For Users

**Desktop:**
- Click **EN** or **RU** buttons in the top navigation bar
- The page refreshes with the selected language

**Mobile:**
- Open the hamburger menu
- Scroll down to the language section
- Tap **English** or **Русский**

### For Developers

#### Using Translations in Blade Templates

```blade
{{-- Basic translation --}}
{{ __('Publications') }}

{{-- With parameters --}}
{{ __('Welcome back, :name', ['name' => $userName]) }}

{{-- In attributes --}}
<input placeholder="{{ __('Search by title...') }}">
```

#### Using Translations in PHP

```php
// In controllers or Livewire components
$message = __('Publications');

// With parameters
$greeting = __('Welcome back, :name', ['name' => $user->name]);

// Check current locale
$locale = app()->getLocale();  // 'en' or 'ru'

// Set locale programmatically
app()->setLocale('ru');
```

#### Adding New Translations

1. **Open translation file**: `lang/en.json` or `lang/ru.json`

2. **Add translation pair**:

```json
{
    "New Text": "New Text",           // English
    "Новый текст": "Новый текст"      // Russian
}
```

3. **Use in template**:

```blade
{{ __('New Text') }}
```

4. **Clear cache**:

```bash
php artisan config:clear
php artisan view:clear
```

---

## Translation Coverage

### Fully Translated Sections

- ✅ Navigation menu
- ✅ Publications list
- ✅ Search and filters
- ✅ Dashboard
- ✅ Authentication pages (via Breeze)
- ✅ Action buttons
- ✅ Error messages
- ✅ Empty states

### Sample Translations

| English | Russian |
|---------|---------|
| Publications | Публикации |
| Dashboard | Панель управления |
| Search by title... | Поиск по названию... |
| Add New | Добавить |
| Show Active | Показать активные |
| Show Deleted | Показать удалённые |
| Authors | Авторы |
| Publisher | Издательство |
| Year | Год |
| Actions | Действия |
| View | Просмотр |
| Edit | Редактировать |
| Delete | Удалить |
| Restore | Восстановить |

---

## Technical Implementation

### Middleware Logic

```php
// app/Http/Middleware/SetLocale.php

public function handle(Request $request, Closure $next): Response
{
    // Get locale from session, default to config
    $locale = session('locale', config('app.locale'));

    // Validate locale (only 'en' and 'ru' allowed)
    if (!in_array($locale, ['en', 'ru'])) {
        $locale = config('app.locale');
    }

    // Set application locale
    app()->setLocale($locale);

    return $next($request);
}
```

### Language Switch Route

```php
// routes/web.php

Route::get('/language/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'ru'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
})->name('language.switch');
```

### Navigation UI

```blade
<!-- Desktop Language Switcher -->
<div class="flex items-center space-x-2">
    <a href="{{ route('language.switch', 'en') }}"
       class="px-2 py-1 text-sm rounded {{ app()->getLocale() == 'en' ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
        EN
    </a>
    <a href="{{ route('language.switch', 'ru') }}"
       class="px-2 py-1 text-sm rounded {{ app()->getLocale() == 'ru' ? 'bg-blue-600 text-white' : 'text-gray-700' }}">
        RU
    </a>
</div>
```

---

## Adding New Languages

To add a new language (e.g., German):

### 1. Create Translation File

```bash
cat > lang/de.json << 'EOF'
{
    "Publications": "Veröffentlichungen",
    "Dashboard": "Dashboard",
    ...
}
EOF
```

### 2. Update Middleware

```php
// app/Http/Middleware/SetLocale.php

if (!in_array($locale, ['en', 'ru', 'de'])) {  // Add 'de'
    $locale = config('app.locale');
}
```

### 3. Update Navigation

```blade
<a href="{{ route('language.switch', 'de') }}" class="...">
    DE
</a>
```

### 4. Clear Cache

```bash
php artisan config:clear
php artisan view:clear
```

---

## Testing

### Manual Testing

1. **Open application**: http://localhost:8080
2. **Click EN button**: Should display in English
3. **Click RU button**: Should display in Russian
4. **Navigate pages**: Language should persist
5. **Test mobile**: Open menu, change language
6. **Check all pages**: Dashboard, Publications, Profile

### Verification Commands

```bash
# Check route exists
php artisan route:list | grep language

# Verify translation files
ls -la lang/

# Test in Tinker
php artisan tinker
>>> app()->setLocale('ru');
>>> __('Publications')
=> "Публикации"
```

---

## Developer Documentation

Comprehensive Russian documentation for developers is available at:

📘 **[docs/DEVELOPER_GUIDE_RU.md](DEVELOPER_GUIDE_RU.md)**

This guide includes:
- Architecture explanation in Russian
- Migration from procedural PHP to Laravel
- Folder structure breakdown
- Database operations (Eloquent ORM)
- Livewire components tutorial
- Common development tasks
- Debugging and testing

---

## Troubleshooting

### Language not changing?

1. Clear all caches:
```bash
php artisan optimize:clear
```

2. Check session configuration in `.env`:
```env
SESSION_DRIVER=file
```

3. Verify middleware is registered in `bootstrap/app.php`

### Translations not showing?

1. Verify translation key exists in `lang/{locale}.json`
2. Check file syntax (valid JSON)
3. Clear view cache:
```bash
php artisan view:clear
```

### Wrong language on page load?

1. Check `config/app.php`:
```php
'locale' => env('APP_LOCALE', 'en'),
'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),
```

2. Set in `.env`:
```env
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
```

---

## Best Practices

1. **Always use translation helpers**: `__('text')` instead of hardcoded strings
2. **Keep translations organized**: Group related translations
3. **Use consistent naming**: Use the English text as the key
4. **Test both languages**: Verify UI looks good in both languages
5. **Consider text length**: Russian text is often longer than English
6. **Update documentation**: Add new translations to this file

---

## Performance

- **No overhead**: Translations loaded only once per request
- **Cached**: Laravel caches translation files in production
- **Session-based**: Minimal storage (just locale code)
- **Fast switching**: No page reload needed (full page refresh for consistency)

---

## Future Enhancements

Potential improvements for consideration:

- [ ] Browser language detection (auto-select on first visit)
- [ ] User preference storage (database-backed for authenticated users)
- [ ] More languages (German, French, Spanish)
- [ ] RTL support (Arabic, Hebrew)
- [ ] Date/time localization
- [ ] Number formatting (1,000 vs 1.000)
- [ ] Currency localization

---

## Summary

The Literature Database now supports **English** and **Russian** with:

✅ Complete UI translation
✅ Easy language switching
✅ Session persistence
✅ Mobile-friendly
✅ Developer-friendly
✅ Comprehensive Russian documentation

**Try it now**: Click **EN** or **RU** in the navigation bar! 🌍
