<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_security_assessment(): void
    {
        $response = $this->get('/security/assessment');
        $response->assertRedirect('/login');
    }

    public function test_cashier_user_is_blocked_from_security_assessment(): void
    {
        $cashier = User::create([
            'name'     => 'Cashier User',
            'email'    => 'cashier_test@abstrack.com',
            'password' => bcrypt('password'),
            'role'     => 'cashier',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($cashier)->get('/security/assessment');
        $response->assertRedirect('/');
        $response->assertSessionHas('error');
    }

    public function test_admin_user_can_access_security_assessment(): void
    {
        $admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin_test@abstrack.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/security/assessment');
        $response->assertStatus(200);
        $response->assertSee('CyberShield Security Center', false);
    }

    public function test_manager_user_can_access_security_assessment(): void
    {
        $manager = User::create([
            'name'     => 'Gym Manager',
            'email'    => 'manager_test@abstrack.com',
            'password' => bcrypt('password'),
            'role'     => 'manager',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($manager)->get('/security/assessment');
        $response->assertStatus(200);
        $response->assertSee('CyberShield Security Center', false);
        $response->assertSee('System Hardening', false);
        $response->assertSee('Configuration Scanner', false);
        $response->assertSee('AI Threat', false);
        $response->assertSee('OWASP Top 10 Vulnerability Matrix', false);
        $response->assertSee('CyberShield AI Security Advisor', false);
    }

    public function test_owner_user_can_access_security_assessment(): void
    {
        $owner = User::create([
            'name'     => 'Gym Owner',
            'email'    => 'owner_test@abstrack.com',
            'password' => bcrypt('password'),
            'role'     => 'owner',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($owner)->get('/security/assessment');
        $response->assertStatus(200);
        $response->assertSee('Owner, Manager & Admin Clearance', false);
        $response->assertSee('CyberShield Security Center', false);
    }

    public function test_sidebar_hides_security_link_for_cashier(): void
    {
        $cashier = User::create([
            'name'     => 'Cashier User',
            'email'    => 'cashier_sidebar@abstrack.com',
            'password' => bcrypt('password'),
            'role'     => 'cashier',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($cashier)->get('/');
        $response->assertDontSee('CyberShield AI');
    }

    public function test_sidebar_shows_security_link_for_admin_owner_and_manager(): void
    {
        $admin = User::create([
            'name'     => 'Gym Admin',
            'email'    => 'admin_sidebar@abstrack.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/');
        $response->assertSee('CyberShield AI');
        $response->assertSee('AI SEC');
    }

    public function test_manager_can_run_on_demand_scan(): void
    {
        $manager = User::create([
            'name'     => 'Gym Manager',
            'email'    => 'manager_scan@abstrack.com',
            'password' => bcrypt('password'),
            'role'     => 'manager',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($manager)->postJson('/security/scan/run');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'scan' => ['health_score', 'total_checks', 'passed_checks']
        ]);
    }

    public function test_manager_can_query_ai_security_advisor(): void
    {
        $manager = User::create([
            'name'     => 'Gym Manager',
            'email'    => 'manager_ai@abstrack.com',
            'password' => bcrypt('password'),
            'role'     => 'manager',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($manager)->postJson('/security/advisor/query', [
            'query' => 'Audit cashier refund security',
            'topic' => 'financial'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'consultation' => ['query', 'ai_response', 'suggested_actions']
        ]);
        $response->assertSee('RefundService');
    }

    public function test_manager_can_run_ai_threat_log_analysis(): void
    {
        $manager = User::create([
            'name'     => 'Gym Manager',
            'email'    => 'manager_threats@abstrack.com',
            'password' => bcrypt('password'),
            'role'     => 'manager',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($manager)->postJson('/security/threats/analyze');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'new_anomalies',
            'active_count',
            'alerts'
        ]);
    }

    public function test_manager_can_export_pdf_security_audit_report(): void
    {
        $manager = User::create([
            'name'     => 'Gym Manager',
            'email'    => 'manager_pdf@abstrack.com',
            'password' => bcrypt('password'),
            'role'     => 'manager',
            'status'   => 'active',
        ]);

        $response = $this->actingAs($manager)->get('/security/export-report');

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('content-type'));
        $this->assertTrue(str_contains($response->headers->get('content-disposition'), 'AbsTrack-Security-Audit-Report'));
    }
}
