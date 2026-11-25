<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginTest extends TestCase
{
    public function test_login_page_renders_without_turnstile_when_disabled(): void
    {
        config(['services.turnstile.enabled' => false]);

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('challenges.cloudflare.com/turnstile/v0/api.js');
        $response->assertDontSee('cf-turnstile');
    }

    public function test_login_page_renders_with_turnstile_when_enabled_with_sitekey(): void
    {
        config([
            'services.turnstile.enabled' => true,
            'services.turnstile.site_key' => '1x00000000000000000000AA',
        ]);

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('1x00000000000000000000AA');
        $response->assertSee('challenges.cloudflare.com/turnstile/v0/api.js');
    }

    public function test_turnstile_response_is_not_required_when_disabled(): void
    {
        config(['services.turnstile.enabled' => false]);

        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionDoesntHaveErrors(['cf-turnstile-response']);
        $response->assertSessionHasErrors(['email', 'password']);
    }

    public function test_turnstile_response_is_required_when_enabled(): void
    {
        config([
            'services.turnstile.enabled' => true,
            'services.turnstile.secret_key' => '1x0000000000000000000000000000000AA',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@abstrack.com',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['cf-turnstile-response']);
    }
}
