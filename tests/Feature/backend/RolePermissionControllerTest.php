<?php

namespace Tests\Feature\backend;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $adminUser;
    protected $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаем тестовые роли и разрешения
        Permission::create(['name' => 'access-admin-panel']);
        Permission::create(['name' => 'manage-roles']);
        Permission::create(['name' => 'manage-permissions']);
        Permission::create(['name' => 'read-posts']);

        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(['access-admin-panel', 'manage-roles', 'manage-permissions']);

        $editorRole = Role::create(['name' => 'editor']);
        $editorRole->givePermissionTo('read-posts');

        // Создаем тестовых пользователей
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('admin');

        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole('editor');
    }

    /** @test */
    public function admin_can_view_roles_index()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('backend.roles.index'));

        $response->assertOk();
        $response->assertViewIs('backend.roles.index');
    }

    /** @test */
    public function non_admin_cannot_view_roles_index()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('backend.roles.index'));

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_view_create_role_form()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('backend.roles.create'));

        $response->assertOk();
        $response->assertViewIs('backend.roles.create');
        $response->assertViewHas('permissions');
    }

    /** @test */
    public function admin_can_store_new_role()
    {
        $data = [
            'name' => 'new_role',
            'permissions' => ['read-posts']
        ];

        $response = $this->actingAs($this->adminUser)
            ->post(route('backend.roles.store'), $data);

        $response->assertRedirect(route('backend.roles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['name' => 'new_role']);
        $this->assertTrue(Role::findByName('new_role')->hasPermissionTo('read-posts'));
    }

    /** @test */
    public function store_role_validation_works()
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('backend.roles.store'), ['name' => '']);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function admin_can_view_edit_role_form()
    {
        $role = Role::create(['name' => 'test_role']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('backend.roles.edit', $role));

        $response->assertOk();
        $response->assertViewIs('backend.roles.edit');
        $response->assertViewHas(['role', 'permissions', 'rolePermissions']);
    }

    /** @test */
    public function admin_can_update_role()
    {
        $role = Role::create(['name' => 'old_name']);

        $data = [
            'name' => 'new_name',
            'permissions' => ['read-posts']
        ];

        $response = $this->actingAs($this->adminUser)
            ->put(route('backend.roles.update', $role), $data);

        $response->assertRedirect(route('backend.roles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('roles', ['name' => 'new_name']);
        $this->assertTrue($role->fresh()->hasPermissionTo('read-posts'));
    }

    /** @test */
    public function update_role_validation_works()
    {
        $role = Role::create(['name' => 'test_role']);

        $response = $this->actingAs($this->adminUser)
            ->put(route('backend.roles.update', $role), ['name' => '']);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function admin_can_delete_unused_role()
    {
        $role = Role::create(['name' => 'to_delete']);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('backend.roles.destroy', $role));

        $response->assertRedirect(route('backend.roles.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('roles', ['name' => 'to_delete']);
    }

    /** @test */
    public function cannot_delete_role_in_use()
    {
        $role = Role::create(['name' => 'in_use']);
        $this->adminUser->assignRole('in_use');

        $response = $this->actingAs($this->adminUser)
            ->delete(route('backend.roles.destroy', $role));

        $response->assertRedirect(route('backend.roles.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('roles', ['name' => 'in_use']);
    }

    /** @test */
    public function admin_can_view_permissions_index()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('backend.permissions.index'));

        $response->assertOk();
        $response->assertViewIs('backend.permissions.index');
    }

    /** @test */
    public function admin_can_create_permission()
    {
        $data = ['name' => 'new_permission'];

        $response = $this->actingAs($this->adminUser)
            ->post(route('backend.permissions.store'), $data);

        $response->assertRedirect(route('backend.permissions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('permissions', ['name' => 'new_permission']);
    }

    /** @test */
    public function cannot_create_duplicate_permission()
    {
        Permission::create(['name' => 'existing_permission']);

        $response = $this->actingAs($this->adminUser)
            ->post(route('backend.permissions.store'), ['name' => 'existing_permission']);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function admin_can_update_permission()
    {
        $permission = Permission::create(['name' => 'old_name']);

        $response = $this->actingAs($this->adminUser)
            ->put(route('backend.permissions.update', $permission), ['name' => 'new_name']);

        $response->assertRedirect(route('backend.permissions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('permissions', ['name' => 'new_name']);
    }

    /** @test */
    public function cannot_update_permission_to_duplicate_name()
    {
        Permission::create(['name' => 'permission1']);
        $permission2 = Permission::create(['name' => 'permission2']);

        $response = $this->actingAs($this->adminUser)
            ->put(route('backend.permissions.update', $permission2), ['name' => 'permission1']);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function admin_can_delete_unused_permission()
    {
        $permission = Permission::create(['name' => 'to_delete']);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('backend.permissions.destroy', $permission));

        $response->assertRedirect(route('backend.permissions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('permissions', ['name' => 'to_delete']);
    }

    /** @test */
    public function cannot_delete_permission_in_use()
    {
        $permission = Permission::create(['name' => 'in_use']);
        $this->adminUser->givePermissionTo('in_use');

        $response = $this->actingAs($this->adminUser)
            ->delete(route('backend.permissions.destroy', $permission));

        $response->assertRedirect(route('backend.permissions.index'));
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('permissions', ['name' => 'in_use']);
    }

    /** @test */
    public function admin_can_manage_user_roles_and_permissions()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('backend.roles.manage'));

        $response->assertOk();
        $response->assertViewIs('backend.roles.manage');
        $response->assertViewHas(['users', 'roles', 'permissions']);
    }

    /** @test */
    public function admin_can_update_user_roles_and_permissions()
    {
        $data = [
            'user_id' => $this->regularUser->id,
            'roles' => ['admin'],
            'permissions' => ['manage-roles']
        ];

        $response = $this->actingAs($this->adminUser)
            ->put(route('backend.roles.update-user'), $data);

        $response->assertRedirect(route('backend.roles.manage'));
        $response->assertSessionHas('success');

        $this->assertTrue($this->regularUser->fresh()->hasRole('admin'));
        $this->assertTrue($this->regularUser->fresh()->hasPermissionTo('manage-roles'));
    }

    /** @test */
    public function can_get_user_roles_and_permissions_via_ajax()
    {
        $this->regularUser->givePermissionTo('read-posts');

        $response = $this->actingAs($this->adminUser)
            ->get(route('backend.roles.get-user', $this->regularUser));

        $response->assertOk();
        $response->assertJson([
            'roles' => ['editor'],
            'permissions' => ['read-posts']
        ]);
    }
}
