# План миграции: Legacy PHP → Laravel 12

> Комплексный план миграции системы управления базами данных литературы с процедурного PHP на Laravel 12

---

## 📋 Содержание

- [Введение](#введение)
- [Обзор Legacy системы](#обзор-legacy-системы)
- [Архитектурная карта миграции](#архитектурная-карта-миграции)
- [Фазы миграции](#фазы-миграции)
- [Детальный план миграции модулей](#детальный-план-миграции-модулей)
- [Стратегия баз данных](#стратегия-баз-данных)
- [Тестирование и валидация](#тестирование-и-валидация)
- [Риски и их митигация](#риски-и-их-митигация)

---

## Введение

### Цели миграции

1. **Модернизация**: Переход на современный фреймворк Laravel 12
2. **Безопасность**: Устранение уязвимостей legacy кода
3. **Поддерживаемость**: Упрощение разработки и поддержки
4. **Производительность**: Оптимизация работы приложения
5. **Масштабируемость**: Подготовка к будущему росту

### Текущее состояние

- **Legacy система**: 186 PHP файлов в директории `/s/`
- **База данных**: 4 MySQL базы данных (`db_manager`, `literature`, `phys_math_contents`, `trees`)
- **Технологии**: PHP 5.5, MySQL 5.6, процедурный код, mysqli
- **Функционал**: Полноценная система управления мета-базами данных

### Целевое состояние

- **Фреймворк**: Laravel 12
- **Стек**: PHP 8.2+, MySQL 8.0+, Livewire 3, Tailwind CSS
- **Архитектура**: MVC с Eloquent ORM
- **Интерактивность**: Livewire компоненты без написания JavaScript

---

## Обзор Legacy системы

### Структура модулей (32 директории)

```
HTDocs_legacy/s/
├── Administrator/          # Административные функции
├── Alarm/                  # Система уведомлений об ошибках
├── Algorithms/             # Алгоритмы парсинга текста
├── Catalogs/              # Управление справочниками (18 файлов!)
├── Codings/               # Управление кодировками
├── Configuration/         # Конфигурация системы
├── DataBases/             # CRUD для баз данных (9 файлов)
├── DeleteMarked/          # Мягкое удаление
├── Fields/                # Динамическая конфигурация полей
├── Languages/             # Управление языками
├── LocalLanguages/        # Локализация
├── LoadToServer/          # Развертывание
├── LogProcessing/         # Обработка логов
├── MainTable/             # Основные таблицы данных
│   ├── Update/           # Обновление записей
│   └── View/             # Просмотр данных
├── PrimatyUpload/         # Загрузка данных
├── SystemDataBaseCopies/  # Резервные копии
├── Tables/                # Управление таблицами
├── Titles/                # Управление интерфейсными текстами
├── Tree/                  # Иерархические структуры
├── UserEnter/             # Аутентификация и вход
├── UserList/              # Управление пользователями
├── Utilities/             # Вспомогательные функции (11 файлов)
└── Visits/                # Отслеживание активности
```

### Ключевые функции Legacy системы

#### 1. Мета-база данных
- Управляет несколькими базами данных
- Динамическое создание/изменение структуры
- Автоматическое восстановление при ошибках

#### 2. Многоязычность
- Все тексты UI хранятся в БД
- Поддержка английского и русского
- Расширяемость на другие языки

#### 3. Динамическая конфигурация
- Поля таблиц настраиваются через UI
- Типы полей, валидация, отображение
- Пользовательские настройки отображения

#### 4. Импорт данных
- Алгоритмы парсинга текстовых файлов
- Регулярные выражения
- Извлечение авторов, названий, годов издания

#### 5. Иерархические данные
- Деревья с неограниченной вложенностью
- Сохранение состояния (свернуто/развернуто)
- Операции копирования/вставки веток

#### 6. Безопасность
- Система аутентификации
- Уровни доступа (0-99)
- Ограничение попыток входа
- Таймауты сессий

---

## Архитектурная карта миграции

### Основные паттерны преобразования

| Legacy подход | Laravel подход | Примечания |
|---------------|----------------|------------|
| **Прямые SQL запросы** | Eloquent ORM | Безопасность, читаемость |
| **mysqli_query()** | DB::query() / Model | Современный API |
| **include/require** | Namespaces, Autoload | PSR-4 автозагрузка |
| **$_SESSION** | Session facade | Встроенная система |
| **$_GET/$_POST** | Request object | Валидация, безопасность |
| **echo HTML** | Blade templates | Шаблонизация |
| **Один файл = страница** | Routes → Controllers/Livewire | MVC архитектура |
| **Функции в файлах** | Классы, методы | ООП подход |
| **config в БД** | .env + config/ | Стандартная практика |

### Архитектурное сравнение

#### Legacy система
```
index.php
    ↓
require 20+ файлов
    ↓
Проверка сессии
    ↓
Прямые SQL запросы
    ↓
echo HTML с PHP вставками
```

#### Laravel система
```
public/index.php (фиксированная точка входа)
    ↓
routes/web.php (маршрутизация)
    ↓
Livewire компонент (логика)
    ↓
Model (Eloquent ORM)
    ↓
Blade template (представление)
```

---

## Фазы миграции

### Фаза 0: Подготовка (1-2 недели)

**Цели:**
- Полное понимание legacy системы
- Настройка окружения разработки
- Создание тестовой среды

**Задачи:**
- [x] Документирование существующей системы
- [ ] Анализ всех модулей и зависимостей
- [ ] Создание схемы данных (ER диаграмма)
- [ ] Настройка Git для legacy кода
- [ ] Подготовка тестовых данных

**Результат:** Полная карта системы и окружение для разработки

---

### Фаза 1: Основа (2-3 недели)

**Цели:**
- Миграция базовой структуры БД
- Создание моделей для всех таблиц
- Базовая аутентификация

#### 1.1 Миграция баз данных

**Приоритет 1: `db_manager` (системная БД)**

```bash
# Создать миграции для всех системных таблиц
php artisan make:migration create_db_list_table
php artisan make:migration create_interface_texts_table
php artisan make:migration create_languages_table
php artisan make:migration create_user_ident_table
php artisan make:migration create_algorithms_table
php artisan make:migration create_coding_table_table
php artisan make:migration create_db_s_configs_table
php artisan make:migration create_interface_special_texts_table
php artisan make:migration create_translate_table_table
php artisan make:migration create_visits_table
```

**Приоритет 2: `literature` (основная БД)**

```bash
php artisan make:migration create_literature_tables
# - publications
# - authors
# - author_groups
# - publishings
# - issue_types
# - themes
# - theme_sets
# - magazines
# - parts
# - part_sets
# - series
# - files
# - collapse_ids
# - field_config
# - table_definitions
# - db_configs
```

**Приоритет 3: `phys_math_contents` и `trees`**

```bash
php artisan make:migration create_phys_math_contents_tables
php artisan make:migration create_trees_tables
```

#### 1.2 Создание моделей

```bash
# Системные модели
php artisan make:model Language
php artisan make:model InterfaceText
php artisan make:model DatabaseConfig
php artisan make:model Algorithm
php artisan make:model CodingTable
php artisan make:model TranslateTable

# Бизнес-модели (уже созданы частично)
php artisan make:model Publication      # ✓ Существует
php artisan make:model Author           # ✓ Существует
php artisan make:model AuthorGroup      # ✓ Существует
php artisan make:model Publishing       # ✓ Существует
php artisan make:model IssueType        # ✓ Существует
php artisan make:model Theme            # ✓ Существует
php artisan make:model ThemeSet         # ✓ Существует
php artisan make:model Magazine         # ✓ Существует
php artisan make:model Part             # ✓ Существует
php artisan make:model PartSet          # ✓ Существует
php artisan make:model Series
php artisan make:model File             # ✓ Существует
```

#### 1.3 Аутентификация

```bash
# Laravel Breeze уже установлен
# Адаптировать для user_ident таблицы
```

**Задачи:**
- [ ] Настроить модель User для работы с `user_ident`
- [ ] Миграция таблицы пользователей
- [ ] Адаптация уровней доступа (user_priority)
- [ ] Ограничение попыток входа
- [ ] Переключение языка пользователя

**Результат:** Работающая БД и базовая аутентификация

---

### Фаза 2: Основной функционал (4-6 недель)

#### 2.1 Модуль публикаций (CRUD)

**Legacy файлы:**
- `MainTable/View/*.php`
- `MainTable/Update/*.php`
- `Catalogs/*.php`

**Laravel компоненты:**

```bash
# Уже созданы частично в app/Livewire/Publications/
php artisan make:livewire Publications/PublicationList    # ✓
php artisan make:livewire Publications/PublicationForm
php artisan make:livewire Publications/PublicationView
php artisan make:livewire Publications/PublicationImport
```

**Функционал:**
- [x] Список публикаций с пагинацией
- [ ] Поиск и фильтрация
- [ ] Создание/редактирование записей
- [ ] Мягкое удаление (_del_mark)
- [ ] Просмотр деталей
- [ ] Связи с авторами, издательствами
- [ ] Импорт из текстовых файлов

#### 2.2 Модуль справочников

**Legacy файлы:**
- `Catalogs/*.php` (18 файлов)

**Laravel компоненты:**

```bash
php artisan make:livewire Catalogs/AuthorCatalog
php artisan make:livewire Catalogs/PublishingCatalog
php artisan make:livewire Catalogs/ThemeCatalog
php artisan make:livewire Catalogs/SeriesCatalog
php artisan make:livewire Catalogs/MagazineCatalog
```

**Функционал:**
- [ ] Управление авторами
- [ ] Управление издательствами
- [ ] Управление темами
- [ ] Управление сериями
- [ ] Управление журналами
- [ ] Автоподстановка (autocomplete)
- [ ] Группировка авторов

#### 2.3 Модуль языков и переводов

**Legacy файлы:**
- `Languages/*.php`
- `Titles/*.php`
- `LocalLanguages/*.php`

**Laravel подход:**

```bash
# Использовать встроенную систему переводов Laravel
lang/
├── en.json
├── ru.json
└── en/
    └── validation.php
```

**Функционал:**
- [ ] Миграция interface_texts в JSON файлы
- [ ] Middleware для переключения языка
- [ ] UI переключатель языка
- [ ] Админ-панель для управления переводами
- [ ] Импорт/экспорт переводов

#### 2.4 Модуль конфигурации

**Legacy файлы:**
- `Configuration/*.php`
- Таблица `db_s_configs`

**Laravel подход:**

```bash
# Использовать config/ и .env
config/
├── app.php
├── database.php
├── legacy.php          # Настройки из db_s_configs
└── literature.php      # Специфичные настройки
```

**Функционал:**
- [ ] Миграция конфигов из БД в файлы
- [ ] UI для управления настройками
- [ ] Валидация конфигурации
- [ ] Кеширование настроек

**Результат:** Работающий CRUD для публикаций и справочников

---

### Фаза 3: Продвинутый функционал (3-4 недели)

#### 3.1 Модуль алгоритмов парсинга

**Legacy файлы:**
- `Algorithms/*.php`
- Таблица `algorithms`

**Laravel компоненты:**

```bash
php artisan make:livewire Algorithms/AlgorithmManager
php artisan make:livewire Algorithms/AlgorithmTest
```

**Создать сервисы:**

```bash
php artisan make:class Services/TextParser
php artisan make:class Services/AlgorithmEngine
```

**Функционал:**
- [ ] UI для настройки алгоритмов
- [ ] Тестирование алгоритмов на примерах
- [ ] Применение алгоритмов при импорте
- [ ] Регулярные выражения
- [ ] Извлечение полей по шаблонам

#### 3.2 Модуль иерархических данных

**Legacy файлы:**
- `Tree/*.php`
- Таблицы `parts`, `part_tree`, `contents`

**Laravel компоненты:**

```bash
php artisan make:livewire Tree/TreeView
php artisan make:livewire Tree/TreeNode
```

**Использовать пакет:**
```bash
composer require staudenmeir/laravel-adjacency-list
```

**Функционал:**
- [ ] Отображение дерева
- [ ] Сворачивание/разворачивание узлов
- [ ] Перетаскивание (drag & drop)
- [ ] Копирование/вставка веток
- [ ] Сохранение состояния

#### 3.3 Модуль загрузки данных

**Legacy файлы:**
- `PrimatyUpload/*.php`
- `LoadToServer/*.php`

**Laravel компоненты:**

```bash
php artisan make:livewire Upload/FileUpload
php artisan make:livewire Upload/ImportWizard
```

**Функционал:**
- [ ] Загрузка файлов
- [ ] Предпросмотр данных
- [ ] Применение алгоритмов
- [ ] Валидация импорта
- [ ] Массовая загрузка
- [ ] Обработка ошибок

#### 3.4 Модуль динамических полей

**Legacy файлы:**
- `Fields/*.php`
- Таблица `field_config`

**Laravel компоненты:**

```bash
php artisan make:livewire Fields/FieldManager
php artisan make:livewire Fields/FieldConfigurator
```

**Функционал:**
- [ ] Управление конфигурацией полей
- [ ] Типы полей
- [ ] Правила валидации
- [ ] Порядок отображения
- [ ] Видимость полей

**Результат:** Полный функционал системы

---

### Фаза 4: Административные функции (2-3 недели)

#### 4.1 Модуль управления базами данных

**Legacy файлы:**
- `DataBases/*.php` (9 файлов)
- `Administrator/*.php`

**Laravel компоненты:**

```bash
php artisan make:livewire Admin/DatabaseManager
php artisan make:livewire Admin/TableManager
php artisan make:livewire Admin/UserManager
```

**Функционал:**
- [ ] Управление списком БД
- [ ] Создание/удаление баз
- [ ] Тестирование структуры
- [ ] Резервное копирование
- [ ] Восстановление
- [ ] Управление пользователями
- [ ] Права доступа

#### 4.2 Модуль логов и мониторинга

**Legacy файлы:**
- `LogProcessing/*.php`
- `Visits/*.php`
- `Alarm/*.php`

**Laravel подход:**

```bash
# Использовать Laravel Log и встроенные инструменты
# + Laravel Telescope для отладки
composer require laravel/telescope --dev
php artisan telescope:install
```

**Функционал:**
- [ ] Просмотр логов
- [ ] История посещений
- [ ] Система уведомлений
- [ ] Мониторинг ошибок
- [ ] Дашборд статистики

#### 4.3 Модуль резервного копирования

**Legacy файлы:**
- `SystemDataBaseCopies/`

**Laravel пакет:**

```bash
composer require spatie/laravel-backup
```

**Функционал:**
- [ ] Автоматическое резервное копирование
- [ ] Ручное создание бэкапов
- [ ] Восстановление из бэкапа
- [ ] Настройка расписания
- [ ] Уведомления о бэкапах

**Результат:** Полноценная админ-панель

---

### Фаза 5: Оптимизация и тестирование (2-3 недели)

#### 5.1 Производительность

**Задачи:**
- [ ] Кеширование запросов
- [ ] Eager loading для связей
- [ ] Индексация БД
- [ ] Оптимизация N+1 проблем
- [ ] Redis для сессий и кеша
- [ ] Queue для долгих операций

#### 5.2 Безопасность

**Задачи:**
- [ ] CSRF защита (встроена в Laravel)
- [ ] XSS защита (Blade автоматически)
- [ ] SQL injection защита (Eloquent)
- [ ] Валидация всех входных данных
- [ ] Аудит безопасности
- [ ] Rate limiting

#### 5.3 Тестирование

**Создать тесты:**

```bash
# Unit тесты
php artisan make:test Unit/PublicationTest --unit
php artisan make:test Unit/AuthorTest --unit

# Feature тесты
php artisan make:test Feature/PublicationCrudTest
php artisan make:test Feature/AuthenticationTest
php artisan make:test Feature/ImportTest

# Browser тесты (Laravel Dusk)
composer require --dev laravel/dusk
php artisan dusk:install
php artisan make:test Browser/PublicationFlowTest
```

**Покрытие тестами:**
- [ ] Модели и связи
- [ ] API endpoints
- [ ] Livewire компоненты
- [ ] Бизнес-логика
- [ ] Импорт данных
- [ ] Аутентификация
- [ ] Права доступа

#### 5.4 Документация

**Задачи:**
- [x] Руководство разработчика
- [ ] API документация
- [ ] Руководство пользователя
- [ ] Видео-туториалы
- [ ] Changelog
- [ ] Deployment guide

**Результат:** Стабильная, протестированная система

---

### Фаза 6: Развертывание (1-2 недели)

#### 6.1 Подготовка к production

**Задачи:**
- [ ] Настройка production окружения
- [ ] SSL сертификаты
- [ ] Настройка веб-сервера (Nginx)
- [ ] Настройка PHP-FPM
- [ ] Redis для кеша
- [ ] Supervisor для очередей
- [ ] Мониторинг (New Relic, Sentry)

#### 6.2 Миграция данных

**План:**
1. Создать полную резервную копию legacy БД
2. Запустить скрипты миграции данных
3. Валидация целостности данных
4. Тестирование на production копии
5. Переключение на новую систему

**Скрипты миграции:**

```bash
php artisan make:command MigrateLegacyData
php artisan make:command ValidateDataIntegrity
```

#### 6.3 Параллельная работа

**Стратегия:**
- Legacy система продолжает работать
- Новая система тестируется параллельно
- Синхронизация данных
- Постепенное переключение пользователей
- Rollback план на случай проблем

**Результат:** Production-ready приложение

---

## Детальный план миграции модулей

### Таблица соответствия: Legacy → Laravel

| Legacy модуль | Laravel компонент | Приоритет | Сложность |
|---------------|-------------------|-----------|-----------|
| **UserEnter/** | Laravel Breeze + User Model | 🔴 Высокий | Средняя |
| **MainTable/View/** | Publications/PublicationList | 🔴 Высокий | Средняя |
| **MainTable/Update/** | Publications/PublicationForm | 🔴 Высокий | Средняя |
| **Catalogs/** | Catalogs/* Livewire | 🔴 Высокий | Высокая |
| **Languages/** | lang/ + Middleware | 🟡 Средний | Средняя |
| **Titles/** | JSON переводы | 🟡 Средний | Низкая |
| **DataBases/** | Admin/DatabaseManager | 🟡 Средний | Высокая |
| **Fields/** | Admin/FieldManager | 🟡 Средний | Высокая |
| **Algorithms/** | Services/TextParser | 🟢 Низкий | Высокая |
| **Tree/** | Tree/TreeView | 🟢 Низкий | Средняя |
| **PrimatyUpload/** | Upload/ImportWizard | 🟢 Низкий | Средняя |
| **Configuration/** | config/ + .env | 🟡 Средний | Низкая |
| **Utilities/** | app/Services/ | 🟡 Средний | Низкая |
| **Alarm/** | Laravel Log + Notifications | 🟢 Низкий | Низкая |
| **Visits/** | Middleware + DB Log | 🟢 Низкий | Низкая |
| **DeleteMarked/** | Soft Deletes (_del_mark) | 🟡 Средний | Низкая |
| **Administrator/** | Admin Dashboard | 🟡 Средний | Средняя |
| **LoadToServer/** | Deployment scripts | 🟢 Низкий | Низкая |
| **LogProcessing/** | Laravel Telescope | 🟢 Низкий | Низкая |
| **SystemDataBaseCopies/** | spatie/laravel-backup | 🟢 Низкий | Низкая |

### Детальная карта функций

#### UserEnter (Аутентификация)

**Legacy файлы:**
```
UserEnter/
├── UserEnterLogin.php          → Laravel Breeze login
├── UserEnterAlarmLogin.php     → Custom error page
├── UserEnterUtilities.php      → AuthService
├── LoginButtons.php            → Blade component
├── MaximumAttempts.php         → RateLimiter middleware
├── HTMLRules.php               → Blade layout
└── HTMLNoTitleRules.php        → Guest layout
```

**Функции для миграции:**
- `TestUserPass()` → `Auth::attempt()`
- `GetUserPriority()` → User model accessor
- `SetUserSession()` → Session management
- `CheckLoginAttempts()` → RateLimiter
- `UserListEmpty()` → User::count()

**Laravel реализация:**

```php
// app/Http/Controllers/Auth/AuthenticatedSessionController.php
public function store(LoginRequest $request)
{
    $request->authenticate(); // Встроенная проверка
    $request->session()->regenerate();

    // Установить язык пользователя
    $user = Auth::user();
    session(['locale' => $user->language->code ?? 'en']);

    return redirect()->intended('dashboard');
}
```

#### Catalogs (Справочники)

**Legacy файлы (18!):**
```
Catalogs/
├── CatalogForm.php             → Livewire form component
├── CatalogListForm.php         → Livewire list component
├── CatalogButtons.php          → Blade component
├── FormUtilities.php           → Service class
├── FormPubSelect.php           → Select component
├── DoubleCatalog.php           → Multi-select component
├── Filter.php                  → Filter trait
├── Search.php                  → Search trait
├── Navigation.php              → Pagination
├── Update.php                  → CRUD methods
├── SetSession.php              → State management
├── Screen.php                  → Display logic
├── Common.php                  → Helpers
├── MainUtilities.php           → Service methods
├── CopyPasteBranch.php         → Tree operations
├── SelectFromCatalog.php       → Modal selector
├── ToProc.php                  → Data processor
└── Test.php                    → Validation
```

**Сложные функции:**
- Динамическое создание форм на основе `field_config`
- Автоподстановка с поиском
- Фильтрация с множественными условиями
- Группировка данных (authors → author_groups)
- Связанные выборки (cascading selects)

**Laravel подход:**

```php
// app/Livewire/Catalogs/AuthorCatalog.php
class AuthorCatalog extends Component
{
    use WithPagination, WithFilters, WithSearch;

    public $search = '';
    public $showDeleted = false;

    public function render()
    {
        return view('livewire.catalogs.author-catalog', [
            'authors' => Author::search($this->search)
                ->withDeleted($this->showDeleted)
                ->paginate(15)
        ]);
    }
}
```

#### DataBases (Управление БД)

**Legacy функции:**
```
DataBases/
├── ManagerDBCreate.php         → Migration генератор
├── UserDBCreate.php            → Schema builder
├── DataBaseTest.php            → Integrity checker
├── DataBaseCorrect.php         → Auto-repair
├── DataBaseUpdate.php          → Schema migrator
└── DataBaseUtilities.php       → DB helpers
```

**Критичные функции:**
- `TestManagerTablesExist()` → Проверка таблиц
- `CreateSystemTable()` → Динамическое создание
- `TestManagerTableStructure()` → Валидация схемы
- `CorrectTableStructure()` → Авто-исправление
- `ManagerDataBaseStructureDefinition()` → Определение схемы

**Laravel альтернатива:**

```bash
# Вместо динамического создания - миграции
php artisan make:migration create_system_tables

# Проверка целостности
php artisan migrate:status
php artisan migrate --pretend

# Валидация данных
php artisan make:command ValidateDatabaseIntegrity
```

#### Algorithms (Парсинг данных)

**Legacy функции:**
```
Algorithms/
├── AlgorithmTest.php           → Parser tester
└── AlgService/                 → Parsing engine
```

**Структура алгоритма в БД:**
```sql
CREATE TABLE algorithms (
    id_algorithm INT,
    beg_limit_set VARCHAR(255),   -- Начальный разделитель
    end_limit_set VARCHAR(255),   -- Конечный разделитель
    inner_limit_set VARCHAR(255), -- Внутренний разделитель
    del_symbols VARCHAR(255),     -- Символы для удаления
    ins_symbols VARCHAR(255),     -- Символы для вставки
    reg_expression VARCHAR(255),  -- Регулярное выражение
    alg_remarks VARCHAR(255)      -- Описание
)
```

**Примеры алгоритмов:**
1. Извлечение авторов: между `. ` и `.`
2. Извлечение серии: между `«` и `»`
3. Извлечение года: regex `/\d{4}/`
4. Извлечение тома: поиск `tom` и номера

**Laravel реализация:**

```php
// app/Services/TextParser.php
class TextParser
{
    public function apply(string $text, Algorithm $algorithm): array
    {
        if ($algorithm->reg_expression) {
            return $this->applyRegex($text, $algorithm);
        }

        return $this->applyDelimiters($text, $algorithm);
    }

    private function applyDelimiters(string $text, Algorithm $algorithm): array
    {
        // Логика извлечения по разделителям
    }
}
```

---

## Стратегия баз данных

### Схема миграции данных

#### Опция 1: Прямая миграция (рекомендуется)

```
Legacy БД → Laravel миграции → Новая БД
           ↓
       Сохранить структуру
```

**Преимущества:**
- Сохраняется совместимость
- Легче откат
- Постепенная миграция

**Недостатки:**
- Устаревшие имена полей
- Неоптимальная структура

#### Опция 2: Рефакторинг структуры

```
Legacy БД → Преобразование → Новая улучшенная структура
```

**Преимущества:**
- Современная структура
- Оптимизация
- Лучшие практики

**Недостатки:**
- Больше работы
- Сложнее откат
- Риск ошибок

### Рекомендуемый подход: Гибридный

1. **Фаза 1**: Прямая миграция структуры
2. **Фаза 2**: Постепенный рефакторинг

### Карта таблиц

#### База: `db_manager`

| Таблица | Строк | Сложность | Заметки |
|---------|-------|-----------|---------|
| `user_ident` | ~10 | Средняя | Адаптировать для Laravel Auth |
| `interface_texts` | ~700 | Высокая | Мигрировать в JSON |
| `languages` | ~5 | Низкая | Простая справочная |
| `db_list` | ~5 | Низкая | Список БД |
| `algorithms` | ~30 | Высокая | Сложная логика |
| `coding_table` | ~5 | Низкая | Кодировки |
| `translate_table` | ~500 | Средняя | Транслитерация |
| `db_s_configs` | ~15 | Средняя | Мигрировать в config/ |
| `interface_special_texts` | ~10 | Средняя | Конфигурация UI |
| `visits` | ~1000+ | Низкая | Логи посещений |

#### База: `literature`

| Таблица | Строк | Связи | Сложность |
|---------|-------|-------|-----------|
| `publications` | ~1000+ | Many | Высокая |
| `authors` | ~500 | HasMany | Средняя |
| `author_groups` | ~800 | Pivot | Высокая |
| `publishings` | ~200 | HasMany | Низкая |
| `themes` | ~100 | HasMany | Низкая |
| `theme_sets` | ~150 | Pivot | Средняя |
| `series` | ~50 | HasMany | Низкая |
| `issue_types` | ~10 | Enum | Низкая |
| `magazines` | ~50 | HasMany | Низкая |
| `parts` | ~200 | Tree | Высокая |
| `part_sets` | ~300 | Pivot | Средняя |
| `files` | ~500 | HasMany | Средняя |
| `field_config` | ~50 | Meta | Высокая |
| `table_definitions` | ~10 | Meta | Высокая |
| `collapse_ids` | ~100 | UI State | Низкая |
| `db_configs` | ~10 | Config | Низкая |

### Особенности структуры

#### 1. Группировки через SET

**Legacy подход:**
```sql
author_groups (
    id_author_group INT,
    author_set VARCHAR(255)  -- "10,11,12"
)
```

**Проблема:** Нарушение нормализации, сложные запросы

**Laravel решение:**

**Вариант A:** Сохранить структуру
```php
class AuthorGroup extends Model
{
    protected $casts = [
        'author_set' => 'array'
    ];

    public function authors()
    {
        return Author::whereIn('id', $this->author_set)->get();
    }
}
```

**Вариант B:** Рефакторинг (лучше)
```sql
-- Новая таблица many-to-many
CREATE TABLE author_group_author (
    author_group_id INT,
    author_id INT,
    order INT
)
```

```php
class AuthorGroup extends Model
{
    public function authors()
    {
        return $this->belongsToMany(Author::class)
            ->withPivot('order')
            ->orderBy('order');
    }
}
```

#### 2. Мягкое удаление через `_del_mark`

**Legacy:**
```sql
_del_mark TINYINT DEFAULT 0
```

**Laravel стандарт:**
```sql
deleted_at TIMESTAMP NULL
```

**Рекомендация:** Сохранить `_del_mark` для совместимости

```php
class Publication extends Model
{
    // Свой trait для _del_mark
    use SoftDeletesByMark;

    public function scopeActive($query)
    {
        return $query->where('_del_mark', 0);
    }
}
```

#### 3. Динамическая конфигурация полей

**Legacy:** `field_config` таблица определяет все поля

**Laravel подход:**
- Использовать встроенную систему миграций
- Для динамики: JSON поля + Cast

```php
class Publication extends Model
{
    protected $casts = [
        'meta_fields' => 'array'
    ];
}
```

---

## Тестирование и валидация

### Стратегия тестирования

#### 1. Unit тесты (модели, сервисы)

```php
// tests/Unit/PublicationTest.php
public function test_publication_has_authors()
{
    $publication = Publication::factory()->create();
    $author = Author::factory()->create();

    $publication->authorGroup->authors()->attach($author);

    $this->assertTrue($publication->authorGroup->authors->contains($author));
}
```

#### 2. Feature тесты (бизнес-логика)

```php
// tests/Feature/PublicationCrudTest.php
public function test_can_create_publication()
{
    $response = $this->actingAs($user)
        ->post('/publications', [
            'title' => 'Test Book',
            'issue_year' => '2025'
        ]);

    $response->assertRedirect('/publications');
    $this->assertDatabaseHas('publications', [
        'title' => 'Test Book'
    ]);
}
```

#### 3. Browser тесты (UI flow)

```php
// tests/Browser/PublicationFlowTest.php
public function test_complete_publication_workflow()
{
    $this->browse(function (Browser $browser) {
        $browser->loginAs($user)
            ->visit('/publications')
            ->clickLink('Create')
            ->type('title', 'New Book')
            ->press('Save')
            ->assertSee('Publication created successfully');
    });
}
```

#### 4. Livewire тесты

```php
// tests/Feature/Livewire/PublicationListTest.php
public function test_search_filters_publications()
{
    Publication::factory()->create(['title' => 'Laravel Book']);
    Publication::factory()->create(['title' => 'PHP Book']);

    Livewire::test(PublicationList::class)
        ->set('search', 'Laravel')
        ->assertSee('Laravel Book')
        ->assertDontSee('PHP Book');
}
```

### Checklist валидации данных

После миграции проверить:

- [ ] Количество записей в каждой таблице совпадает
- [ ] Связи между таблицами работают корректно
- [ ] Все индексы созданы
- [ ] Foreign keys установлены
- [ ] Триггеры мигрированы (если есть)
- [ ] Хранимые процедуры мигрированы (если есть)
- [ ] Кодировка UTF-8 везде
- [ ] Collation правильная (utf8mb4_unicode_ci)

### Скрипт валидации

```bash
php artisan make:command ValidateMigration
```

```php
class ValidateMigration extends Command
{
    public function handle()
    {
        $this->info('Validating migration...');

        // Проверка количества записей
        $legacyCount = DB::connection('legacy')
            ->table('publications')->count();
        $newCount = Publication::count();

        if ($legacyCount !== $newCount) {
            $this->error("Publications count mismatch!");
            return 1;
        }

        // Проверка связей
        $publicationsWithoutAuthors = Publication::whereDoesntHave('authorGroup')->count();
        if ($publicationsWithoutAuthors > 0) {
            $this->warn("Found {$publicationsWithoutAuthors} publications without authors");
        }

        $this->info('Validation complete!');
        return 0;
    }
}
```

---

## Риски и их митигация

### Высокие риски

#### Риск 1: Потеря данных при миграции

**Вероятность:** Средняя
**Влияние:** Критическое

**Митигация:**
- Полное резервное копирование перед началом
- Тестирование на копии БД
- Поэтапная миграция
- Валидация после каждого этапа
- Rollback план

#### Риск 2: Несовместимость алгоритмов парсинга

**Вероятность:** Высокая
**Влияние:** Высокое

**Митигация:**
- Детальное документирование алгоритмов
- Unit тесты для каждого алгоритма
- Сравнение результатов legacy vs новый
- Поддержка старых алгоритмов параллельно

#### Риск 3: Производительность

**Вероятность:** Средняя
**Влияние:** Среднее

**Митигация:**
- Load testing
- Профилирование запросов
- Индексирование
- Кеширование
- Query optimization

### Средние риски

#### Риск 4: Проблемы с многоязычностью

**Вероятность:** Средняя
**Влияние:** Среднее

**Митигация:**
- Полная миграция interface_texts
- Тестирование на обоих языках
- Fallback на английский

#### Риск 5: Сложность динамических полей

**Вероятность:** Высокая
**Влияние:** Среднее

**Митигация:**
- Использовать JSON для метаданных
- Постепенная миграция field_config
- Оставить совместимость с БД

### Низкие риски

#### Риск 6: Изменение UI/UX

**Вероятность:** Низкая
**Влияние:** Низкое

**Митигация:**
- Сохранить похожий интерфейс
- Обучение пользователей
- Документация изменений

---

## Метрики успеха

### Технические метрики

- [ ] 100% функционала мигрировано
- [ ] 0 потерянных записей
- [ ] < 500ms среднее время ответа
- [ ] > 95% покрытие тестами
- [ ] 0 критических багов в production

### Бизнес-метрики

- [ ] Время создания записи сократилось
- [ ] Пользователи довольны новым UI
- [ ] Нет жалоб на потерю данных
- [ ] Время обучения < 1 дня
- [ ] ROI достигнут за 6 месяцев

---

## Следующие шаги

### Немедленные действия

1. **Одобрение плана** руководством
2. **Создание команды** (разработчики, тестировщики)
3. **Настройка окружения** (Git, Docker, CI/CD)
4. **Начало Фазы 0** (подготовка)

### Долгосрочные цели

После успешной миграции:
- API для внешних систем
- Mobile приложение
- Интеграция с внешними БД
- Machine learning для классификации
- Продвинутая аналитика

---

## Контакты и поддержка

**Документация:**
- [DEVELOPER_GUIDE_RU.md](DEVELOPER_GUIDE_RU.md) - Руководство разработчика
- [QUICKSTART.md](QUICKSTART.md) - Быстрый старт
- [LIVEWIRE_GUIDE.md](LIVEWIRE_GUIDE.md) - Руководство по Livewire

**Вопросы:**
- Создать Issue в репозитории
- Обсудить в команде

---

**Версия:** 1.0
**Дата:** 2025-10-10
**Статус:** Draft
