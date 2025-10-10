# Руководство разработчика - База данных литературы

> **Для опытных разработчиков, переходящих с процедурного PHP на современный Laravel**

---

## 📚 Оглавление

- [Что изменилось](#что-изменилось)
- [Архитектура приложения](#архитектура-приложения)
- [Структура папок](#структура-папок)
- [Как работает Laravel](#как-работает-laravel)
- [Работа с базой данных](#работа-с-базой-данных)
- [Работа с представлениями](#работа-с-представлениями)
- [Livewire компоненты](#livewire-компоненты)
- [Начало разработки](#начало-разработки)
- [Типичные задачи](#типичные-задачи)
- [Отладка и тестирование](#отладка-и-тестирование)

---

## Что изменилось

### Раньше (Процедурный PHP)

```php
// Старый подход - index.php
<?php
include 'connect.php';

$sql = "SELECT * FROM publications WHERE _del_mark = 0";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['title'] . "</td>";
    echo "</tr>";
}
?>
```

### Теперь (Laravel + Livewire)

```php
// Современный подход - PublicationList.php
use App\Models\Publication;

$publications = Publication::where('_del_mark', 0)
    ->with(['publishing', 'authors'])
    ->paginate(15);
```

### Основные отличия

| Аспект | Раньше | Теперь |
|--------|--------|--------|
| **База данных** | Прямые SQL-запросы | ORM (Eloquent) |
| **HTML** | echo, print | Blade шаблоны |
| **Структура** | Один файл = страница | MVC архитектура |
| **Безопасность** | Ручная проверка | Автоматическая защита |
| **Сессии** | $_SESSION | Встроенная система |
| **Роутинг** | Файлы .php | Централизованные маршруты |

---

## Архитектура приложения

Laravel использует **MVC (Model-View-Controller)** архитектуру:

```
Запрос → Маршрут → Контроллер/Livewire → Модель → База данных
                                       ↓
                                    Представление (Blade) → Ответ
```

### Пример потока данных

1. **Пользователь** открывает `/publications`
2. **Роутер** (`routes/web.php`) направляет на компонент `PublicationList`
3. **Livewire компонент** (`app/Livewire/Publications/PublicationList.php`) запрашивает данные
4. **Модель** (`app/Models/Publication.php`) делает запрос к БД через Eloquent
5. **База данных** возвращает данные
6. **Представление** (`resources/views/livewire/publications/publication-list.blade.php`) отображает HTML
7. **Пользователь** видит страницу

---

## Структура папок

```
/
├── app/                          # Ядро приложения
│   ├── Http/
│   │   ├── Controllers/          # Контроллеры (традиционный подход)
│   │   └── Middleware/           # Промежуточное ПО (фильтры)
│   │       └── SetLocale.php     # Переключение языка
│   ├── Livewire/                 # Livewire компоненты (современный подход)
│   │   └── Publications/
│   │       └── PublicationList.php  # Список публикаций
│   └── Models/                   # Модели (работа с БД)
│       ├── Publication.php       # Модель публикации
│       ├── Author.php            # Модель автора
│       └── ...
│
├── database/                     # База данных
│   ├── migrations/               # Миграции (версионирование схемы БД)
│   │   └── 2025_10_10_*_create_publications_table.php
│   └── seeders/                  # Заполнение тестовыми данными
│
├── resources/                    # Ресурсы (фронтенд)
│   ├── views/                    # Представления (HTML шаблоны)
│   │   ├── layouts/              # Макеты страниц
│   │   │   ├── app.blade.php     # Главный макет (для авторизованных)
│   │   │   └── guest.blade.php   # Макет для гостей
│   │   ├── livewire/             # Представления Livewire компонентов
│   │   │   ├── layout/
│   │   │   │   └── navigation.blade.php  # Навигация
│   │   │   └── publications/
│   │   │       └── publication-list.blade.php  # Список публикаций
│   │   └── dashboard.blade.php   # Дашборд
│   ├── css/                      # Стили
│   │   └── app.css               # Главный CSS (Tailwind)
│   └── js/                       # JavaScript
│       └── app.js                # Главный JS (Alpine.js)
│
├── routes/                       # Маршруты (URL → Компонент/Контроллер)
│   ├── web.php                   # Веб-маршруты
│   └── auth.php                  # Маршруты аутентификации
│
├── lang/                         # Переводы
│   ├── en.json                   # Английский
│   └── ru.json                   # Русский
│
├── public/                       # Публичная директория (доступна из браузера)
│   ├── index.php                 # Точка входа
│   └── build/                    # Скомпилированные CSS/JS
│
├── config/                       # Конфигурация
│   ├── app.php                   # Настройки приложения
│   ├── database.php              # Настройки БД
│   └── ...
│
├── docker/                       # Docker конфигурация
├── docker-compose.yml            # Docker Compose файл
├── .env                          # Переменные окружения (пароли, настройки)
├── composer.json                 # PHP зависимости
└── package.json                  # JavaScript зависимости
```

---

## Как работает Laravel

### 1. Точка входа

**Раньше:**
```php
// Каждый файл = страница
// index.php, publications.php, edit.php
```

**Теперь:**
```php
// Всё начинается с public/index.php
// Затем routes/web.php определяет, какой компонент вызвать
```

### 2. Маршруты (Routing)

Файл: `routes/web.php`

```php
// Вместо прямого доступа к файлам используются маршруты

// Простой маршрут
Route::get('/', function () {
    return redirect()->route('publications.index');
});

// Маршрут к Livewire компоненту
Route::get('/publications', PublicationList::class)
    ->name('publications.index');

// Группа маршрутов с аутентификацией
Route::middleware(['auth'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');
    Route::view('profile', 'profile')->name('profile');
});

// Параметры в URL
Route::get('/publication/{id}', function ($id) {
    // $id содержит значение из URL
});
```

### 3. Промежуточное ПО (Middleware)

Middleware = фильтры, которые выполняются до/после запроса

**Пример:** Переключение языка

Файл: `app/Http/Middleware/SetLocale.php`

```php
public function handle(Request $request, Closure $next)
{
    // Получаем язык из сессии
    $locale = session('locale', 'en');

    // Устанавливаем язык приложения
    app()->setLocale($locale);

    // Передаём управление дальше
    return $next($request);
}
```

**Регистрация middleware:** `bootstrap/app.php`

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        \App\Http\Middleware\SetLocale::class,  // Добавляем наш middleware
    ]);
})
```

---

## Работа с базой данных

### Старый подход (Прямые SQL запросы)

```php
// connect.php
$conn = mysqli_connect("localhost", "user", "pass", "db");

// Получение данных
$sql = "SELECT * FROM publications WHERE id = " . $_GET['id'];
$result = mysqli_query($conn, $sql);  // ❌ SQL-инъекция!

// Вставка данных
$sql = "INSERT INTO publications (title) VALUES ('$title')";
mysqli_query($conn, $sql);  // ❌ Небезопасно!
```

### Новый подход (Eloquent ORM)

#### Модель

Файл: `app/Models/Publication.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    // Название таблицы
    protected $table = 'publications';

    // Первичный ключ
    protected $primaryKey = 'id_publication';

    // Поля, которые можно заполнять массово
    protected $fillable = [
        'title', 'title_low', 'issue_year',
        'id_publishing', 'id_author_set'
    ];

    // Связь с издательством (один ко многим)
    public function publishing()
    {
        return $this->belongsTo(Publishing::class, 'id_publishing', 'id_publishing');
    }

    // Связь с авторами (через промежуточную таблицу)
    public function authorGroup()
    {
        return $this->belongsTo(AuthorGroup::class, 'id_author_set', 'id_author_set');
    }
}
```

#### Запросы к БД (Query Builder)

```php
// Получить все активные публикации
$publications = Publication::where('_del_mark', 0)->get();

// Получить с пагинацией
$publications = Publication::where('_del_mark', 0)->paginate(15);

// Поиск по названию
$publications = Publication::where('title', 'like', '%' . $search . '%')->get();

// Получить одну запись
$publication = Publication::find(1);

// С загрузкой связей (eager loading)
$publications = Publication::with(['publishing', 'authorGroup'])->get();

// Создание новой записи
$publication = Publication::create([
    'title' => 'Новая книга',
    'issue_year' => '2025',
]);

// Обновление
$publication = Publication::find(1);
$publication->title = 'Обновлённое название';
$publication->save();

// Или так:
Publication::where('id_publication', 1)
    ->update(['title' => 'Обновлённое название']);

// Удаление (мягкое удаление в нашем случае)
$publication = Publication::find(1);
$publication->_del_mark = 1;
$publication->save();
```

### Миграции (Версионирование схемы БД)

Вместо ручного создания таблиц через phpMyAdmin:

```php
// database/migrations/2025_10_10_create_publications_table.php

public function up()
{
    Schema::create('publications', function (Blueprint $table) {
        $table->id('id_publication');
        $table->string('title');
        $table->string('title_low')->nullable();
        $table->char('issue_year', 4)->nullable();
        $table->bigInteger('id_publishing')->nullable();
        $table->tinyInteger('_del_mark')->default(0);
        $table->timestamps();

        // Индексы
        $table->index('id_publishing');
        $table->index('_del_mark');
    });
}
```

**Команды миграций:**

```bash
# Применить все миграции
php artisan migrate

# Откатить последнюю миграцию
php artisan migrate:rollback

# Откатить всё и применить заново
php artisan migrate:fresh

# Создать новую миграцию
php artisan make:migration create_table_name
```

---

## Работа с представлениями

### Blade шаблоны

Blade = шаблонизатор Laravel (вместо echo/print)

Файл: `resources/views/livewire/publications/publication-list.blade.php`

#### Основной синтаксис

```blade
{{-- Комментарий --}}

{{-- Вывод переменных (автоматическое экранирование) --}}
{{ $title }}

{{-- Вывод без экранирования (HTML) --}}
{!! $html !!}

{{-- Условия --}}
@if($condition)
    <p>Правда</p>
@elseif($other)
    <p>Другое условие</p>
@else
    <p>Ложь</p>
@endif

{{-- Циклы --}}
@foreach($publications as $publication)
    <tr>
        <td>{{ $publication->title }}</td>
    </tr>
@endforeach

@forelse($publications as $publication)
    <tr>{{ $publication->title }}</tr>
@empty
    <tr><td>Нет данных</td></tr>
@endforelse

{{-- Проверка аутентификации --}}
@auth
    <p>Пользователь авторизован</p>
@endauth

@guest
    <p>Гость</p>
@endguest

{{-- Переводы --}}
{{ __('Publications') }}  {{-- Выведет "Публикации" на русском --}}

{{-- Включение других шаблонов --}}
@include('partials.header')

{{-- Слоты (для макетов) --}}
<x-slot name="header">
    <h1>Заголовок</h1>
</x-slot>
```

#### Макеты (Layouts)

Файл: `resources/views/layouts/app.blade.php`

```blade
<!DOCTYPE html>
<html>
<head>
    <title>{{ $title ?? 'Приложение' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <nav>
        {{-- Навигация --}}
    </nav>

    <main>
        {{ $slot }}  {{-- Здесь будет содержимое страницы --}}
    </main>
</body>
</html>
```

Использование макета:

```blade
<x-app-layout>
    <x-slot name="header">
        <h2>Заголовок страницы</h2>
    </x-slot>

    <div>
        {{-- Содержимое страницы --}}
    </div>
</x-app-layout>
```

---

## Livewire компоненты

Livewire = современный способ создания интерактивных интерфейсов без написания JavaScript

### Структура компонента

1. **PHP класс** (логика)
2. **Blade представление** (HTML)

### Пример: Список публикаций

#### PHP класс

Файл: `app/Livewire/Publications/PublicationList.php`

```php
namespace App\Livewire\Publications;

use App\Models\Publication;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;

class PublicationList extends Component
{
    use WithPagination;  // Добавляет пагинацию

    // Публичные свойства автоматически доступны в представлении
    #[Url]  // Сохраняет значение в URL (?search=текст)
    public $search = '';

    #[Url]
    public $showDeleted = false;

    public $perPage = 15;

    // Вызывается при изменении $search
    public function updatingSearch()
    {
        $this->resetPage();  // Сбрасываем на первую страницу
    }

    // Публичные методы можно вызывать из представления
    public function toggleDeleted()
    {
        $this->showDeleted = !$this->showDeleted;
        $this->resetPage();
    }

    public function deletePublication($id)
    {
        $publication = Publication::find($id);
        if ($publication) {
            $publication->_del_mark = 1;
            $publication->save();
        }
    }

    // Главный метод - рендерит представление
    public function render()
    {
        $publications = Publication::query()
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%' . $this->search . '%');
            })
            ->when($this->showDeleted, function ($query) {
                $query->where('_del_mark', 1);
            }, function ($query) {
                $query->where('_del_mark', 0);
            })
            ->with(['publishing', 'authorGroup'])
            ->orderBy('upload_date', 'desc')
            ->paginate($this->perPage);

        return view('livewire.publications.publication-list', [
            'publications' => $publications,
        ]);
    }
}
```

#### Blade представление

Файл: `resources/views/livewire/publications/publication-list.blade.php`

```blade
<div>
    {{-- Поиск с автоматическим обновлением --}}
    <input
        type="text"
        wire:model.live.debounce.300ms="search"
        placeholder="Поиск..."
    >

    {{-- Кнопка вызывает метод toggleDeleted() --}}
    <button wire:click="toggleDeleted">
        {{ $showDeleted ? 'Показать активные' : 'Показать удалённые' }}
    </button>

    {{-- Таблица с данными --}}
    <table>
        @foreach($publications as $publication)
            <tr>
                <td>{{ $publication->title }}</td>
                <td>
                    {{-- Подтверждение удаления --}}
                    <button
                        wire:click="deletePublication({{ $publication->id_publication }})"
                        wire:confirm="Вы уверены?"
                    >
                        Удалить
                    </button>
                </td>
            </tr>
        @endforeach
    </table>

    {{-- Пагинация --}}
    {{ $publications->links() }}
</div>
```

### Livewire директивы

```blade
{{-- Привязка данных --}}
wire:model="search"              {{-- Двусторонняя привязка --}}
wire:model.live="search"         {{-- Мгновенное обновление --}}
wire:model.live.debounce.300ms="search"  {{-- С задержкой 300мс --}}

{{-- События --}}
wire:click="methodName"          {{-- Клик --}}
wire:submit="save"               {{-- Отправка формы --}}
wire:keydown.enter="search"      {{-- Нажатие Enter --}}

{{-- Подтверждение --}}
wire:confirm="Вы уверены?"       {{-- Диалог подтверждения --}}

{{-- Индикатор загрузки --}}
<div wire:loading>
    Загрузка...
</div>
```

---

## Начало разработки

### Запуск проекта

```bash
# 1. Запустить Docker контейнеры
docker compose up -d

# 2. Проверить статус
docker compose ps

# 3. Просмотр логов
docker compose logs -f web

# 4. Остановить контейнеры
docker compose down
```

### Работа с кодом

```bash
# Выполнение команд в контейнере
docker compose exec web bash

# Или напрямую
docker compose exec web php artisan [команда]
```

### Структура разработки

```
1. Создать модель и миграцию
2. Создать Livewire компонент
3. Добавить маршрут
4. Создать представление
5. Тестировать
```

---

## Типичные задачи

### Создать новую страницу со списком

**1. Создать модель:**

```bash
php artisan make:model Author -m
```

**2. Настроить миграцию:**

```php
// database/migrations/..._create_authors_table.php
Schema::create('authors', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});
```

**3. Применить миграцию:**

```bash
php artisan migrate
```

**4. Создать Livewire компонент:**

```bash
php artisan make:livewire Authors/AuthorList
```

Это создаст:
- `app/Livewire/Authors/AuthorList.php`
- `resources/views/livewire/authors/author-list.blade.php`

**5. Написать логику:**

```php
// app/Livewire/Authors/AuthorList.php
public function render()
{
    $authors = Author::orderBy('name')->get();
    return view('livewire.authors.author-list', [
        'authors' => $authors
    ]);
}
```

**6. Создать представление:**

```blade
{{-- resources/views/livewire/authors/author-list.blade.php --}}
<div>
    <table>
        @foreach($authors as $author)
            <tr><td>{{ $author->name }}</td></tr>
        @endforeach
    </table>
</div>
```

**7. Добавить маршрут:**

```php
// routes/web.php
Route::get('/authors', AuthorList::class)->name('authors.index');
```

### Добавить форму создания/редактирования

**1. Создать компонент:**

```bash
php artisan make:livewire Publications/PublicationForm
```

**2. Добавить свойства формы:**

```php
// app/Livewire/Publications/PublicationForm.php
class PublicationForm extends Component
{
    public $publicationId;
    public $title = '';
    public $issue_year = '';

    // Правила валидации
    protected $rules = [
        'title' => 'required|min:3',
        'issue_year' => 'nullable|digits:4',
    ];

    // Загрузка данных при редактировании
    public function mount($id = null)
    {
        if ($id) {
            $publication = Publication::find($id);
            $this->publicationId = $publication->id_publication;
            $this->title = $publication->title;
            $this->issue_year = $publication->issue_year;
        }
    }

    // Сохранение
    public function save()
    {
        $this->validate();

        if ($this->publicationId) {
            // Обновление
            $publication = Publication::find($this->publicationId);
            $publication->update([
                'title' => $this->title,
                'issue_year' => $this->issue_year,
            ]);
        } else {
            // Создание
            Publication::create([
                'title' => $this->title,
                'issue_year' => $this->issue_year,
            ]);
        }

        session()->flash('message', 'Сохранено успешно!');
        return redirect()->route('publications.index');
    }

    public function render()
    {
        return view('livewire.publications.publication-form');
    }
}
```

**3. Создать форму:**

```blade
{{-- resources/views/livewire/publications/publication-form.blade.php --}}
<div>
    <form wire:submit="save">
        <div>
            <label>Название:</label>
            <input type="text" wire:model="title">
            @error('title') <span>{{ $message }}</span> @enderror
        </div>

        <div>
            <label>Год:</label>
            <input type="text" wire:model="issue_year">
            @error('issue_year') <span>{{ $message }}</span> @enderror
        </div>

        <button type="submit">Сохранить</button>
    </form>
</div>
```

**4. Добавить маршруты:**

```php
// routes/web.php
Route::get('/publications/create', PublicationForm::class)
    ->name('publications.create');
Route::get('/publications/{id}/edit', PublicationForm::class)
    ->name('publications.edit');
```

### Добавить поиск и фильтрацию

```php
// В компоненте
public $search = '';
public $filterType = '';

public function render()
{
    $publications = Publication::query()
        ->when($this->search, function ($query) {
            $query->where('title', 'like', '%' . $this->search . '%');
        })
        ->when($this->filterType, function ($query) {
            $query->where('id_issue_type', $this->filterType);
        })
        ->get();

    return view('livewire.publications.publication-list', [
        'publications' => $publications
    ]);
}
```

```blade
{{-- В представлении --}}
<input type="text" wire:model.live.debounce.300ms="search">
<select wire:model.live="filterType">
    <option value="">Все типы</option>
    <option value="1">Книга</option>
    <option value="2">Статья</option>
</select>
```

---

## Отладка и тестирование

### Вывод отладочной информации

```php
// Вывести в лог (storage/logs/laravel.log)
\Log::info('Значение переменной', ['data' => $variable]);

// Вывести и остановить выполнение
dd($variable);  // Dump and Die

// Вывести в консоль браузера (через Ray или Telescope)
dump($variable);
```

### Laravel Tinker (Интерактивная консоль)

```bash
docker compose exec web php artisan tinker
```

```php
// Выполнять PHP код напрямую
>>> $publications = \App\Models\Publication::all();
>>> $publications->count();
=> 150

>>> $pub = \App\Models\Publication::find(1);
>>> $pub->title;
=> "Название книги"

>>> $pub->title = "Новое название";
>>> $pub->save();
```

### Просмотр запросов к БД

```php
// Включить логирование запросов
\DB::enableQueryLog();

// Выполнить запросы
$publications = Publication::where('_del_mark', 0)->get();

// Посмотреть выполненные запросы
dd(\DB::getQueryLog());
```

### Очистка кеша

```bash
# Очистить кеш конфигурации
php artisan config:clear

# Очистить кеш маршрутов
php artisan route:clear

# Очистить кеш представлений
php artisan view:clear

# Очистить весь кеш
php artisan cache:clear

# Очистить всё сразу
php artisan optimize:clear
```

### Тестирование

```bash
# Запустить тесты
php artisan test

# Создать тест
php artisan make:test PublicationTest
```

```php
// tests/Feature/PublicationTest.php
public function test_can_view_publications()
{
    $response = $this->get('/publications');
    $response->assertStatus(200);
    $response->assertSee('Publications');
}
```

---

## Полезные команды Artisan

```bash
# Информация о приложении
php artisan about

# Список всех маршрутов
php artisan route:list

# Создать контроллер
php artisan make:controller PublicationController

# Создать модель с миграцией и контроллером
php artisan make:model Publication -mcr

# Создать Livewire компонент
php artisan make:livewire ComponentName

# Создать middleware
php artisan make:middleware MiddlewareName

# Список всех команд
php artisan list
```

---

## Дополнительные ресурсы

### Документация

- **Laravel:** https://laravel.com/docs/11.x
- **Livewire:** https://livewire.laravel.com/docs
- **Tailwind CSS:** https://tailwindcss.com/docs
- **Alpine.js:** https://alpinejs.dev

### Локальные файлы

- [QUICKSTART.md](QUICKSTART.md) - Быстрый старт (на английском)
- [LIVEWIRE_GUIDE.md](LIVEWIRE_GUIDE.md) - Подробное руководство по Livewire

---

## Часто задаваемые вопросы

### Где хранятся пароли и настройки?

В файле `.env` в корне проекта

```env
DB_HOST=db
DB_PORT=3306
DB_DATABASE=db_manager
DB_USERNAME=dbuser
DB_PASSWORD=dbpass
```

### Как изменить порт?

В файле `docker-compose.yml`:

```yaml
ports:
  - "8080:80"  # Изменить 8080 на нужный порт
```

### Как добавить новое поле в таблицу?

1. Создать миграцию:
```bash
php artisan make:migration add_field_to_publications
```

2. Добавить поле:
```php
Schema::table('publications', function (Blueprint $table) {
    $table->string('new_field')->nullable();
});
```

3. Применить:
```bash
php artisan migrate
```

4. Добавить в модель:
```php
protected $fillable = ['title', 'new_field'];
```

### Как работать с датами?

```php
// В модели
protected $dates = ['upload_date', 'created_at'];

// Использование
$publication->upload_date->format('Y-m-d');
$publication->upload_date->diffForHumans();  // "2 дня назад"
```

### Как сделать загрузку файлов?

```php
// В компоненте
public $file;

public function save()
{
    $path = $this->file->store('uploads', 'public');
    // $path содержит путь к файлу
}
```

```blade
<input type="file" wire:model="file">
```

---

## Контакты и поддержка

При возникновении проблем:

1. Проверьте логи: `docker compose logs web`
2. Проверьте логи Laravel: `storage/logs/laravel.log`
3. Очистите кеш: `php artisan optimize:clear`
4. Пересоберите контейнеры: `docker compose down && docker compose up -d --build`

---

## Карта миграции из Legacy системы

> **Для тех, кто знаком со старой системой `HTDocs_legacy/s/`**

Если вы работали со старой процедурной системой и хотите понять, куда что переехало в Laravel, эта секция для вас.

### Общая карта директорий

| Legacy (`HTDocs_legacy/s/`) | Laravel | Описание |
|----------------------------|---------|----------|
| `index.php` | `routes/web.php` + Auth | Точка входа и роутинг |
| `UserEnter/` | Laravel Breeze + `app/Models/User.php` | Аутентификация |
| `MainTable/View/` | `app/Livewire/Publications/` | Просмотр данных |
| `MainTable/Update/` | `app/Livewire/Publications/PublicationForm.php` | Создание/редактирование |
| `Catalogs/` | `app/Livewire/Catalogs/` | Справочники |
| `DataBases/` | `app/Console/Commands/` + Миграции | Управление БД |
| `Languages/` | `lang/` + `app/Http/Middleware/SetLocale.php` | Многоязычность |
| `Titles/` | `lang/en.json`, `lang/ru.json` | Переводы интерфейса |
| `Utilities/` | `app/Services/` + Helpers | Вспомогательные функции |
| `Fields/` | Миграции + `field_config` таблица | Конфигурация полей |
| `Algorithms/` | `app/Services/TextParser.php` | Парсинг данных |
| `Tree/` | `app/Livewire/Tree/` | Иерархические данные |
| `Configuration/` | `config/`, `.env` | Конфигурация |
| `Alarm/` | Laravel Log + Notifications | Уведомления об ошибках |

### Карта основных функций

#### Работа с БД

**Раньше:**
```php
// HTDocs_legacy/s/Utilities/DataBases.php
$dbh = mysqli_connect($host, $user, $pass, $db);
$sql = "SELECT * FROM publications WHERE _del_mark = 0";
$result = mysqli_query($dbh, $sql);
while ($row = mysqli_fetch_assoc($result)) {
    // ...
}
```

**Теперь:**
```php
// Laravel
$publications = Publication::where('_del_mark', 0)->get();
// или
$publications = Publication::active()->get(); // через scope
```

#### Аутентификация

**Раньше:**
```php
// HTDocs_legacy/s/UserEnter/UserEnterUtilities.php
function TestUserPass($user, $pass, $dbh) {
    $sql = "SELECT * FROM user_ident WHERE name='$user'";
    // ...
}
```

**Теперь:**
```php
// Laravel Breeze
Auth::attempt(['name' => $user, 'password' => $pass]);
```

#### Сессии

**Раньше:**
```php
// Legacy
session_start();
$_SESSION['user_lang'] = array(1, "English");
$_SESSION['arr_db'] = CreateDBArray($dbh);
```

**Теперь:**
```php
// Laravel
session(['locale' => 'en']);
session(['databases' => $databases]);
// или
$request->session()->put('key', 'value');
```

#### Переводы

**Раньше:**
```php
// Legacy: HTDocs_legacy/s/Titles/TitleSelect.php
function Title($id) {
    global $dbh;
    $sql = "SELECT title_text FROM interface_texts
            WHERE id_title=$id AND id_language=".$_SESSION['user_lang'][0];
    // ...
}
```

**Теперь:**
```php
// Laravel
{{ __('Publications') }}
// или в PHP
__('Publications')
```

#### Каталоги/Справочники

**Раньше:**
```php
// Legacy: HTDocs_legacy/s/Catalogs/CatalogForm.php
// ~800 строк процедурного кода с echo
```

**Теперь:**
```php
// Laravel Livewire: app/Livewire/Catalogs/AuthorCatalog.php
class AuthorCatalog extends Component
{
    public $search = '';

    public function render()
    {
        return view('livewire.catalogs.author-catalog', [
            'authors' => Author::search($this->search)->paginate(15)
        ]);
    }
}
```

### Карта таблиц БД

Большинство таблиц сохранили свою структуру для упрощения миграции:

| Legacy таблица | Laravel модель | Изменения |
|----------------|----------------|-----------|
| `user_ident` | `User` | Адаптирована для Laravel Auth |
| `publications` | `Publication` | Без изменений |
| `authors` | `Author` | Без изменений |
| `author_groups` | `AuthorGroup` | Может быть рефакторена в будущем |
| `publishings` | `Publishing` | Без изменений |
| `interface_texts` | JSON файлы в `lang/` | Мигрировано из БД |
| `languages` | `Language` + `lang/` | Гибридный подход |
| `db_list` | Конфигурация | Частично в `.env` |
| `algorithms` | `Algorithm` + Service | Логика в сервисах |
| `field_config` | `FieldConfig` | Пока без изменений |

### Типичные паттерны преобразования

#### 1. Создание записи

**Раньше:**
```php
// Legacy
$sql = "INSERT INTO authors (author, author_low) VALUES ('$author', '".strtolower($author)."')";
mysqli_query($dbh, $sql);
$id = mysqli_insert_id($dbh);
```

**Теперь:**
```php
// Laravel
$author = Author::create([
    'author' => $authorName,
    'author_low' => strtolower($authorName)
]);
$id = $author->id;
```

#### 2. Обновление записи

**Раньше:**
```php
// Legacy
$sql = "UPDATE publications SET title='$title' WHERE id_publication=$id";
mysqli_query($dbh, $sql);
```

**Теперь:**
```php
// Laravel
$publication = Publication::find($id);
$publication->update(['title' => $title]);
// или
Publication::where('id_publication', $id)->update(['title' => $title]);
```

#### 3. Удаление (мягкое)

**Раньше:**
```php
// Legacy
$sql = "UPDATE publications SET _del_mark=1 WHERE id_publication=$id";
mysqli_query($dbh, $sql);
```

**Теперь:**
```php
// Laravel (сохранили _del_mark для совместимости)
$publication = Publication::find($id);
$publication->_del_mark = 1;
$publication->save();

// Или через scope
$publication->softDelete(); // custom метод
```

#### 4. Выборка со связями

**Раньше:**
```php
// Legacy
$sql = "SELECT p.*, pub.publishing FROM publications p
        LEFT JOIN publishings pub ON p.id_publishing = pub.id_publishing
        WHERE p._del_mark=0";
$result = mysqli_query($dbh, $sql);
```

**Теперь:**
```php
// Laravel (Eloquent автоматически делает JOIN)
$publications = Publication::with('publishing')
    ->where('_del_mark', 0)
    ->get();

// В шаблоне:
{{ $publication->publishing->publishing }}
```

#### 5. Пагинация

**Раньше:**
```php
// Legacy: HTDocs_legacy/s/Catalogs/Navigation.php
// ~100 строк кода для пагинации
$offset = ($page - 1) * $perPage;
$sql = "SELECT * FROM publications LIMIT $offset, $perPage";
```

**Теперь:**
```php
// Laravel (одна строка!)
$publications = Publication::paginate(15);

// В шаблоне:
{{ $publications->links() }}
```

#### 6. Поиск

**Раньше:**
```php
// Legacy: HTDocs_legacy/s/Catalogs/Search.php
$search = mysqli_real_escape_string($dbh, $_POST['search']);
$sql = "SELECT * FROM publications WHERE title LIKE '%$search%'";
```

**Теперь:**
```php
// Laravel Livewire
class PublicationList extends Component
{
    public $search = '';

    public function render()
    {
        return view('...', [
            'publications' => Publication::where('title', 'like', "%{$this->search}%")->get()
        ]);
    }
}

// В шаблоне (автоматическое обновление при вводе):
<input type="text" wire:model.live.debounce.300ms="search">
```

### Где найти соответствующий код

Если вы ищете, как что-то делалось в legacy системе:

1. **Аутентификация**:
   - Legacy: `HTDocs_legacy/s/UserEnter/`
   - Laravel: `app/Http/Controllers/Auth/`, `routes/auth.php`

2. **Список публикаций**:
   - Legacy: `HTDocs_legacy/s/MainTable/View/`
   - Laravel: `app/Livewire/Publications/PublicationList.php`

3. **Форма редактирования**:
   - Legacy: `HTDocs_legacy/s/MainTable/Update/`
   - Laravel: `app/Livewire/Publications/PublicationForm.php`

4. **Справочники**:
   - Legacy: `HTDocs_legacy/s/Catalogs/`
   - Laravel: `app/Livewire/Catalogs/`

5. **Переводы**:
   - Legacy: `db_manager.interface_texts` таблица
   - Laravel: `lang/en.json`, `lang/ru.json`

6. **Утилиты БД**:
   - Legacy: `HTDocs_legacy/s/Utilities/DataBases.php`
   - Laravel: `app/Models/` + Eloquent

### Полезные алиасы для миграции

Если вы хотите использовать старые названия функций, создайте helpers:

```php
// app/Helpers/LegacyHelpers.php

// Вместо Title($id)
function Title($key) {
    return __($key);
}

// Вместо GetDB()
function GetDB($name) {
    return DB::connection($name);
}

// И т.д.
```

Зарегистрировать в `composer.json`:
```json
"autoload": {
    "files": [
        "app/Helpers/LegacyHelpers.php"
    ]
}
```

### Дополнительная документация

Для детального плана миграции смотрите:
- **[MIGRATION_PLAN.md](MIGRATION_PLAN.md)** - Полный план миграции со старой системы

---

**Удачной разработки! 🚀**
