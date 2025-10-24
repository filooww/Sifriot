<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = $this->getCategoryStructure();

        foreach ($categories as $categoryData) {
            $this->createCategoryWithChildren($categoryData);
        }
    }

    /**
     * Create a parent category and its children recursively.
     *
     * @param  array<string, mixed>  $categoryData
     */
    private function createCategoryWithChildren(array $categoryData): void
    {
        $children = $categoryData['children'] ?? [];
        unset($categoryData['children']);

        $category = Category::firstOrCreate(
            ['slug' => $categoryData['slug']],
            $categoryData
        );

        foreach ($children as $childData) {
            $childData['parent_id'] = $category->id;
            Category::firstOrCreate(
                ['slug' => $childData['slug']],
                $childData
            );
        }
    }

    /**
     * Get the category hierarchy structure with multilingual names.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getCategoryStructure(): array
    {
        return [
            [
                'name_en' => 'Books',
                'name_ru' => 'Книги',
                'name_he' => 'ספרים',
                'slug' => 'books',
                'sort_order' => 1,
                'children' => [
                    [
                        'name_en' => 'Fiction',
                        'name_ru' => 'Художественная литература',
                        'name_he' => 'סיפורת',
                        'slug' => 'fiction',
                        'sort_order' => 1,
                    ],
                    [
                        'name_en' => 'Science Fiction',
                        'name_ru' => 'Научная фантастика',
                        'name_he' => 'מדע בדיוני',
                        'slug' => 'sci-fi',
                        'sort_order' => 2,
                    ],
                    [
                        'name_en' => 'Non-Fiction',
                        'name_ru' => 'Научно-популярная литература',
                        'name_he' => 'עיון',
                        'slug' => 'non-fiction',
                        'sort_order' => 3,
                    ],
                ],
            ],
            [
                'name_en' => 'Magazines',
                'name_ru' => 'Журналы',
                'name_he' => 'כתבי עת',
                'slug' => 'magazines',
                'sort_order' => 2,
                'children' => [
                    [
                        'name_en' => 'Technology',
                        'name_ru' => 'Технологии',
                        'name_he' => 'טכנולוגיה',
                        'slug' => 'technology',
                        'sort_order' => 1,
                    ],
                    [
                        'name_en' => 'Science',
                        'name_ru' => 'Наука',
                        'name_he' => 'מדע',
                        'slug' => 'science',
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'name_en' => 'Articles',
                'name_ru' => 'Статьи',
                'name_he' => 'מאמרים',
                'slug' => 'articles',
                'sort_order' => 3,
                'children' => [
                    [
                        'name_en' => 'Research Papers',
                        'name_ru' => 'Научные статьи',
                        'name_he' => 'מאמרי מחקר',
                        'slug' => 'research-papers',
                        'sort_order' => 1,
                    ],
                    [
                        'name_en' => 'Opinion',
                        'name_ru' => 'Мнения',
                        'name_he' => 'דעות',
                        'slug' => 'opinion',
                        'sort_order' => 2,
                    ],
                ],
            ],
        ];
    }
}
