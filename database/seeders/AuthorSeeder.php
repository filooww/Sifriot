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

        $this->command->info("Created " . count($authors) . " authors");
    }

    /**
     * Get list of common authors for seeding.
     *
     * @return array<int, string>
     */
    private function getAuthorsData(): array
    {
        return [
            'Isaac Asimov',
            'Arthur C. Clarke',
            'Philip K. Dick',
            'Ursula K. Le Guin',
            'Ray Bradbury',
            'Carl Sagan',
            'Stephen Hawking',
            'Richard Feynman',
            'Neil deGrasse Tyson',
            'Brian Greene',
            'Douglas Hofstadter',
            'Michio Kaku',
            'Daniel Dennett',
            'David Deutsch',
            'Roger Penrose',
            'Max Tegmark',
            'Sean Carroll',
            'Alan Turing',
            'John von Neumann',
            'Marvin Minsky',
        ];
    }
}
