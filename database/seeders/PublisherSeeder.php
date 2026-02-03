<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Publisher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PublisherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publishers = $this->getPublishers();

        foreach ($publishers as $data) {
            Publisher::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );
        }

        $this->command->info('Created '.count($publishers).' publishers');
    }

    private function getPublishers(): array
    {
        return [
            [
                'name_en' => 'Eksmo',
                'name_ru' => 'Эксмо',
                'name_he' => 'אקסמו',
                'slug' => 'eksmo',
                'website' => 'https://eksmo.ru',
            ],
            [
                'name_en' => 'AST',
                'name_ru' => 'АСТ',
                'name_he' => 'אסט',
                'slug' => 'ast',
                'website' => 'https://ast.ru',
            ],
            [
                'name_en' => 'Azbuka-Atticus',
                'name_ru' => 'Азбука-Аттикус',
                'name_he' => 'אזבוקה-אטיקוס',
                'slug' => 'azbuka-atticus',
                'website' => 'https://azbooka.ru',
            ],
            [
                'name_en' => 'Prosveshcheniye',
                'name_ru' => 'Просвещение',
                'name_he' => 'פרוסוושצ׳נייה',
                'slug' => 'prosveshcheniye',
                'website' => 'https://prosv.ru',
            ],
            [
                'name_en' => 'Alpina Publisher',
                'name_ru' => 'Альпина Паблишер',
                'name_he' => 'אלפינה פבלישר',
                'slug' => 'alpina',
                'website' => 'https://alpina.ru',
            ],
            [
                'name_en' => 'Mann, Ivanov and Ferber',
                'name_ru' => 'Манн, Иванов и Фербер',
                'name_he' => 'מאן, איבנוב ופרבר',
                'slug' => 'mif',
                'website' => 'https://mann-ivanov-ferber.ru',
            ],
            [
                'name_en' => 'Corpus',
                'name_ru' => 'Corpus',
                'name_he' => 'קורפוס',
                'slug' => 'corpus',
                'website' => 'https://corp.ru',
            ],
            [
                'name_en' => 'Ripol Classic',
                'name_ru' => 'Рипол Классик',
                'name_he' => 'ריפול קלאסיק',
                'slug' => 'ripol',
                'website' => 'https://ripol.ru',
            ],
            [
                'name_en' => 'Rosman',
                'name_ru' => 'Росмэн',
                'name_he' => 'רוסמן',
                'slug' => 'rosman',
                'website' => 'https://rosman.ru',
            ],
            [
                'name_en' => 'Molodaya Gvardiya',
                'name_ru' => 'Молодая гвардия',
                'name_he' => 'מולודאיה גווארדיה',
                'slug' => 'molodaya-gvardiya',
                'website' => 'https://gvardiya.ru',
            ],
        ];
    }
}
