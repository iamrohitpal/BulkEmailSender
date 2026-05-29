<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\User;
use App\Models\SmtpSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaasWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test user registration and login flows.
     */
    public function test_user_can_register_and_login(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'John Candidate',
            'email' => 'john@candidate.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertDatabaseHas('users', ['email' => 'john@candidate.com']);

        // Log out
        $response = $this->actingAs(User::first(), 'web')->post(route('logout'));
        $response->assertRedirect(route('login'));

        // Log back in
        $response = $this->post(route('login'), [
            'email' => 'john@candidate.com',
            'password' => 'password123',
        ]);
        $response->assertRedirect(route('user.dashboard'));
    }

    /**
     * Test SMTP Settings CRUD for User.
     */
    public function test_user_can_manage_smtp_settings(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user, 'web')
            ->post(route('user.smtp.store'), [
                'name' => 'My Personal SMTP',
                'host' => 'smtp.mailtrap.io',
                'port' => 2525,
                'username' => 'testuser',
                'password' => 'testpass',
                'encryption' => 'tls',
                'from_address' => 'sender@domain.com',
                'from_name' => 'My Sender Name',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('user.smtp.index'));
        $this->assertDatabaseHas('smtp_settings', [
            'user_id' => $user->id,
            'name' => 'My Personal SMTP',
            'is_active' => true,
        ]);

        $smtp = SmtpSetting::first();

        // Edit/Update SMTP
        $response = $this->actingAs($user, 'web')
            ->put(route('user.smtp.update', $smtp->id), [
                'name' => 'My Updated SMTP',
                'host' => 'smtp.mailtrap.io',
                'port' => 587,
                'username' => 'testuser',
                'password' => 'newpass',
                'encryption' => 'tls',
                'from_address' => 'updated@domain.com',
                'from_name' => 'My Updated Sender Name',
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('user.smtp.index'));
        $this->assertDatabaseHas('smtp_settings', [
            'id' => $smtp->id,
            'name' => 'My Updated SMTP',
            'from_address' => 'updated@domain.com',
        ]);

        // Delete SMTP
        $response = $this->actingAs($user, 'web')
            ->delete(route('user.smtp.destroy', $smtp->id));

        $response->assertRedirect(route('user.smtp.index'));
        $this->assertDatabaseMissing('smtp_settings', ['id' => $smtp->id]);
    }

    /**
     * Test Super Admin dashboard, listing users, and toggling user state.
     */
    public function test_admin_can_manage_users_and_active_state(): void
    {
        // Create Admin
        $admin = Admin::create([
            'name' => 'SaaS Admin',
            'email' => 'admin@saas.com',
            'password' => 'password',
        ]);

        // Create active Candidate
        $user = User::factory()->create([
            'name' => 'Active Candidate',
            'email' => 'active@candidate.com',
            'is_active' => true,
        ]);

        // Log in admin
        $response = $this->actingAs($admin, 'admin')->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // View users list
        $response = $this->actingAs($admin, 'admin')->get(route('admin.users.index'));
        $response->assertStatus(200);
        $response->assertSee('Active Candidate');

        // Toggle candidate status to disabled
        $response = $this->actingAs($admin, 'admin')->post(route('admin.users.toggle', $user->id));
        $response->assertRedirect();
        
        $user->refresh();
        $this->assertFalse($user->is_active);

        // Try to access user dashboard as disabled user
        $response = $this->actingAs($user, 'web')->get(route('user.dashboard'));
        $response->assertRedirect(route('login'));
        $this->assertFalse(\Auth::guard('web')->check()); // Assert user was logged out
    }
}
