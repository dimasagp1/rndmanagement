<?php

namespace Tests\Feature;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $superadmin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed Roles and Permissions
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Create Users
        $this->superadmin = User::factory()->create();
        $this->superadmin->assignRole('Superadmin');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('Staff R&D');
    }

    public function test_superadmin_can_access_user_management_index()
    {
        $response = $this->actingAs($this->superadmin)->get(route('users.index'));
        $response->assertStatus(200);
        $response->assertViewHas('users');
    }

    public function test_staff_cannot_access_user_management_index()
    {
        $response = $this->actingAs($this->staff)->get(route('users.index'));
        $response->assertStatus(403);
    }

    public function test_superadmin_can_create_new_user_with_role()
    {
        $response = $this->actingAs($this->superadmin)->post(route('users.store'), [
            'name' => 'New User Test',
            'email' => 'newuser@herbatech.com',
            'role' => 'Operational Manager',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $newUser = User::where('email', 'newuser@herbatech.com')->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('New User Test', $newUser->name);
        $this->assertTrue($newUser->hasRole('Operational Manager'));

        $response->assertRedirect(route('users.index'));
    }

    public function test_superadmin_can_update_user_role()
    {
        $targetUser = User::factory()->create();
        $targetUser->assignRole('Staff R&D');

        $response = $this->actingAs($this->superadmin)->put(route('users.update', $targetUser), [
            'name' => 'Updated Name',
            'email' => $targetUser->email,
            'role' => 'General Manager',
        ]);

        $this->assertEquals('Updated Name', $targetUser->fresh()->name);
        $this->assertTrue($targetUser->fresh()->hasRole('General Manager'));
        $this->assertFalse($targetUser->fresh()->hasRole('Staff R&D'));

        $response->assertRedirect(route('users.index'));
    }

    public function test_superadmin_cannot_delete_themselves()
    {
        $response = $this->actingAs($this->superadmin)->delete(route('users.destroy', $this->superadmin));
        $this->assertNotNull(User::find($this->superadmin->id));
        $response->assertSessionHas('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
    }
}
