<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Author;
use Illuminate\Database\Seeder;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $authors = $this->getAuthorsData();

        foreach ($authors as $authorData) {
            Author::firstOrCreate(
                ['author' => $authorData],
                ['author' => $authorData]
            );
        }

        $this->command->info('Created '.count($authors).' authors');
    }

    /**
     * Get list of common authors for seeding.
     *
     * @return array<int, string>
     */
    private function getAuthorsData(): array
    {
        return [
            'Александр Пушкин',
            'Лев Толстой',
            'Фёдор Достоевский',
            'Антон Чехов',
            'Николай Гоголь',
            'Михаил Булгаков',
            'Иван Тургенев',
            'Владимир Набоков',
            'Борис Пастернак',
            'Александр Солженицын',
            'Исаак Азимов',
            'Артур Кларк',
            'Рэй Брэдбери',
            'Стивен Кинг',
            'Джордж Оруэлл',
            'Дж. Р. Р. Толкин',
            'Джоан Роулинг',
            'Агата Кристи',
            'Эрих Мария Ремарк',
            'Харуки Мураками',
        ];
    }
}
