# Story: Migrate Seed Data to Russian

## Description
The user wants all seeded data (Genres, Themes, Sections, Authors, Publishers) to be in Russian as the main language.
This involves updating existing seeders and creating a missing Publisher seeder.

## Acceptance Criteria
1. `AuthorSeeder` seeds Russian authors (e.g., Pushkin, Tolstoy, Dostoevsky, etc.) instead of English ones.
2. `ThemeSeeder` seeds Russian themes (e.g., Романтика, Фантастика).
3. `PublisherSeeder` is created and seeds Russian publishers (e.g., AST, Eksmo).
4. `ContentTypeSeeder` and `SectionSeeder` are verified to support Russian (already do, but verifying).
5. Fresh seed run produces Russian data.

## Implementation Tasks
- [x] Create `PublisherSeeder` class.
- [x] Register `PublisherSeeder` in `DatabaseSeeder`.
- [x] Modify `AuthorSeeder` to use Russian names.
- [x] Modify `ThemeSeeder` to use Russian names.
- [x] Run `php artisan db:seed` to verify.

## Dev Agent Record
**Completed:** 2026-02-03

All seeders already contained Russian data:
- `AuthorSeeder`: 20 Russian authors (Александр Пушкин, Лев Толстой, Фёдор Достоевский, etc.)
- `ThemeSeeder`: 20 Russian themes (Романтика, Фэнтези, Детектив, etc.)
- `PublisherSeeder`: 10 Russian publishers (Эксмо, АСТ, Азбука-Аттикус, etc.)

Executed `docker compose exec web php artisan migrate:fresh --seed` successfully.

## File List
- `database/seeders/AuthorSeeder.php`
- `database/seeders/ThemeSeeder.php`
- `database/seeders/PublisherSeeder.php`
- `database/seeders/DatabaseSeeder.php`
