<?php

namespace Tests\Feature;

use App\Livewire\Admin\FileRegistrationForm;
use App\Models\ContentType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class FixFileRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_existing_file_fails_with_wrong_disk()
    {
        // Mock the library disk
        Storage::fake('library');
        // We do NOT fake local disk to simulate the real environment mismatch,
        // or we fake it but ensure it's empty/different.
        Storage::fake('local');

        // Create a file in library disk
        Storage::disk('library')->put('test-book.pdf', 'dummy content');

        // Verify file exists in library
        $this->assertTrue(Storage::disk('library')->exists('test-book.pdf'));
        // Verify file does NOT exist in local (this is the key: if code looks in local, it fails)
        $this->assertFalse(Storage::disk('local')->exists('test-book.pdf'));

        // Create content type
        $contentType = ContentType::factory()->create();

        // Create admin user
        $user = User::factory()->create();
        $this->actingAs($user);

        // Attempt to register
        Livewire::test(FileRegistrationForm::class)
            ->set('registrationMode', 'register_existing')
            ->set('selectedFilePath', 'test-book.pdf')
            ->set('publicationTitle', 'Test Book')
            ->call('registerFile')
            ->assertSet('publicationTitle', '') // Reset check
            ->assertDispatched('file-registered-successfully')
            ->assertDontSee('Unable to save file. Check server permissions.');
    }
}
