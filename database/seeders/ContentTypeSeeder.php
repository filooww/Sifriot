<?php

namespace Database\Seeders;

use App\Models\ContentType;
use Illuminate\Database\Seeder;

class ContentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contentTypes = [
            [
                'name_en' => 'Books',
                'name_ru' => 'Книги',
                'name_he' => 'ספרים',
                'slug' => 'books',
                'icon' => 'book-open',
                'folder_name' => 'books',
                'is_system' => true,
            ],
            [
                'name_en' => 'Magazines',
                'name_ru' => 'Журналы',
                'name_he' => 'כתבי עת',
                'slug' => 'magazines',
                'icon' => 'newspaper',
                'folder_name' => 'magazines',
                'is_system' => true,
            ],
            [
                'name_en' => 'Articles',
                'name_ru' => 'Статьи',
                'name_he' => 'מאמרים',
                'slug' => 'articles',
                'icon' => 'document-text',
                'folder_name' => 'articles',
                'is_system' => true,
            ],
            [
                'name_en' => 'Other',
                'name_ru' => 'Другое',
                'name_he' => 'אחר',
                'slug' => 'other',
                'icon' => 'folder',
                'folder_name' => 'other',
                'is_system' => true,
            ],
        ];

        foreach ($contentTypes as $type) {
            ContentType::updateOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }
    }
}
