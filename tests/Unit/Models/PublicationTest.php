<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Publication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_stores_original_folder_path(): void
    {
        $publication = Publication::factory()->create([
            'original_folder_path' => 'content/books/fiction',
        ]);

        $this->assertDatabaseHas('publications', [
            'id_publication' => $publication->id_publication,
            'original_folder_path' => 'content/books/fiction',
        ]);

        $this->assertEquals('content/books/fiction', $publication->original_folder_path);
    }
}
