# E2E Testing Guide

This document provides comprehensive guidance for running and understanding the End-to-End (E2E) tests for this Laravel Livewire application.

## Overview

This application uses **Laravel's built-in testing framework** with **Livewire Testing Utilities** to provide comprehensive E2E test coverage without requiring browser automation tools like Selenium.

## Test Structure

```
tests/
├── Feature/
│   ├── E2E/
│   │   ├── AuthenticationFlowTest.php      # Complete auth flows
│   │   ├── ProfileManagementTest.php       # User profile CRUD
│   │   ├── LanguageSwitchingTest.php       # i18n functionality
│   │   └── CompleteUserJourneyTest.php     # Full integration test
│   └── Publications/
│       └── PublicationListTest.php         # Publications feature tests
└── Unit/                                    # Unit tests
```

## Test Coverage

### 1. Authentication Flow Tests (`AuthenticationFlowTest.php`)

Tests the complete authentication lifecycle:

- ✅ User registration flow
- ✅ User login flow with valid/invalid credentials
- ✅ User logout flow
- ✅ Password reset flow
- ✅ Guest redirection to login for protected routes
- ✅ Authenticated user access to protected routes
- ✅ Email uniqueness validation
- ✅ Password confirmation validation
- ✅ Complete authentication journey (register → login → logout → login)

### 2. Profile Management Tests (`ProfileManagementTest.php`)

Tests user profile functionality:

- ✅ Profile page access (authenticated/guest)
- ✅ Update profile information (name, email)
- ✅ Email validation
- ✅ Email verification on change
- ✅ Password updates with current password verification
- ✅ Password confirmation requirement
- ✅ Password minimum length validation
- ✅ Account deletion with password confirmation
- ✅ Complete profile management journey

### 3. Publication List Tests (`PublicationListTest.php`)

Tests the core publications feature:

- ✅ Render publication list page
- ✅ Display publications
- ✅ Search publications by title
- ✅ Filter deleted publications
- ✅ Toggle between active/deleted views
- ✅ Soft delete publications
- ✅ Restore deleted publications
- ✅ Pagination
- ✅ URL query persistence (search, showDeleted)
- ✅ Ordering by upload date
- ✅ Eager loading of relationships
- ✅ Guest user access

### 4. Language Switching Tests (`LanguageSwitchingTest.php`)

Tests internationalization features:

- ✅ Switch to Russian (ru)
- ✅ Switch to English (en)
- ✅ Invalid locale handling
- ✅ Redirect back to previous page
- ✅ Locale persistence across requests
- ✅ Authenticated user language switching
- ✅ Guest user language switching
- ✅ Language switching with query parameters
- ✅ Complete multilingual user journey

### 5. Complete User Journey Test (`CompleteUserJourneyTest.php`)

A comprehensive integration test simulating a full user experience:

**Phase 1: Guest Experience**
- Landing on homepage
- Viewing publications
- Searching publications
- Protected route redirection
- Language switching

**Phase 2: Registration**
- Registration form submission
- Account creation verification

**Phase 3: Authenticated Actions**
- Dashboard access
- Language switching
- Publication viewing and searching

**Phase 4: Profile Management**
- Profile viewing
- Profile updates
- Password changes

**Phase 5: Session Management**
- Logout
- Public page access while logged out
- Login with new password

**Phase 6: Advanced Features**
- Soft deleting publications
- Viewing deleted publications
- Restoring publications

**Phase 7: Data Verification**
- Complete data integrity check

## Running Tests

### Run All E2E Tests

```bash
# Run all tests
composer test

# Or using PHPUnit directly
php artisan test

# Run only E2E tests
php artisan test --testsuite=Feature

# Run specific test file
php artisan test tests/Feature/E2E/CompleteUserJourneyTest.php
```

### Run Specific Test Methods

```bash
# Run a specific test method
php artisan test --filter=complete_application_user_journey

# Run all authentication tests
php artisan test --filter=AuthenticationFlowTest
```

### Run Tests with Coverage

```bash
# Generate coverage report (requires Xdebug or PCOV)
php artisan test --coverage

# Generate HTML coverage report
php artisan test --coverage-html coverage
```

### Parallel Testing

```bash
# Run tests in parallel (faster execution)
php artisan test --parallel

# Specify number of processes
php artisan test --parallel --processes=4
```

## Test Database

The tests use the `RefreshDatabase` trait, which:

1. Migrates the test database before each test
2. Rolls back all changes after each test
3. Ensures test isolation

### Configure Test Database

