# Рабочие процессы администратора (Admin Workflows)

Этот документ описывает ключевые рабочие процессы администратора в системе Sifriot. Диаграммы и описания помогут понять, как функционируют различные части административной панели.

## 1. Аутентификация и Доступ (Authentication & Access)

Административный раздел защищен middleware `auth` и `role:admin`.

```mermaid
sequenceDiagram
    participant User as Пользователь
    participant Middleware as Middleware (Auth/Role)
    participant AdminPanel as Админ-панель
    participant Login as Страница входа

    User->>AdminPanel: Попытка доступа к /admin/*
    Middleware->>User: Проверка авторизации
    alt Не авторизован
        Middleware->>Login: Перенаправление на вход
        User->>Login: Ввод учетных данных
        Login->>Middleware: Проверка данных
    else Авторизован, но не админ
        Middleware->>User: Ошибка 403 (Доступ запрещен)
    end
    alt Авторизован и Админ
        Middleware->>AdminPanel: Доступ разрешен
        AdminPanel->>User: Отображение Dashboard
    end
```

## 2. Обзор Панели Управления (Dashboard Overview)

**Маршрут:** `/dashboard`
**Компонент:** `App\Livewire\Admin\MetadataReviewDashboard`

Главная страница администратора предоставляет сводку по состоянию системы, в частности по очереди проверки метаданных.

```mermaid
graph TD
    Dashboard[Dashboard /dashboard]
    Stats[Статистика]
    ReviewQueue[Очередь проверки]
    RecentActivity[Недавняя активность]

    Dashboard --> Stats
    Dashboard --> ReviewQueue
    Dashboard --> RecentActivity

    subgraph "Ключевые метрики"
        Stats --> Pending[Ожидает проверки]
        Stats --> Approved[Утверждено]
        Stats --> Rejected[Отклонено]
    end
```

## 3. Управление Файлами (File Management)

**Маршрут:** `/admin/files`
**Компонент:** `App\Livewire\Admin\FileManagement`

Централизованное управление файлами, включая загрузку, сканирование папок и регистрацию новых поступлений.

```mermaid
flowchart LR
    FileMgmt[Управление файлами] --> Upload[Загрузка файлов]
    FileMgmt --> Scan[Сканирование папок]
    FileMgmt --> Registration[Регистрация в БД]
    
    Scan --> BulkScanner[Bulk Folder Scanner]
    BulkScanner -- Результаты --> SelectFiles[Выбор файлов]
    SelectFiles --> Registration
```

### 3.1 Сканирование и Регистрация (Scanning & Registration)

**Маршрут:** `/admin/bulk-scan` -> `/admin/scan-results/{id}`
**Компоненты:** `BulkFolderScanner`, `ScanResultsViewer`

Процесс массового добавления файлов из локальных директорий библиотеки.

```mermaid
sequenceDiagram
    participant Admin as Администратор
    participant Scanner as BulkFolderScanner
    participant Results as ScanResultsViewer
    participant DB as База Данных

    Admin->>Scanner: Выбор директории для сканирования
    Scanner->>Scanner: Сканирование файловой системы
    Scanner->>DB: Сохранение результатов сканирования (ScanJob)
    Scanner->>Admin: Перенаправление на результаты
    Admin->>Results: Просмотр найденных файлов
    Admin->>Results: Выбор файлов для импорта
    Results->>DB: Создание записей публикаций
    DB-->>Admin: Подтверждение импорта
    Note over DB: Создается запись в file_registration_logs
```

## 4. Управление Фильтрацией (Filtration Management)

**Маршрут:** `/admin/filtration`
**Компонент:** `App\Livewire\Admin\FiltrationManagement`

Управление справочниками системы. Поддерживает CRUD операции для следующих сущностей:
- **Content Types** (Типы контента): Книги, Журналы, Статьи и т.д.
- **Genres** (Жанры): Фантастика, История и т.д.
- **Themes** (Темы): Тематические подборки.
- **Sections** (Разделы): Иерархическая структура каталога.
- **Authors** (Авторы)
- **Publishers** (Издатели)

```mermaid
classDiagram
    class FiltrationManagement {
        +activeTab: string
        +switchTab(tab)
        +create/edit/delete{Entity}()
    }
    class ContentType {
        +Name (EN/RU/HE)
        +Slug
        +Icon
    }
    class Section {
        +Name (EN/RU/HE)
        +ParentID
        +SortOrder
    }

    FiltrationManagement --> ContentType : Manage
    FiltrationManagement --> Section : Manage
```

## 5. Проверка Метаданных (Metadata Review)

**Компонент:** `MetadataReviewDashboard`, `MetadataReviewQueue`

Критический процесс проверки качества данных перед публикацией.

### Возможности фильтрации:
- **Статус метаданных:** Все, Ожидает, Обработано, Подтверждено, Отклонено, Ошибка.
- **Формат файла:** PDF, DJVU, DOC, FB2, EPUB.
- **Дата:** За 1/7/30 дней.
- **Атрибуты публикации:** По автору, жанру, разделу.

### Действия с записями:
- **Подтвердить (Confirm):** Данные корректны, публикация становится доступной.
- **Отклонить (Reject):** Данные неверны или дубликат.
- **Повторное извлечение (Re-extract):** Запуск извлечения метаданных заново (в том числе с AI).
- **AI Извлечение:** Использование Gemini для анализа текста (если настроено).

```mermaid
stateDiagram-v2
    [*] --> Pending: Файл загружен
    Pending --> Processing: Извлечение метаданных
    Processing --> Processed: Данные извлечены
    Processing --> Failed: Ошибка извлечения
    
    Processed --> Reviewing: Админ проверяет
    Reviewing --> Confirmed: Подтверждено
    Reviewing --> Rejected: Отклонено
    
    Failed --> Pending: Повторное извлечение
    Rejected --> Pending: Повторное извлечение
    
    Confirmed --> [*]: Публикация в каталоге
```

---
*Документ автоматически сгенерирован для проекта Sifriot.*
