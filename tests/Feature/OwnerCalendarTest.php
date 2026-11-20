<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OwnerCalendarTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'owner_calendar_test',
            'database.connections.owner_calendar_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
        ]);

        foreach ([
            'users (id INTEGER PRIMARY KEY, name TEXT, deleted_at TEXT)',
            'venues (id INTEGER PRIMARY KEY, owner_id INTEGER, name TEXT, deleted_at TEXT)',
            'spaces (id INTEGER PRIMARY KEY, venue_id INTEGER, name TEXT)',
            'bookings (id INTEGER PRIMARY KEY, venue_id INTEGER, customer_id INTEGER, status TEXT)',
            'event_types (id INTEGER PRIMARY KEY, name TEXT)',
            'booking_details (id INTEGER PRIMARY KEY, booking_id INTEGER, space_id INTEGER, event_type_id INTEGER, start_at TEXT, end_at TEXT, guest_count INTEGER)',
        ] as $table) {
            DB::statement('CREATE TABLE '.$table);
        }
    }

    public function test_calendar_scopes_spaces_and_bookings_to_the_signed_in_owner(): void
    {
        $this->catalog();
        $this->booking(1, 1, 1, 'confirmed', '2026-11-19 08:00:00', '2026-11-19 09:00:00');
        $this->booking(2, 2, 2, 'confirmed', '2026-11-19 08:00:00', '2026-11-19 09:00:00');
        $this->booking(3, 2, 1, 'pending', '2026-11-19 08:00:00', '2026-11-19 09:00:00');

        $this->signIn(1);
        $this->calendar(['date' => '2026-11-19'])
            ->assertOk()->assertSee('Booking #1')->assertSee('Conference')
            ->assertDontSee('Second Hall')->assertDontSee('Booking #2')->assertDontSee('Booking #3');
        $this->calendar(['space_id' => 2])->assertNotFound();

        $this->signIn(2);
        $this->calendar(['date' => '2026-11-19'])
            ->assertOk()->assertSee('Booking #2')->assertDontSee('First Hall')
            ->assertDontSee('Booking #1')->assertDontSee('Booking #3');
    }

    public function test_day_view_converts_utc_and_marks_only_overlapping_active_intervals(): void
    {
        $this->catalog();
        $this->signIn(1);
        $this->booking(1, 1, 1, 'confirmed', '2026-11-18 20:30:00', '2026-11-18 22:00:00');
        $this->booking(2, 1, 1, 'pending', '2026-11-18 21:30:00', '2026-11-18 22:00:00');
        $this->booking(3, 1, 1, 'pending', '2026-11-18 22:00:00', '2026-11-18 23:00:00');
        $this->booking(4, 1, 1, 'cancelled', '2026-11-18 22:15:00', '2026-11-18 22:45:00');
        $this->booking(5, 1, 1, 'rejected', '2026-11-19 21:00:00', '2026-11-19 22:00:00');

        $response = $this->calendar(['date' => '2026-11-19', 'view' => 'day', 'space_id' => 1]);
        $response->assertOk()->assertSee('00:30')->assertDontSee('Booking #5');
        $events = $response->viewData('days')['2026-11-19']['events'];
        $this->assertSame([true, true, false, false], array_column($events, 'overlap'));
        $this->assertSame('00:00', $events[0]['start']->format('H:i'));
    }

    public function test_week_and_month_boundaries_and_invalid_filters(): void
    {
        $this->catalog();
        $this->signIn(1);
        $this->booking(1, 1, 1, 'confirmed', '2026-11-28 10:00:00', '2026-11-28 11:00:00');
        $this->calendar(['date' => '2026-11-19', 'view' => 'week'])->assertOk()->assertDontSee('Booking #1');
        $this->calendar(['date' => '2026-11-19', 'view' => 'month'])->assertOk()->assertSee('Booking #1');
        $this->calendar(['date' => '2026-02-30', 'view' => 'year', 'space_id' => -1])
            ->assertRedirectToRoute('owner.calendar.index')
            ->assertSessionHasErrors(['date', 'view', 'space_id']);
    }

    public function test_only_active_owners_have_access(): void
    {
        $guest = $this->calendar();
        if (Route::has('login')) {
            $guest->assertRedirectToRoute('login');
        } else {
            $guest->assertUnauthorized();
        }

        foreach ([['organizer', 'active'], ['admin', 'active'], ['venue_owner', 'suspended']] as [$role, $status]) {
            $this->signIn(1, $role, $status);
            $this->calendar()->assertForbidden();
        }

        $this->signIn(1);
        $this->calendar()->assertOk()->assertSee('No spaces yet.');
    }

    private function calendar(array $filters = [])
    {
        return $this->get(route('owner.calendar.index', $filters));
    }

    private function signIn(int $id, string $role = 'venue_owner', string $status = 'active'): void
    {
        $this->actingAs((new User())->forceFill(['id' => $id, 'role' => $role, 'status' => $status]));
    }

    private function catalog(): void
    {
        DB::table('users')->insert(['id' => 10, 'name' => 'Organizer']);
        DB::table('venues')->insert([
            ['id' => 1, 'owner_id' => 1, 'name' => 'First Venue'],
            ['id' => 2, 'owner_id' => 2, 'name' => 'Second Venue'],
        ]);
        DB::table('spaces')->insert([
            ['id' => 1, 'venue_id' => 1, 'name' => 'First Hall'],
            ['id' => 2, 'venue_id' => 2, 'name' => 'Second Hall'],
        ]);
        DB::table('event_types')->insert(['id' => 1, 'name' => 'Conference']);
    }

    private function booking(int $id, int $venue, int $space, string $status, string $start, string $end): void
    {
        DB::table('bookings')->insert([
            'id' => $id, 'venue_id' => $venue, 'customer_id' => 10, 'status' => $status,
        ]);
        DB::table('booking_details')->insert([
            'booking_id' => $id,
            'space_id' => $space,
            'event_type_id' => 1,
            'start_at' => $start,
            'end_at' => $end,
            'guest_count' => 40,
        ]);
    }
}