In [.env.testing](../.env.testing) (create if it doesn't exist):

```env
APP_ENV=testing
APP_KEY=base64:YOUR_APP_KEY_HERE

DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

Using SQLite in-memory database provides fast test execution.

## Factories

Test data is generated using Laravel factories:

- **UserFactory** - Creates test users
- **PublicationFactory** - Creates test publications

### PublicationFactory Usage

```php
// Create a basic publication
$publication = Publication::factory()->create();

// Create a deleted publication
$publication = Publication::factory()->deleted()->create();

// Create an active publication
$publication = Publication::factory()->active()->create();

// Create publication with relationships
$publication = Publication::factory()->withRelationships()->create();

// Create multiple publications
Publication::factory()->count(10)->create();
```

## Writing New E2E Tests

### Test Structure Template

```php
<?php

namespace Tests\Feature\E2E;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyFeatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_performs_expected_behavior()
    {
        // Arrange - Set up test data
        $user = User::factory()->create();

        // Act - Perform the action
        $response = $this->actingAs($user)->get('/my-route');

        // Assert - Verify the result
        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}
```

### Testing Livewire Components

```php
use Livewire\Livewire;

Livewire::test('component.name')
    ->set('property', 'value')        // Set component property
    ->call('methodName')              // Call component method
    ->assertSee('Expected Text')      // Assert text is visible
    ->assertDontSee('Hidden Text')    // Assert text is not visible
    ->assertSet('property', 'value')  // Assert property value
    ->assertHasErrors('field')        // Assert validation errors
    ->assertRedirect('/path');        // Assert redirect occurred
```

## Best Practices

### 1. Test Isolation

Each test should be independent:

```php
// ✅ Good - Creates its own data
public function test_user_can_login()
{
    $user = User::factory()->create(['email' => 'test@example.com']);
    // ... test logic
}

// ❌ Bad - Depends on external data
public function test_user_can_login()
{
    $user = User::where('email', 'test@example.com')->first();
    // ... test logic
}
```

### 2. Descriptive Test Names

Use clear, descriptive test names:

```php
// ✅ Good
public function authenticated_user_can_update_their_profile()

// ❌ Bad
public function test1()
```

### 3. Arrange-Act-Assert Pattern

Structure tests clearly:

```php
public function test_example()
{
    // Arrange - Set up the test scenario
    $user = User::factory()->create();
    $publication = Publication::factory()->create();

    // Act - Perform the action being tested
    $response = $this->actingAs($user)->get('/publications');

    // Assert - Verify the expected outcome
    $response->assertStatus(200);
    $response->assertSee($publication->title);
}
```

### 4. Test One Thing

Each test should verify one behavior:

```php
// ✅ Good - Tests one thing
public function user_can_update_name()
{
    // Test name update
}

public function user_can_update_email()
{
    // Test email update
}

// ❌ Bad - Tests multiple things
public function user_can_update_profile()
{
    // Tests name, email, password all at once
}
```

### 5. Use Factories

Always use factories instead of manual creation:

```php
// ✅ Good
$user = User::factory()->create(['name' => 'Test User']);

// ❌ Bad
$user = new User();
$user->name = 'Test User';
$user->email = 'test@example.com';
$user->password = Hash::make('password');
$user->save();
```

## Continuous Integration

Add to your CI pipeline (e.g., GitHub Actions):

```yaml
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.2'

      - name: Install Dependencies
        run: composer install

      - name: Run Tests
        run: php artisan test
```

## Debugging Tests

### View Test Output

```bash
# Verbose output
php artisan test --verbose

# Stop on first failure
php artisan test --stop-on-failure

# Display test output
php artisan test --display-errors
```

### Debug Specific Test

```php
// Add dd() or dump() to inspect data
public function test_something()
{
    $user = User::factory()->create();
    dd($user); // Dump and die

    // Or
    dump($user); // Just dump, continue execution
}
```

### Enable Test Logging

In [phpunit.xml](../phpunit.xml), uncomment:

```xml
<env name="LOG_CHANNEL" value="single"/>
```

Check logs at `storage/logs/laravel.log`

## Common Issues

### Issue: Database Connection Error

**Solution:** Ensure `.env.testing` is configured properly:

```env
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

### Issue: Class Not Found

**Solution:** Regenerate autoload files:

```bash
composer dump-autoload
```

### Issue: Route Not Found

**Solution:** Clear cached routes before testing:

```bash
php artisan route:clear
php artisan config:clear
```

### Issue: Livewire Component Not Found

**Solution:** Ensure component is registered:

```bash
php artisan livewire:list
```

## Performance Tips

1. **Use SQLite in-memory** - Fastest database for tests
2. **Run tests in parallel** - `php artisan test --parallel`
3. **Limit factory relationships** - Only create needed data
4. **Use `RefreshDatabase`** - Instead of `DatabaseMigrations`

## Additional Resources

- [Laravel Testing Documentation](https://laravel.com/docs/11.x/testing)
- [Livewire Testing Documentation](https://livewire.laravel.com/docs/testing)
- [PHPUnit Documentation](https://phpunit.de/documentation.html)

## Support

For issues or questions about the tests, please:

1. Check this documentation
2. Review existing test files for examples
3. Consult Laravel and Livewire documentation
4. Open an issue in the project repository
