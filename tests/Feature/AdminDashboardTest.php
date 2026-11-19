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
            'booking_details (id INTEGER PRIMARY KEY, booking_id INTEGER, commission_amount DECIMAL)',
            'payments (id INTEGER PRIMARY KEY, status TEXT, amount DECIMAL)',
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
            ->assertSee('No ratings yet')
            ->assertSee('0.00 JOD');
    }

    public function test_dashboard_renders_totals_reviews_and_available_venue_links(): void
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
            DB::table('booking_details')->insert(['booking_id' => $id, 'commission_amount' => $id * 10]);
        }

        DB::table('payments')->insert([
            ['status' => 'paid', 'amount' => 120],
            ['status' => 'pending', 'amount' => 65],
        ]);
        DB::table('reviews')->insert([
            'booking_id' => 2,
            'rating' => 4,
            'comment' => 'Good space for a workshop.',
            'created_at' => '2026-11-19 12:00:00',
        ]);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertViewHas('totalCommission', fn ($total) => (float) $total === 30.0)
            ->assertViewHas('totalPaid', fn ($total) => (float) $total === 120.0)
            ->assertViewHas('paymentCounts', fn ($counts) => (int) $counts['pending'] === 1)
            ->assertSee('Average rating: 4.0 / 5')
            ->assertSee('Good space for a workshop.')
            ->assertSee('Test Organizer')
            ->assertSee(route('admin.venues.index', ['status' => 'pending']))
            ->assertSee(route('admin.venues.show', 1));
    }
}
