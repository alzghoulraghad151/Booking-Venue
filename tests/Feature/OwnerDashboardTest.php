<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OwnerDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'owner_dashboard_test',
            'database.connections.owner_dashboard_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
        ]);

        foreach ([
            'users (id INTEGER PRIMARY KEY, name TEXT, deleted_at TEXT)',
            'venues (id INTEGER PRIMARY KEY, owner_id INTEGER, name TEXT, deleted_at TEXT)',
            'spaces (id INTEGER PRIMARY KEY, venue_id INTEGER, name TEXT, status TEXT, created_at TEXT)',
            'bookings (id INTEGER PRIMARY KEY, venue_id INTEGER, customer_id INTEGER, status TEXT, created_at TEXT)',
            'booking_details (id INTEGER PRIMARY KEY, booking_id INTEGER)',
        ] as $table) {
            DB::statement('CREATE TABLE '.$table);
        }
    }

    public function test_each_owner_sees_only_their_own_records_and_counts(): void
    {
        DB::table('users')->insert([
            ['id' => 10, 'name' => 'First Organizer'],
            ['id' => 20, 'name' => 'Second Organizer'],
        ]);
        DB::table('venues')->insert([
            ['id' => 1, 'owner_id' => 1, 'name' => 'First Venue', 'deleted_at' => null],
            ['id' => 2, 'owner_id' => 2, 'name' => 'Second Venue', 'deleted_at' => null],
            ['id' => 3, 'owner_id' => 1, 'name' => 'Removed Venue', 'deleted_at' => '2026-11-18'],
        ]);
        DB::table('spaces')->insert([
            ['venue_id' => 1, 'name' => 'First Space', 'status' => 'active'],
            ['venue_id' => 2, 'name' => 'Second Space', 'status' => 'active'],
            ['venue_id' => 3, 'name' => 'Removed Space', 'status' => 'active'],
        ]);
        DB::table('bookings')->insert([
            ['id' => 11, 'venue_id' => 1, 'customer_id' => 10, 'status' => 'pending'],
            ['id' => 12, 'venue_id' => 1, 'customer_id' => 10, 'status' => 'confirmed'],
            ['id' => 13, 'venue_id' => 2, 'customer_id' => 20, 'status' => 'confirmed'],
            ['id' => 14, 'venue_id' => 3, 'customer_id' => 10, 'status' => 'pending'],
        ]);
        DB::table('booking_details')->insert([
            ['booking_id' => 11], ['booking_id' => 11], ['booking_id' => 12],
            ['booking_id' => 13], ['booking_id' => 14],
        ]);

        $this->signIn(1);
        $this->get(route('owner.dashboard'))
            ->assertOk()
            ->assertViewHasAll(['totalVenues' => 1, 'totalSpaces' => 1, 'pendingDetails' => 2, 'confirmedDetails' => 1])
            ->assertSee('First Organizer')
            ->assertSee('First Space')
            ->assertDontSee('Second Organizer')
            ->assertDontSee('Second Space')
            ->assertDontSee('Removed Venue')
            ->assertDontSee('Removed Space')
            ->assertSee(route('owner.venues.create'));

        $this->signIn(2);
        $this->get(route('owner.dashboard'))
            ->assertOk()
            ->assertViewHasAll(['totalVenues' => 1, 'totalSpaces' => 1, 'pendingDetails' => 0, 'confirmedDetails' => 1])
            ->assertSee('Second Organizer')
            ->assertSee('Second Space')
            ->assertDontSee('First Organizer')
            ->assertDontSee('First Space');
    }

    public function test_new_owner_sees_empty_states(): void
    {
        $this->signIn(3);
        $this->get(route('owner.dashboard'))
            ->assertOk()
            ->assertViewHasAll(['totalVenues' => 0, 'totalSpaces' => 0, 'pendingDetails' => 0, 'confirmedDetails' => 0])
            ->assertSee('No bookings yet.')
            ->assertSee('No spaces added yet.');
    }

    public function test_dashboard_requires_an_active_owner(): void
    {
        $guest = $this->get(route('owner.dashboard'));
        if (Route::has('login')) {
            $guest->assertRedirectToRoute('login');
        } else {
            $guest->assertUnauthorized();
        }

        foreach ([['organizer', 'active'], ['admin', 'active'], ['venue_owner', 'suspended']] as [$role, $status]) {
            $this->signIn(1, $role, $status);
            $this->get(route('owner.dashboard'))->assertForbidden();
        }
    }

    private function signIn(int $id, string $role = 'venue_owner', string $status = 'active'): void
    {
        $this->actingAs((new User())->forceFill(['id' => $id, 'role' => $role, 'status' => $status]));
    }
}
