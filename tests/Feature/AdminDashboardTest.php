<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-20 12:00:00', 'UTC'));

        config([
            'database.default' => 'dashboard_test',
            'database.connections.dashboard_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
        ]);

        // Only the columns read by the dashboard, in an isolated test database.
        foreach ([
            'users (id INTEGER PRIMARY KEY, name TEXT, role TEXT, deleted_at TEXT)',
            'venues (id INTEGER PRIMARY KEY, name TEXT, status TEXT, created_at TEXT, deleted_at TEXT)',
            'bookings (id INTEGER PRIMARY KEY, customer_id INTEGER, venue_id INTEGER, status TEXT, created_at TEXT)',
            'booking_details (id INTEGER PRIMARY KEY, booking_id INTEGER, commission_amount DECIMAL, start_at TEXT)',
            'payments (id INTEGER PRIMARY KEY, status TEXT, amount DECIMAL, paid_at TEXT)',
            'reviews (id INTEGER PRIMARY KEY, booking_id INTEGER, rating INTEGER, comment TEXT, created_at TEXT)',
        ] as $table) {
            DB::statement('CREATE TABLE '.$table);
        }

        $this->actingAs((new User())->forceFill(['role' => 'admin', 'status' => 'active']));
    }

    public function test_dashboard_renders_empty_sections(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('No bookings yet.')
            ->assertSee('No venues awaiting review.')
            ->assertSee('November 2026')
            ->assertDontSee('No ratings yet')
            ->assertSee('0.00 JOD');
    }

    public function test_dashboard_renders_monthly_totals_and_reviews_are_on_their_own_page(): void
    {
        DB::table('users')->insert(['id' => 1, 'name' => 'Test Organizer', 'role' => 'organizer']);
        DB::table('venues')->insert(['id' => 1, 'name' => 'Test Conference Hall', 'status' => 'pending']);
        foreach (['confirmed', 'completed', 'pending', 'cancelled', 'rejected'] as $index => $status) {
            $id = $index + 1;
            DB::table('bookings')->insert([
                'id' => $id,
                'customer_id' => 1,
                'venue_id' => 1,
                'status' => $status,
                'created_at' => '2026-11-19 10:00:00',
            ]);
            DB::table('booking_details')->insert(['booking_id' => $id, 'commission_amount' => $id * 10, 'start_at' => '2026-11-19 13:00:00']);
        }

        DB::table('booking_details')->insert([
            ['booking_id' => 1, 'commission_amount' => 100, 'start_at' => '2026-10-31 20:59:59'],
            ['booking_id' => 2, 'commission_amount' => 200, 'start_at' => '2026-11-30 21:00:00'],
        ]);

        DB::table('payments')->insert([
            ['status' => 'paid', 'amount' => 120, 'paid_at' => '2026-10-31 21:00:00'],
            ['status' => 'paid', 'amount' => 400, 'paid_at' => '2026-10-31 20:59:59'],
            ['status' => 'paid', 'amount' => 500, 'paid_at' => '2026-11-30 21:00:00'],
            ['status' => 'paid', 'amount' => 600, 'paid_at' => null],
            ['status' => 'pending', 'amount' => 65, 'paid_at' => '2026-11-05 08:00:00'],
        ]);
        DB::table('reviews')->insert([
            'booking_id' => 2,
            'rating' => 4,
            'comment' => 'Good space for a workshop.',
            'created_at' => '2026-11-19 12:00:00',
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('monthlyCommission', fn ($total) => (float) $total === 30.0)
            ->assertViewHas('monthlyPaid', fn ($total) => (float) $total === 120.0)
            ->assertViewHas('paymentCounts', fn ($counts) => (int) $counts['pending'] === 1)
            ->assertSee(route('admin.reviews.index'))
            ->assertDontSee('Good space for a workshop.')
            ->assertDontSee('<h2>Venue Owners</h2>', false)
            ->assertDontSee('<h2>Pending Venues</h2>', false)
            ->assertSee('Test Organizer')
            ->assertSee(route('admin.venues.index', ['status' => 'pending']))
            ->assertSee(route('admin.venues.show', 1));

        $this->get(route('admin.reviews.index'))
            ->assertOk()
            ->assertSee('Average rating: 4.0 / 5')
            ->assertSee('Good space for a workshop.');

        $this->actingAs((new User())->forceFill(['role' => 'venue_owner', 'status' => 'active']));
        $this->get(route('admin.reviews.index'))->assertForbidden();
    }
}
