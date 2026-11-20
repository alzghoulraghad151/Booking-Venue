<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    public function test_guest_json_requests_to_admin_pages_are_unauthorized(): void
    {
        $this->getJson('/admin/users')->assertUnauthorized();
        $this->getJson('/admin/users/1')->assertUnauthorized();
    }

    public function test_guests_are_redirected_when_the_shared_login_route_exists(): void
    {
        if (! Route::has('login')) {
            Route::get('/test-login', fn () => 'Login')->name('login');
            Route::getRoutes()->refreshNameLookups();
        }

        $this->get('/admin/users')->assertRedirectToRoute('login');
    }

    public function test_non_admin_roles_and_suspended_admins_are_forbidden(): void
    {
        foreach ([
            ['organizer', 'active'],
            ['venue_owner', 'active'],
            ['admin', 'suspended'],
        ] as [$role, $status]) {
            $this->actingAs((new User(['name' => 'Test User']))->forceFill([
                'role' => $role,
                'status' => $status,
            ]));

            $this->get('/admin/users')->assertForbidden();
            $this->get('/admin/users/1')->assertForbidden();
        }
    }

    public function test_dashboard_is_only_available_to_active_admins(): void
    {
        $this->getJson('/admin/dashboard')->assertUnauthorized();

        $this->actingAs((new User())->forceFill([
            'role' => 'organizer',
            'status' => 'active',
        ]));
        $this->get('/admin/dashboard')->assertForbidden();

        $this->actingAs((new User())->forceFill([
            'role' => 'admin',
            'status' => 'active',
        ]));
        $this->get('/admin/dashboard')
            ->assertOk()
            ->assertViewIs('admin.dashboard')
            ->assertSee('Admin Dashboard')
            ->assertSee('Total Users')
            ->assertSee('aria-current="page"', false);
    }
}
