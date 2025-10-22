<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Seeder;

class Story13TestDataSeeder extends Seeder
{
    private const ACTIVE_PUBLICATIONS_COUNT = 20;
    private const SOFT_DELETED_PUBLICATIONS_COUNT = 3;

    /**
     * Seed test data specifically for Story 1.3 testing
     * (Guest vs Authenticated Access Control)
     */
    public function run(): void
    {
        $this->command->info('Seeding Story 1.3 test data...');

        try {
            $this->seedTestUsers();
            $this->seedTestPublications();

            $this->command->info('✓ Story 1.3 test data seeded successfully!');
        } catch (\Exception $e) {
            $this->command->error('Error seeding Story 1.3 test data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create test users with different roles for access control testing.
     */
    private function seedTestUsers(): void
    {
        $users = [
            [
                'name' => 'Test Admin',
                'email' => 'admin@test.com',
                'password' => bcrypt('password'),
                'role' => 'admin',
            ],
            [
                'name' => 'Test User',
                'email' => 'user@test.com',
                'password' => bcrypt('password'),
                'role' => 'user',
            ],
            [
                'name' => 'Test Guest',
                'email' => 'guest@test.com',
                'password' => bcrypt('password'),
                'role' => 'guest',
            ],
        ];

        foreach ($users as $userData) {
            User::firstOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['email_verified_at' => now()])
            );
        }

        $this->command->info('Created 3 test users:');
        $this->command->info('  ✓ admin@test.com (password: password) - Admin');
        $this->command->info('  ✓ user@test.com (password: password) - User');
        $this->command->info('  ✓ guest@test.com (password: password) - Guest');
    }

    /**
     * Create test publications for access control testing.
     */
    private function seedTestPublications(): void
    {
        // Create active publications
        Publication::factory(self::ACTIVE_PUBLICATIONS_COUNT)->create();

        // Create soft-deleted publications (only visible to authenticated users)
        Publication::factory(self::SOFT_DELETED_PUBLICATIONS_COUNT)->create()->each(function ($publication) {
            $publication->delete(); // Soft delete
        });

        $totalPublications = self::ACTIVE_PUBLICATIONS_COUNT + self::SOFT_DELETED_PUBLICATIONS_COUNT;
        $this->command->info("Created {$totalPublications} publications:");
        $this->command->info('  ✓ ' . self::ACTIVE_PUBLICATIONS_COUNT . ' active publications');
        $this->command->info('  ✓ ' . self::SOFT_DELETED_PUBLICATIONS_COUNT . ' soft-deleted publications');
    }
}
