<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Theme;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themes = $this->getThemesData();

        foreach ($themes as $themeName) {
            Theme::firstOrCreate(
                ['theme' => $themeName],
                [
                    'theme' => $themeName,
                    'theme_low' => mb_strtolower($themeName),
                ]
            );
        }

        $this->command->info("Created " . count($themes) . " themes");
    }

    /**
     * Get list of classic literature themes/topics.
     *
     * @return array<int, string>
     */
    private function getThemesData(): array
    {
        return [
            'Romance',
            'Adventure',
            'Mystery',
            'Crime',
            'Historical Fiction',
            'Science Fiction',
            'Fantasy',
            'Horror',
            'Drama',
            'Comedy',
            'Tragedy',
            'Philosophy',
            'Poetry',
            'Essays',
            'Biography',
            'Memoir',
            'Travel',
            'Nature',
            'Social Commentary',
            'Politics',
        ];
    }
}
