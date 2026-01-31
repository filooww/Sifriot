<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Section;
use Illuminate\Database\Seeder;

class SectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sections = $this->getSectionStructure();

        foreach ($sections as $sectionData) {
            $this->createSectionWithChildren($sectionData);
        }
    }

    /**
     * Create a parent section and its children recursively.
     *
     * @param  array<string, mixed>  $sectionData
     */
    private function createSectionWithChildren(array $sectionData): void
    {
        $children = $sectionData['children'] ?? [];
        unset($sectionData['children']);

        $section = Section::firstOrCreate(
            ['slug' => $sectionData['slug']],
            $sectionData
        );

        foreach ($children as $childData) {
            $childData['parent_id'] = $section->id;
            Section::firstOrCreate(
                ['slug' => $childData['slug']],
                $childData
            );
        }
    }

    /**
     * Get the section hierarchy structure with multilingual names.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getSectionStructure(): array
    {
        return [
            [
                'name_en' => 'Featured',
                'name_ru' => 'Избранное',
                'name_he' => 'מומלצים',
                'slug' => 'featured',
                'sort_order' => 1,
                'children' => [
                    [
                        'name_en' => 'Staff Picks',
                        'name_ru' => 'Выбор редакции',
                        'name_he' => 'בחירת הצוות',
                        'slug' => 'staff-picks',
                        'sort_order' => 1,
                    ],
                    [
                        'name_en' => 'New Arrivals',
                        'name_ru' => 'Новые поступления',
                        'name_he' => 'חדשים',
                        'slug' => 'new-arrivals',
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'name_en' => 'Collections',
                'name_ru' => 'Коллекции',
                'name_he' => 'אוספים',
                'slug' => 'collections',
                'sort_order' => 2,
                'children' => [
                    [
                        'name_en' => 'By Topic',
                        'name_ru' => 'По теме',
                        'name_he' => 'לפי נושא',
                        'slug' => 'by-topic',
                        'sort_order' => 1,
                    ],
                    [
                        'name_en' => 'By Author',
                        'name_ru' => 'По автору',
                        'name_he' => 'לפי מחבר',
                        'slug' => 'by-author',
                        'sort_order' => 2,
                    ],
                ],
            ],
            [
                'name_en' => 'Browse',
                'name_ru' => 'Обзор',
                'name_he' => 'עיון',
                'slug' => 'browse',
                'sort_order' => 3,
                'children' => [
                    [
                        'name_en' => 'Recent',
                        'name_ru' => 'Недавние',
                        'name_he' => 'אחרונים',
                        'slug' => 'recent',
                        'sort_order' => 1,
                    ],
                    [
                        'name_en' => 'Popular',
                        'name_ru' => 'Популярные',
                        'name_he' => 'פופולריים',
                        'slug' => 'popular',
                        'sort_order' => 2,
                    ],
                ],
            ],
        ];
    }
}
