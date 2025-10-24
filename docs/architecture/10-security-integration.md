# 10. Security Integration

## 10.1 Existing Security

- Laravel Breeze authentication (bcrypt passwords)
- CSRF protection on all forms
- XSS prevention (Blade escaping)
- SQL injection prevention (Eloquent ORM)

## 10.2 Enhancement Security

**Role-Based Access Control:**
- Guest (unauthenticated): Browse limited, search
- User (authenticated): Full access, download, engagement
- Admin: All + upload, edit, delete, moderation

**Implementation:**
- Middleware: `EnsureUserRole`
- Policies: `PublicationPolicy`, `CommentPolicy`
- User model: `role` enum column

**File Download Authorization:**
- Middleware: `DownloadAuthorization`
- Policy check before serving file
- Download logging and rate limiting (60/minute)

**File Path Validation:**
```php
// FileStorageService validates all paths against configured library paths
public function validatePath(string $filePath): string
{
    $realPath = realpath($filePath);

    // Get all configured library paths from database
    $configuredPaths = LibraryPath::active()->pluck('path')->toArray();

    // Add internal storage path
    $configuredPaths[] = storage_path('app/content');

    // Check if path is within any configured location
    $isValid = false;
    foreach ($configuredPaths as $allowedPath) {
        if (str_starts_with($realPath, realpath($allowedPath))) {
            $isValid = true;
            break;
        }
    }

    if (!$isValid) {
        throw new InvalidFilePathException("Path not within configured library paths");
    }

    return $realPath;
}
```

**Rate Limiting:**
- Authentication: 5 attempts/minute
- Downloads: 60/minute
- Comments: 10/hour

**Input Validation:**
- Form Requests for complex validation
- MIME type verification (not just extension)
- File size limits (500MB)

**Security Testing:**
- Path traversal tests
- Authorization bypass attempts
- XSS/SQL injection tests

---
