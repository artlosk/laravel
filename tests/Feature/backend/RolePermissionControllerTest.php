<?php

namespace Tests\Feature\backend;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Tests\TestCase;

class RolePermissionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permissions
        Permission::create(['name' => 'manage-roles']);
        Permission::create(['name' => 'manage-permissions']);
        Permission::create(['name' => 'access-admin-panel']);
        Permission::create(['name' => 'read-posts']);
        Permission::create(['name' => 'create-posts']);
        Permission::create(['name' => 'edit-posts']);
        Permission::create(['name' => 'delete-posts']);

        // Create admin role
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo([
            'manage-roles',
            'manage-permissions',
            'access-admin-panel',
            'read-posts',
            'create-posts',
            'edit-posts',
            'delete-posts',
        ]);

        // Create user role
        Role::create(['name' => 'user']);

        // Create admin user
        $this->admin = User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->admin->assignRole('admin');

        // Create regular user
        $this->user = User::factory()->create([
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
        ]);
        $this->user->assignRole('user');
        $this->user->givePermissionTo('read-posts');
    }

    public function test_index_roles_authorized()
    {
        $response = $this->actingAs($this->admin)->get(route('backend.roles.index'));
        $response->assertStatus(200);
        $response->assertViewIs('backend.roles.index');
        $response->assertViewHas('roles');
    }

    public function test_index_roles_unauthorized()
    {
        $response = $this->actingAs($this->user)->get(route('backend.roles.index'));
        $response->assertStatus(403);
    }

    public function test_store_role()
    {
        $data = [
            'name' => 'editor',
            'permissions' => ['access-admin-panel'],
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.roles.store'), $data);
        $response->assertRedirect(route('backend.roles.index'));
        $response->assertSessionHas('success', __('messages.role_created'));

        $this->assertDatabaseHas('roles', ['name' => 'editor']);
        $this->assertDatabaseHas('role_has_permissions', [
            'permission_id' => Permission::where('name', 'access-admin-panel')->first()->id,
            'role_id' => Role::where('name', 'editor')->first()->id,
        ]);
    }

    public function test_update_role()
    {
        $role = Role::create(['name' => 'editor']);
        $data = [
            'name' => 'senior_editor',
            'permissions' => ['access-admin-panel'],
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.roles.update', $role), $data);
        $response->assertRedirect(route('backend.roles.index'));
        $response->assertSessionHas('success', __('messages.role_updated'));

        $this->assertDatabaseHas('roles', ['name' => 'senior_editor']);
    }

    public function test_destroy_role()
    {
        $role = Role::create(['name' => 'editor']);

        $response = $this->actingAs($this->admin)->delete(route('backend.roles.destroy', $role));
        $response->assertRedirect(route('backend.roles.index'));
        $response->assertSessionHas('success', __('messages.role_deleted'));

        $this->assertDatabaseMissing('roles', ['name' => 'editor']);
    }

    public function test_index_permissions_authorized()
    {
        $response = $this->actingAs($this->admin)->get(route('backend.permissions.index'));
        $response->assertStatus(200);
        $response->assertViewIs('backend.permissions.index');
        $response->assertViewHas('permissions');
    }

    public function test_store_permission()
    {
        $data = ['name' => 'new-permission'];

        $response = $this->actingAs($this->admin)->post(route('backend.permissions.store'), $data);
        $response->assertRedirect(route('backend.permissions.index'));
        $response->assertSessionHas('success', __('messages.permission_created'));

        $this->assertDatabaseHas('permissions', ['name' => 'new-permission']);
    }

    public function test_store_permission_fails_with_duplicate_name()
    {
        $data = ['name' => 'edit-posts'];

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, EnsureFrontendRequestsAreStateful::class])
            ->from(route('backend.permissions.create'))
            ->post(route('backend.permissions.store'), $data);

        $response->assertStatus(302);
        $response->assertRedirect(route('backend.permissions.create'));

        // Check errors after redirect
        $redirectResponse = $this->followRedirects($response);
        $redirectResponse->assertViewHas('errors');
        $redirectResponse->assertSessionHasErrorsIn('default', ['name']);

        // Debug: Log session contents
        Log::debug('Session contents in test_store_permission_fails_with_duplicate_name', [
            'session' => $response->hasSession() ? $response->session()->all() : 'No session',
            'status' => $response->getStatusCode(),
            'location' => $response->headers->get('Location'),
            'input' => $response->session()->get('_old_input', []),
            'errors' => $response->session()->get('errors', 'No errors'),
            'flash' => $response->session()->get('_flash', []),
            'redirect_session' => $redirectResponse->hasSession() ? $redirectResponse->session()->all() : 'No redirect session',
        ]);

        // Temporary workaround: Check validation failure indirectly
        $this->assertDatabaseMissing('permissions', ['name' => 'edit-posts']);
    }

    public function test_update_permission()
    {
        $permission = Permission::create(['name' => 'edit-posts']);
        $data = ['name' => 'update-posts'];

        $response = $this->actingAs($this->admin)->post(route('backend.permissions.update', $permission), $data);
        $response->assertRedirect(route('backend.permissions.index'));
        $response->assertSessionHas('success', __('messages.permission_updated'));

        $this->assertDatabaseHas('permissions', ['name' => 'update-posts']);
    }

    public function test_destroy_permission()
    {
        $permission = Permission::create(['name' => 'edit-posts']);

        $response = $this->actingAs($this->admin)->delete(route('backend.permissions.destroy', $permission));
        $response->assertRedirect(route('backend.permissions.index'));
        $response->assertSessionHas('success', __('messages.permission_deleted'));

        $this->assertDatabaseMissing('permissions', ['name' => 'edit-posts']);
    }

    public function test_manage_user_roles_permissions()
    {
        $response = $this->actingAs($this->admin)->get(route('backend.roles.manage'));
        $response->assertStatus(200);
        $response->assertViewIs('backend.roles.manage');
        $response->assertViewHas(['users', 'roles', 'permissions']);
    }

    public function test_update_user_roles_permissions()
    {
        $user = User::factory()->create();
        $data = [
            'user_id' => $user->id,
            'roles' => ['admin'],
            'permissions' => ['access-admin-panel'],
        ];

        $response = $this->actingAs($this->admin)->post(route('backend.roles.update-user'), $data);
        $response->assertRedirect(route('backend.roles.manage'));
        $response->assertSessionHas('success', __('messages.user_roles_permissions_updated'));

        $this->assertTrue($user->fresh()->hasRole('admin'));
        $this->assertTrue($user->fresh()->hasPermissionTo('access-admin-panel'));
    }

    public function test_get_user_roles_permissions()
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $user->givePermissionTo('access-admin-panel');

        $response = $this->actingAs($this->admin)->get(route('backend.roles.get-user', $user));
        $response->assertStatus(200);
        $response->assertJson([
            'roles' => ['admin'],
            'permissions' => ['access-admin-panel'],
        ]);
    }
}
