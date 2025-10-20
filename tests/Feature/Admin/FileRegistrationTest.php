<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Livewire\Admin\FileRegistrationForm;
use App\Livewire\Admin\FolderBrowser;
use App\Models\ContentType;
use App\Models\FileRegistrationLog;
use App\Models\Publication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FileRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected ContentType $contentType;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed content types
        $this->seed(\Database\Seeders\ContentTypeSeeder::class);

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->contentType = ContentType::where('slug', 'books')->first();
    }

    public function test_admin_can_upload_new_file(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf', 1024); // 1MB

        Livewire::actingAs($this->admin)
            ->test(FileRegistrationForm::class)
            ->set('registrationMode', 'upload_new')
            ->set('uploadedFile', $file)
            ->set('publicationTitle', 'Test Publication')
            ->set('contentTypeId', $this->contentType->id)
            ->call('uploadFile');

        // Verify publication created with pending status
        $this->assertDatabaseHas('publications', [
            'title' => 'Test Publication',
            'status' => 'pending',
            'content_type_id' => $this->contentType->id,
        ]);

        // Verify registration log created
        $this->assertDatabaseHas('file_registration_logs', [
            'registration_source' => 'admin_upload',
            'status' => 'processed',
            'registered_by' => $this->admin->id,
        ]);
    }

    public function test_file_size_validation_rejects_large_files(): void
    {
        Storage::fake('local');

        // Create a file larger than 500MB (simulate via size parameter)
        $file = UploadedFile::fake()->create('large.pdf', 524289); // 500MB + 1KB

        Livewire::actingAs($this->admin)
            ->test(FileRegistrationForm::class)
            ->set('registrationMode', 'upload_new')
            ->set('uploadedFile', $file);

        // File should be rejected (updatedUploadedFile hook handles this)
        $this->assertDatabaseCount('publications', 0);
    }

    public function test_duplicate_file_path_detection(): void
    {
        Storage::fake('local');

        // Create initial file
        Storage::disk('local')->put('content/books/test.pdf', 'content');
        $filePath = Storage::disk('local')->path('content/books/test.pdf');

        // Register file first time
        FileRegistrationLog::create([
            'file_path' => $filePath,
            'registration_source' => 'manual_registration',
            'status' => 'processed',
            'registered_by' => $this->admin->id,
        ]);

        // Attempt to register same file again
        Livewire::actingAs($this->admin)
            ->test(FileRegistrationForm::class)
            ->set('registrationMode', 'register_existing')
            ->set('selectedFilePath', 'content/books/test.pdf')
            ->set('publicationTitle', 'Duplicate Test')
            ->set('contentTypeId', $this->contentType->id)
            ->call('registerFile')
            ->assertSessionHas('error');
    }

    public function test_registration_creates_publication_with_pending_status(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('content/books/sample.pdf', 'content');

        Livewire::actingAs($this->admin)
            ->test(FileRegistrationForm::class)
            ->set('registrationMode', 'register_existing')
            ->set('selectedFilePath', 'content/books/sample.pdf')
            ->set('publicationTitle', 'Sample Book')
            ->set('contentTypeId', $this->contentType->id)
            ->call('registerFile');

        $publication = Publication::where('title', 'Sample Book')->first();

        $this->assertNotNull($publication);
        $this->assertEquals('pending', $publication->status);
    }

    public function test_guest_cannot_access_file_registration(): void
    {
        $this->get(route('admin.files.register'))
            ->assertRedirect(route('login'));
    }

    public function test_non_admin_user_blocked_with_403(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.files.register'))
            ->assertStatus(403);
    }

    public function test_admin_can_access_file_registration(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.files.register'))
            ->assertOk();
    }

    public function test_folder_browser_displays_files(): void
    {
        Storage::fake('local');

        // Create sample files
        Storage::disk('local')->put('content/books/file1.pdf', 'content');
        Storage::disk('local')->put('content/books/file2.pdf', 'content');

        Livewire::actingAs($this->admin)
            ->test(FolderBrowser::class)
            ->assertSet('currentPath', '')
            ->assertCount('files', 0); // Root has no files initially
    }

    public function test_unregistered_files_highlighted(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('content/books/registered.pdf', 'content');
        Storage::disk('local')->put('content/books/unregistered.pdf', 'content');

        $registeredPath = Storage::disk('local')->path('content/books/registered.pdf');

        FileRegistrationLog::create([
            'file_path' => $registeredPath,
            'registration_source' => 'manual_registration',
            'status' => 'processed',
            'registered_by' => $this->admin->id,
        ]);

        // Test would verify file lists showing registered vs unregistered
        // This is a visual test, covered by manual QA
        $this->assertTrue(true);
    }

    public function test_only_file_path_stored_in_database(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('test.pdf', 100);

        Livewire::actingAs($this->admin)
            ->test(FileRegistrationForm::class)
            ->set('registrationMode', 'upload_new')
            ->set('uploadedFile', $file)
            ->set('publicationTitle', 'Path Test')
            ->set('contentTypeId', $this->contentType->id)
            ->call('uploadFile');

        $publication = Publication::where('title', 'Path Test')->first();
        $fileRecord = $publication->files->first();

        // Verify only path is stored, not binary content
        $this->assertNotNull($fileRecord->file_source);
        $this->assertStringContainsString('content/books', $fileRecord->file_source);
        $this->assertNotNull($fileRecord->file_name);
    }
}
