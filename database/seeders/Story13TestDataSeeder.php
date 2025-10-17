<?php

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\User;
use Illuminate\Database\Seeder;

class Story13TestDataSeeder extends Seeder
{
    /**
     * Seed test data specifically for Story 1.3 testing
     * (Guest vs Authenticated Access Control)
     */
    public function run(): void
    {
        $this->command->info('Seeding Story 1.3 test data...');

        // Create test users with different roles
        $admin = User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
        ]);

        $guest = User::factory()->create([
            'name' => 'Test Guest',
            'email' => 'guest@test.com',
            'password' => bcrypt('password'),
            'role' => 'guest',
        ]);

        $this->command->info('Created 3 test users:');
        $this->command->info('  - admin@test.com (password: password) - Admin');
        $this->command->info('  - user@test.com (password: password) - User');
        $this->command->info('  - guest@test.com (password: password) - Guest');

        // Create publications for testing
        $publications = Publication::factory(20)->create();

        // Create some soft-deleted publications (only visible to authenticated users)
        Publication::factory(3)->create()->each(function ($publication) {
            $publication->delete(); // Soft delete
        });

        $this->command->info('Created 23 publications (20 active, 3 soft-deleted)');
        $this->command->info('Story 1.3 test data seeded successfully!');
    }
}
