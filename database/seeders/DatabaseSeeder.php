<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('Starting database seeding...');

        try {
            // Seed core system data
            $this->call(ContentTypeSeeder::class);
            $this->command->info('✓ Content types seeded');

            $this->call(SectionSeeder::class);
            $this->command->info('✓ Sections seeded');

            // Seed reference data
            $this->call(AuthorSeeder::class);
            $this->call(ThemeSeeder::class);

            // Seed test users
            $this->seedDefaultUsers();
            $this->command->info('✓ Default users created');

            $this->command->info('✓ Database seeding completed successfully!');
        } catch (\Exception $e) {
            $this->command->error('Error during seeding: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Create default users for testing and administration.
     */
    private function seedDefaultUsers(): void
    {
        // Create admin user
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]
        );

        // Create regular test user
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]
        );
    }
}
