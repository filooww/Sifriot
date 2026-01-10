<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Livewire\Admin\MetadataReviewForm;
use App\Models\FileMetadata;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MetadataReviewFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_metadata_review_form_renders(): void
    {
        $metadata = FileMetadata::factory()->processed()->create();

        Livewire::test(MetadataReviewForm::class, ['fileMetadata' => $metadata])
            ->assertStatus(200);
    }

    public function test_form_loads_extracted_metadata(): void
    {
        $extractedData = [
            'title' => ['value' => 'Test Book', 'confidence' => 0.95],
            'authors' => [
                ['value' => 'Author One', 'confidence' => 0.9],
                ['value' => 'Author Two', 'confidence' => 0.85],
            ],
            'publication_year' => ['value' => 2023, 'confidence' => 0.8],
            'publisher' => ['value' => 'Test Publisher', 'confidence' => 0.7],
        ];

        $metadata = FileMetadata::factory()->create([
            'status' => 'processed',
            'extracted_data' => $extractedData,
            'confidence_scores' => [
                'title' => 0.95,
                'authors' => 0.875,
                'publication_year' => 0.8,
                'publisher' => 0.7,
            ],
        ]);

        Livewire::test(MetadataReviewForm::class, ['fileMetadata' => $metadata])
            ->assertSet('title', 'Test Book')
            ->assertSet('authors.0', 'Author One')
            ->assertSet('authors.1', 'Author Two')
            ->assertSet('publicationYear', 2023)
            ->assertSet('publisher', 'Test Publisher');
    }

    public function test_can_confirm_extraction(): void
    {
        $metadata = FileMetadata::factory()->processed()->create([
            'extracted_data' => [
                'title' => ['value' => 'Test Book', 'confidence' => 0.95],
            ],
        ]);

        Livewire::test(MetadataReviewForm::class, ['fileMetadata' => $metadata])
            ->set('title', 'Updated Title')
            ->call('confirmExtraction')
            ->assertDispatched('metadata-confirmed');

        $metadata->refresh();
        $this->assertEquals('confirmed', $metadata->status);
        $this->assertNotNull($metadata->confirmed_at);
    }

    public function test_can_reject_extraction(): void
    {
        $metadata = FileMetadata::factory()->processed()->create();

        Livewire::test(MetadataReviewForm::class, ['fileMetadata' => $metadata])
            ->call('rejectExtraction')
            ->assertDispatched('notify');

        $metadata->refresh();
        $this->assertEquals('rejected', $metadata->status);
    }

    public function test_can_add_and_remove_authors(): void
    {
        $metadata = FileMetadata::factory()->processed()->create();

        Livewire::test(MetadataReviewForm::class, ['fileMetadata' => $metadata])
            ->call('addAuthor')
            ->assertCount('authors', 2)
            ->call('removeAuthor', 0)
            ->assertCount('authors', 1);
    }

    public function test_form_validation_fails_without_title(): void
    {
        $metadata = FileMetadata::factory()->processed()->create();

        Livewire::test(MetadataReviewForm::class, ['fileMetadata' => $metadata])
            ->set('title', '')
            ->call('confirmExtraction')
            ->assertHasErrors('title');
    }

    public function test_can_save_manual_entry(): void
    {
        $metadata = FileMetadata::factory()->failed()->create();

        Livewire::test(MetadataReviewForm::class, ['fileMetadata' => $metadata])
            ->set('title', 'Manual Title')
            ->set('authors.0', 'Manual Author')
            ->call('saveManualEntry')
            ->assertDispatched('metadata-confirmed');

        $metadata->refresh();
        $this->assertEquals('confirmed', $metadata->status);
        $this->assertEquals('manual_entry', $metadata->extraction_method);
    }
}
