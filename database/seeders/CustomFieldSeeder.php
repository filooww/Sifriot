<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ContentType;
use App\Models\CustomField;
use Illuminate\Database\Seeder;

class CustomFieldSeeder extends Seeder
{
    public function run(): void
    {
        // Get existing content types
        $books = ContentType::where('slug', 'books')->first();
        $magazines = ContentType::where('slug', 'magazines')->first();
        $articles = ContentType::where('slug', 'articles')->first();

        if ($books) {
            // Custom fields for Books
            CustomField::create([
                'content_type_id' => $books->id,
                'field_name' => 'isbn',
                'label_en' => 'ISBN',
                'label_ru' => 'ISBN',
                'label_he' => 'ISBN',
                'field_type' => 'text',
                'field_config' => ['max_length' => 20],
                'is_required' => false,
                'visibility' => 'public',
                'is_searchable' => true,
                'is_filterable' => false,
                'sort_order' => 1,
            ]);

            CustomField::create([
                'content_type_id' => $books->id,
                'field_name' => 'page_count',
                'label_en' => 'Page Count',
                'label_ru' => 'Количество страниц',
                'label_he' => 'מספר עמודים',
                'field_type' => 'number',
                'field_config' => ['min' => 1, 'max' => 10000, 'step' => 1],
                'is_required' => false,
                'visibility' => 'public',
                'is_searchable' => false,
                'is_filterable' => true,
                'sort_order' => 2,
            ]);

            CustomField::create([
                'content_type_id' => $books->id,
                'field_name' => 'series',
                'label_en' => 'Series',
                'label_ru' => 'Серия',
                'label_he' => 'סדרה',
                'field_type' => 'dropdown',
                'field_config' => [
                    'options' => [
                        ['value' => 'standalone', 'label_en' => 'Standalone', 'label_ru' => 'Отдельная книга', 'label_he' => 'עצמאי'],
                        ['value' => 'series', 'label_en' => 'Part of Series', 'label_ru' => 'Часть серии', 'label_he' => 'חלק מסדרה'],
                    ],
                ],
                'is_required' => false,
                'visibility' => 'public',
                'is_searchable' => false,
                'is_filterable' => true,
                'sort_order' => 3,
            ]);
        }

        if ($magazines) {
            // Custom fields for Magazines
            CustomField::create([
                'content_type_id' => $magazines->id,
                'field_name' => 'issue_number',
                'label_en' => 'Issue Number',
                'label_ru' => 'Номер выпуска',
                'label_he' => 'מספר גיליון',
                'field_type' => 'number',
                'field_config' => ['min' => 1, 'max' => 1000, 'step' => 1],
                'is_required' => false,
                'visibility' => 'public',
                'is_searchable' => true,
                'is_filterable' => true,
                'sort_order' => 1,
            ]);

            CustomField::create([
                'content_type_id' => $magazines->id,
                'field_name' => 'volume',
                'label_en' => 'Volume',
                'label_ru' => 'Том',
                'label_he' => 'כרך',
                'field_type' => 'number',
                'field_config' => ['min' => 1, 'max' => 100, 'step' => 1],
                'is_required' => false,
                'visibility' => 'public',
                'is_searchable' => false,
                'is_filterable' => true,
                'sort_order' => 2,
            ]);

            CustomField::create([
                'content_type_id' => $magazines->id,
                'field_name' => 'frequency',
                'label_en' => 'Publication Frequency',
                'label_ru' => 'Периодичность',
                'label_he' => 'תדירות פרסום',
                'field_type' => 'dropdown',
                'field_config' => [
                    'options' => [
                        ['value' => 'daily', 'label_en' => 'Daily', 'label_ru' => 'Ежедневно', 'label_he' => 'יומי'],
                        ['value' => 'weekly', 'label_en' => 'Weekly', 'label_ru' => 'Еженедельно', 'label_he' => 'שבועי'],
                        ['value' => 'monthly', 'label_en' => 'Monthly', 'label_ru' => 'Ежемесячно', 'label_he' => 'חודשי'],
                        ['value' => 'quarterly', 'label_en' => 'Quarterly', 'label_ru' => 'Ежеквартально', 'label_he' => 'רבעוני'],
                    ],
                ],
                'is_required' => false,
                'visibility' => 'public',
                'is_searchable' => false,
                'is_filterable' => true,
                'sort_order' => 3,
            ]);
        }

        if ($articles) {
            // Custom fields for Articles
            CustomField::create([
                'content_type_id' => $articles->id,
                'field_name' => 'doi',
                'label_en' => 'DOI',
                'label_ru' => 'DOI',
                'label_he' => 'DOI',
                'field_type' => 'text',
                'field_config' => ['max_length' => 100],
                'is_required' => false,
                'visibility' => 'public',
                'is_searchable' => true,
                'is_filterable' => false,
                'sort_order' => 1,
            ]);

            CustomField::create([
                'content_type_id' => $articles->id,
                'field_name' => 'journal_name',
                'label_en' => 'Journal Name',
                'label_ru' => 'Название журнала',
                'label_he' => 'שם כתב העת',
                'field_type' => 'text',
                'field_config' => ['max_length' => 255],
                'is_required' => false,
                'visibility' => 'public',
                'is_searchable' => true,
                'is_filterable' => true,
                'sort_order' => 2,
            ]);

            CustomField::create([
                'content_type_id' => $articles->id,
                'field_name' => 'peer_reviewed',
                'label_en' => 'Peer Reviewed',
                'label_ru' => 'Рецензировано',
                'label_he' => 'נבדק עמיתים',
                'field_type' => 'boolean',
                'field_config' => [],
                'is_required' => false,
                'visibility' => 'public',
                'is_searchable' => false,
                'is_filterable' => true,
                'sort_order' => 3,
            ]);
        }
    }
}
