<?php

declare(strict_types=1);

namespace Tests\Feature\Models;

use App\Models\Author;
use App\Models\AuthorGroup;
use App\Models\File;
use App\Models\IssueType;
use App\Models\Magazine;
use App\Models\Part;
use App\Models\Publication;
use App\Models\Publishing;
use App\Models\Theme;
use App\Models\ThemeSet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicationRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_publication_has_many_authors_through_pivot_table(): void
    {
        $publication = Publication::factory()->create();
        $authors = Author::factory()->count(3)->create();

        $publication->authors()->attach($authors->pluck('id_author'));

        $this->assertCount(3, $publication->authors);
        $this->assertInstanceOf(Author::class, $publication->authors->first());
    }

    public function test_publication_authors_are_ordered_by_pivot_order_column(): void
    {
        $publication = Publication::factory()->create();
        $author1 = Author::factory()->create();
        $author2 = Author::factory()->create();
        $author3 = Author::factory()->create();

        $publication->authors()->attach([
            $author1->id_author => ['order' => 3],
            $author2->id_author => ['order' => 1],
            $author3->id_author => ['order' => 2],
        ]);

        $orderedAuthors = $publication->authors;
        $this->assertEquals($author2->id_author, $orderedAuthors[0]->id_author);
        $this->assertEquals($author3->id_author, $orderedAuthors[1]->id_author);
        $this->assertEquals($author1->id_author, $orderedAuthors[2]->id_author);
    }

    public function test_author_belongs_to_many_publications(): void
    {
        $author = Author::factory()->create();
        $publications = Publication::factory()->count(2)->create();

        $author->publications()->attach($publications->pluck('id_publication'));

        $this->assertCount(2, $author->publications);
        $this->assertInstanceOf(Publication::class, $author->publications->first());
    }

    public function test_publication_has_many_themes_through_pivot_table(): void
    {
        $publication = Publication::factory()->create();
        $themes = Theme::factory()->count(3)->create();

        $publication->themes()->attach($themes->pluck('id_theme'));

        $this->assertCount(3, $publication->themes);
        $this->assertInstanceOf(Theme::class, $publication->themes->first());
    }

    public function test_theme_belongs_to_many_publications(): void
    {
        $theme = Theme::factory()->create();
        $publications = Publication::factory()->count(2)->create();

        $theme->publications()->attach($publications->pluck('id_publication'));

        $this->assertCount(2, $theme->publications);
        $this->assertInstanceOf(Publication::class, $theme->publications->first());
    }

    public function test_publication_has_many_files(): void
    {
        $publication = Publication::factory()->create();

        File::factory()->count(3)->create([
            'id_publication' => $publication->id_publication,
        ]);

        $this->assertCount(3, $publication->files);
        $this->assertInstanceOf(File::class, $publication->files->first());
    }

    public function test_file_belongs_to_publication(): void
    {
        $publication = Publication::factory()->create();
        $file = File::factory()->create([
            'id_publication' => $publication->id_publication,
        ]);

        $this->assertEquals($publication->id_publication, $file->publication->id_publication);
    }

    public function test_publication_belongs_to_publishing(): void
    {
        $publishing = Publishing::factory()->create();
        $publication = Publication::factory()->create([
            'id_publishing' => $publishing->id_publishing,
        ]);

        $this->assertEquals($publishing->id_publishing, $publication->publishing->id_publishing);
    }

    public function test_publishing_has_many_publications(): void
    {
        $publishing = Publishing::factory()->create();
        Publication::factory()->count(3)->create([
            'id_publishing' => $publishing->id_publishing,
        ]);

        $this->assertCount(3, $publishing->publications);
    }

    public function test_publication_belongs_to_magazine(): void
    {
        $magazine = Magazine::factory()->create();
        $publication = Publication::factory()->create([
            'id_magazine' => $magazine->id_magazine,
        ]);

        $this->assertEquals($magazine->id_magazine, $publication->magazine->id_magazine);
    }

    public function test_magazine_has_many_publications(): void
    {
        $magazine = Magazine::factory()->create();
        Publication::factory()->count(2)->create([
            'id_magazine' => $magazine->id_magazine,
        ]);

        $this->assertCount(2, $magazine->publications);
    }

    public function test_publication_belongs_to_part(): void
    {
        $part = Part::factory()->create();
        $publication = Publication::factory()->create([
            'id_part' => $part->id_part,
        ]);

        $this->assertEquals($part->id_part, $publication->part->id_part);
    }

    public function test_part_has_many_publications(): void
    {
        $part = Part::factory()->create();
        Publication::factory()->count(2)->create([
            'id_part' => $part->id_part,
        ]);

        $this->assertCount(2, $part->publications);
    }

    public function test_publication_belongs_to_issue_type(): void
    {
        $issueType = IssueType::factory()->create();
        $publication = Publication::factory()->create([
            'id_issue_type' => $issueType->id_issue_type,
        ]);

        $this->assertEquals($issueType->id_issue_type, $publication->issueType->id_issue_type);
    }

    public function test_issue_type_has_many_publications(): void
    {
        $issueType = IssueType::factory()->create();
        Publication::factory()->count(2)->create([
            'id_issue_type' => $issueType->id_issue_type,
        ]);

        $this->assertCount(2, $issueType->publications);
    }

    public function test_publication_belongs_to_author_group(): void
    {
        $authorGroup = AuthorGroup::factory()->create();
        $publication = Publication::factory()->create([
            'id_author_set' => $authorGroup->id_author_group,
        ]);

        $this->assertEquals($authorGroup->id_author_group, $publication->authorGroup->id_author_group);
    }

    public function test_author_group_has_many_publications(): void
    {
        $authorGroup = AuthorGroup::factory()->create();
        Publication::factory()->count(2)->create([
            'id_author_set' => $authorGroup->id_author_group,
        ]);

        $this->assertCount(2, $authorGroup->publications);
    }

    public function test_publication_belongs_to_theme_set(): void
    {
        $themeSet = ThemeSet::factory()->create();
        $publication = Publication::factory()->create([
            'id_theme_set' => $themeSet->id_theme_set,
        ]);

        $this->assertEquals($themeSet->id_theme_set, $publication->themeSet->id_theme_set);
    }

    public function test_theme_set_has_many_publications(): void
    {
        $themeSet = ThemeSet::factory()->create();
        Publication::factory()->count(2)->create([
            'id_theme_set' => $themeSet->id_theme_set,
        ]);

        $this->assertCount(2, $themeSet->publications);
    }

    public function test_publication_soft_deletes_correctly(): void
    {
        $publication = Publication::factory()->create();

        $publication->delete();

        $this->assertTrue($publication->trashed());
        $this->assertNotNull(Publication::withTrashed()->find($publication->id_publication));
        $this->assertNull(Publication::find($publication->id_publication));
    }

    public function test_author_soft_deletes_correctly(): void
    {
        $author = Author::factory()->create();

        $author->delete();

        $this->assertTrue($author->trashed());
        $this->assertNotNull(Author::withTrashed()->find($author->id_author));
    }

    public function test_file_soft_deletes_correctly(): void
    {
        $publication = Publication::factory()->create();
        $file = File::factory()->create([
            'id_publication' => $publication->id_publication,
        ]);

        $file->delete();

        $this->assertTrue($file->trashed());
    }

    public function test_publication_accessors_return_expected_values(): void
    {
        $publication = Publication::factory()->create([
            'upload_date' => '2023-05-15',
            'word_count' => 5000,
        ]);

        $this->assertIsString($publication->formatted_upload_date);
        $this->assertEquals(5000, $publication->word_count);
    }

    public function test_publication_total_file_size_accessor_calculates_correctly(): void
    {
        $publication = Publication::factory()->create();

        File::factory()->create([
            'id_publication' => $publication->id_publication,
            'file_size_bytes' => 1024,
        ]);

        File::factory()->create([
            'id_publication' => $publication->id_publication,
            'file_size_bytes' => 2048,
        ]);

        // Refresh to clear any relationship cache
        $publication = $publication->fresh();

        $this->assertEquals(3072, $publication->total_file_size);
    }
}
