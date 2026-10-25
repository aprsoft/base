<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get('/settings/profile')->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        User::factory()->create(); // Cuenta principal protegida (ID 1).
        $user = User::factory()->create(); // Usuario ordinario (ID 2).

        $response = $this
            ->actingAs($user)
            ->put('/settings/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_email_address_is_unchanged(): void
    {
        User::factory()->create(); // Cuenta principal protegida (ID 1).
        $user = User::factory()->create(); // Usuario ordinario (ID 2).

        $response = $this
            ->actingAs($user)
            ->put('/settings/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_cannot_delete_their_account_from_profile(): void
    {
        User::factory()->create(); // Cuenta principal protegida (ID 1).
        $user = User::factory()->create(); // Usuario ordinario (ID 2).

        $this->actingAs($user)
            ->delete('/settings/profile')
            ->assertNotFound();

        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => null,
        ]);
    }
}
