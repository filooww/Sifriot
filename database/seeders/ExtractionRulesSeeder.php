<?php

namespace Database\Seeders;

use App\Models\ExtractionRule;
use Illuminate\Database\Seeder;

class ExtractionRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get content type IDs (assuming Books=1, Articles=2, Magazines=3, Fiction=4)
        // Adjust these IDs based on your actual content types
        $contentTypes = [
            'books' => 1,
            'articles' => 2,
            'magazines' => 3,
            'fiction' => 4,
        ];

        $rules = [
            // Magazines (PDF/EPUB) - ISSN patterns
            [
                'content_type_id' => $contentTypes['magazines'],
                'format' => 'pdf',
                'priority' => 1,
                'pattern_type' => 'regex',
                'pattern' => '/\b(?:ISSN|eISSN)\s?:?\s?(?P<issn>\d{4}[- ]?\d{4})\b/i',
                'target_field' => 'issn',
                'enabled' => true,
            ],
            [
                'content_type_id' => $contentTypes['magazines'],
                'format' => 'epub',
                'priority' => 1,
                'pattern_type' => 'regex',
                'pattern' => '/\b(?:ISSN|eISSN)\s?:?\s?(?P<issn>\d{4}[- ]?\d{4})\b/i',
                'target_field' => 'issn',
                'enabled' => true,
            ],

            // Fiction (EPUB/FB2) - Standard FB2 field mapping
            [
                'content_type_id' => $contentTypes['fiction'],
                'format' => 'fb2',
                'priority' => 1,
                'pattern_type' => 'field_mapping',
                'pattern' => 'book-title',
                'target_field' => 'title',
                'enabled' => true,
            ],
            [
                'content_type_id' => $contentTypes['fiction'],
                'format' => 'fb2',
                'priority' => 2,
                'pattern_type' => 'field_mapping',
                'pattern' => 'author',
                'target_field' => 'author',
                'enabled' => true,
            ],
        ];

        foreach ($rules as $rule) {
            ExtractionRule::updateOrCreate(
                [
                    'content_type_id' => $rule['content_type_id'],
                    'format' => $rule['format'],
                    'target_field' => $rule['target_field'],
                    'priority' => $rule['priority'],
                ],
                $rule
            );
        }
    }
}
