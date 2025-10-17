<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_column_exists_and_has_default_value(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'user',
        ]);

        $this->assertEquals('user', $user->role);
    }

    public function test_role_column_accepts_valid_enum_values(): void
    {
        $userRole = User::factory()->create(['role' => 'user']);
        $adminRole = User::factory()->create(['role' => 'admin']);
        $guestRole = User::factory()->create(['role' => 'guest']);

        $this->assertEquals('user', $userRole->role);
        $this->assertEquals('admin', $adminRole->role);
        $this->assertEquals('guest', $guestRole->role);
    }

    public function test_is_admin_helper_method(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    public function test_is_user_helper_method(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->isUser());
        $this->assertFalse($admin->isUser());
    }

    public function test_is_guest_helper_method(): void
    {
        $guest = User::factory()->create(['role' => 'guest']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($guest->isGuest());
        $this->assertFalse($user->isGuest());
    }
}
