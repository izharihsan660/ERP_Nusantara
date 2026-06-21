<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\NotificationHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NotificationHelperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_get_users_by_role_includes_active_superadmin_without_duplicates(): void
    {
        $managerRole = Role::findOrCreate('Manager', 'web');
        $superadminRole = Role::findOrCreate('Superadmin', 'web');

        $manager = User::factory()->create(['is_active' => true]);
        $superadmin = User::factory()->create(['is_active' => true]);
        $inactiveSuperadmin = User::factory()->create(['is_active' => false]);
        $superadminManager = User::factory()->create(['is_active' => true]);

        $manager->assignRole($managerRole);
        $superadmin->assignRole($superadminRole);
        $inactiveSuperadmin->assignRole($superadminRole);
        $superadminManager->assignRole($managerRole, $superadminRole);

        $users = app(NotificationHelper::class)->getUsersByRole('Manager');

        $this->assertEqualsCanonicalizing(
            [$manager->id, $superadmin->id, $superadminManager->id],
            $users->pluck('id')->all(),
        );
        $this->assertSame($users->pluck('id')->unique()->count(), $users->count());
        $this->assertFalse($users->contains('id', $inactiveSuperadmin->id));
    }
}
