<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Publication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_has_parent_relationship(): void
    {
        $parent = Category::factory()->create();
        $child = Category::factory()->create(['parent_id' => $parent->id]);

        $this->assertInstanceOf(Category::class, $child->parent);
        $this->assertEquals($parent->id, $child->parent->id);
    }

    public function test_category_has_children_relationship(): void
    {
        $parent = Category::factory()->create();
        $child1 = Category::factory()->create(['parent_id' => $parent->id, 'sort_order' => 2]);
        $child2 = Category::factory()->create(['parent_id' => $parent->id, 'sort_order' => 1]);

        $children = $parent->children;

        $this->assertCount(2, $children);
        $this->assertEquals($child2->id, $children->first()->id); // Ordered by sort_order
    }

    public function test_category_has_publications_relationship(): void
    {
        $category = Category::factory()->create();
        $publication = Publication::factory()->create();

        $category->publications()->attach($publication->id_publication);

        $this->assertCount(1, $category->publications);
        $this->assertEquals($publication->id_publication, $category->publications->first()->id_publication);
    }

    public function test_localized_name_returns_correct_locale(): void
    {
        $category = Category::factory()->create([
            'name_en' => 'Science Fiction',
            'name_ru' => 'Научная фантастика',
            'name_he' => 'מדע בדיוני',
        ]);

        app()->setLocale('en');
        $this->assertEquals('Science Fiction', $category->localized_name);

        app()->setLocale('ru');
        $this->assertEquals('Научная фантастика', $category->localized_name);

        app()->setLocale('he');
        $this->assertEquals('מדע בדיוני', $category->localized_name);
    }

    public function test_localized_name_falls_back_to_english(): void
    {
        $category = Category::factory()->create([
            'name_en' => 'Science Fiction',
        ]);

        app()->setLocale('invalid');
        $this->assertEquals('Science Fiction', $category->localized_name);
    }
}
