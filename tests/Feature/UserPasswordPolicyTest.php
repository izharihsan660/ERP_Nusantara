<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserPasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_user_with_simple_eight_character_password(): void
    {
        $permission = Permission::create(['name' => 'tambah_user']);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->givePermissionTo($permission);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Weak Password User',
            'email' => 'weak-password@example.test',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_active' => true,
            'roles' => [],
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertDatabaseMissing('users', ['email' => 'weak-password@example.test']);
    }
}
