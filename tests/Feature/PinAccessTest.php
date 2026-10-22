<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PinAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_pin_login(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_pin_login_screen_can_be_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Enter PIN to unlock');
    }

    public function test_valid_pin_unlocks_workspace(): void
    {
        config(['crm.pin' => '8642']);

        $this->post('/login', ['pin' => '8642'])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('crm_unlocked', true);
    }

    public function test_invalid_pin_is_rejected(): void
    {
        config(['crm.pin' => '8642']);

        $this->post('/login', ['pin' => '0000'])
            ->assertSessionHasErrors('pin')
            ->assertSessionMissing('crm_unlocked');
    }

    public function test_lock_clears_workspace_access(): void
    {
        $this->unlock()
            ->post('/logout')
            ->assertRedirect('/login')
            ->assertSessionMissing('crm_unlocked');
    }
}
