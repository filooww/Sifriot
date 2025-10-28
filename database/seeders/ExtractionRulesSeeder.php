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
            // Books (PDF/EPUB/DOCX) - ISBN patterns
            [
                'content_type_id' => $contentTypes['books'],
                'format' => 'pdf',
                'priority' => 1,
                'pattern_type' => 'regex',
                'pattern' => '/\b(?:ISBN(?:-1[03])?:?\s?)?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[X0-9]\b/i',
                'target_field' => 'isbn',
                'enabled' => true,
            ],
            [
                'content_type_id' => $contentTypes['books'],
                'format' => 'pdf',
                'priority' => 2,
                'pattern_type' => 'regex',
                'pattern' => '/10\.\d{4,}\/\S+/i',
                'target_field' => 'doi',
                'enabled' => true,
            ],
            [
                'content_type_id' => $contentTypes['books'],
                'format' => 'epub',
                'priority' => 1,
                'pattern_type' => 'regex',
                'pattern' => '/\b(?:ISBN(?:-1[03])?:?\s?)?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[X0-9]\b/i',
                'target_field' => 'isbn',
                'enabled' => true,
            ],
            [
                'content_type_id' => $contentTypes['books'],
                'format' => 'docx',
                'priority' => 1,
                'pattern_type' => 'regex',
                'pattern' => '/\b(?:ISBN(?:-1[03])?:?\s?)?(?=[0-9X]{10}$|(?=(?:[0-9]+[- ]){3})[- 0-9X]{13}$|97[89][0-9]{10}$|(?=(?:[0-9]+[- ]){4})[- 0-9]{17}$)(?:97[89][- ]?)?[0-9]{1,5}[- ]?[0-9]+[- ]?[0-9]+[- ]?[X0-9]\b/i',
                'target_field' => 'isbn',
                'enabled' => true,
            ],

            // Articles (PDF/TXT) - DOI patterns
            [
                'content_type_id' => $contentTypes['articles'],
                'format' => 'pdf',
                'priority' => 1,
                'pattern_type' => 'regex',
                'pattern' => '/10\.\d{4,}\/\S+/i',
                'target_field' => 'doi',
                'enabled' => true,
            ],
            [
                'content_type_id' => $contentTypes['articles'],
                'format' => 'txt',
                'priority' => 1,
                'pattern_type' => 'regex',
                'pattern' => '/10\.\d{4,}\/\S+/i',
                'target_field' => 'doi',
                'enabled' => true,
            ],

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
