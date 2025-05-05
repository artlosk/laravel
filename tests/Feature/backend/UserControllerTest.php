<?php

namespace Tests\Feature\backend;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::create(['name' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $this->admin->givePermissionTo('manage-users');

        $this->user = User::factory()->create();
    }

    /** @test */
    public function admin_can_view_users_index()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('backend.users.index'));

        $response->assertOk();
        $response->assertViewHas('users');
    }

    /** @test */
    public function non_admin_cannot_view_users_index()
    {
        $response = $this->actingAs($this->user)
            ->get(route('backend.users.index'));

        $response->assertForbidden();
    }

    /** @test */
    public function admin_can_create_user()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'roles' => ['admin']
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('backend.users.store'), $data);

        $response->assertRedirect(route('backend.users.index'));
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    // Добавьте остальные тесты для edit, update, delete
}
