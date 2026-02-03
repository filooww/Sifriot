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
- [ ] Create `PublisherSeeder` class.
- [ ] Register `PublisherSeeder` in `DatabaseSeeder`.
- [ ] Modify `AuthorSeeder` to use Russian names.
- [ ] Modify `ThemeSeeder` to use Russian names.
- [ ] Run `php artisan db:seed` to verify.
