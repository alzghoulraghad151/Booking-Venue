<?php

namespace Tests\Feature;

use App\Models\EventType;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AdminEventTypesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'event_types_test',
            'database.connections.event_types_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'foreign_key_constraints' => true,
            ],
        ]);

        (require database_path('migrations/2026_11_18_230007_create_event_types_table.php'))->up();

        foreach (['booking_details', 'layout_event_types', 'package_event_types'] as $table) {
            DB::connection()->getSchemaBuilder()->create($table, function (Blueprint $table) {
                $table->id();
                $table->foreignId('event_type_id')->constrained();
            });
        }
    }

    public function test_active_admin_can_list_create_edit_and_delete_an_unused_type(): void
    {
        $this->signIn();
        $this->get(route('admin.event-types.index'))->assertOk()->assertSee('No event types yet.');
        $this->get(route('admin.event-types.create'))->assertOk()->assertSee('Corporate Conference');

        $this->post(route('admin.event-types.store'), ['name' => 'Dinner'])
            ->assertRedirectToRoute('admin.event-types.index');
        $type = EventType::sole();

        $this->get(route('admin.event-types.index', ['search' => 'Din']))->assertOk()->assertSee('Dinner');
        $this->get(route('admin.event-types.index', ['search' => 'Networking']))
            ->assertOk()->assertSee('No event types match your search.');
        $this->get(route('admin.event-types.edit', $type))->assertOk()->assertSee('Edit Event Type');

        $this->put(route('admin.event-types.update', $type), [
            'name' => 'Dinner',
            'description' => 'Corporate dining',
        ])->assertRedirectToRoute('admin.event-types.index');
        $this->assertDatabaseHas('event_types', ['id' => $type->id, 'description' => 'Corporate dining']);

        $this->delete(route('admin.event-types.destroy', $type))
            ->assertRedirectToRoute('admin.event-types.index');
        $this->assertDatabaseMissing('event_types', ['id' => $type->id]);
    }

    public function test_names_must_be_approved_unique_and_valid(): void
    {
        $this->signIn();
        $type = EventType::create(['name' => 'Dinner']);
        EventType::create(['name' => 'Networking']);

        foreach (['', str_repeat('x', 101), 'Wedding', 'Dinner'] as $name) {
            $this->post(route('admin.event-types.store'), ['name' => $name])->assertSessionHasErrors('name');
        }

        $this->put(route('admin.event-types.update', $type), ['name' => 'Networking'])
            ->assertSessionHasErrors('name');
        $this->assertDatabaseHas('event_types', ['id' => $type->id, 'name' => 'Dinner']);
    }

    public function test_linked_types_cannot_be_deleted(): void
    {
        $this->signIn();

        foreach (['booking_details', 'layout_event_types', 'package_event_types'] as $index => $table) {
            $type = EventType::create(['name' => EventType::APPROVED_NAMES[$index]]);
            DB::table($table)->insert(['event_type_id' => $type->id]);

            $this->delete(route('admin.event-types.destroy', $type))
                ->assertRedirectToRoute('admin.event-types.index')
                ->assertSessionHasErrors('delete');
            $this->assertDatabaseHas('event_types', ['id' => $type->id]);
        }

        $this->get(route('admin.event-types.index'))
            ->assertViewHas('eventTypes', fn ($types) => (int) $types->firstWhere('name', 'Dinner')->booking_details_count === 1);
    }

    public function test_only_active_admins_can_manage_event_types(): void
    {
        $type = EventType::create(['name' => 'Dinner']);
        $guest = $this->get(route('admin.event-types.index'));

        if (Route::has('login')) {
            $guest->assertRedirectToRoute('login');
        } else {
            $guest->assertUnauthorized();
        }

        foreach ([['organizer', 'active'], ['venue_owner', 'active'], ['admin', 'suspended']] as [$role, $status]) {
            $this->signIn($role, $status);
            $this->get(route('admin.event-types.index'))->assertForbidden();
            $this->get(route('admin.event-types.create'))->assertForbidden();
            $this->post(route('admin.event-types.store'), ['name' => 'Networking'])->assertForbidden();
            $this->get(route('admin.event-types.edit', $type))->assertForbidden();
            $this->put(route('admin.event-types.update', $type), ['name' => 'Networking'])->assertForbidden();
            $this->delete(route('admin.event-types.destroy', $type))->assertForbidden();
        }

        $this->assertDatabaseCount('event_types', 1);
    }

    private function signIn(string $role = 'admin', string $status = 'active'): void
    {
        $this->actingAs((new User())->forceFill(['role' => $role, 'status' => $status]));
    }
}
