<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventPoster;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Tout ce qui doit fonctionner sans disque persistant ni planificateur (Render gratuit). */
class FreeHostingTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
        $this->admin = User::create(['name' => 'Admin', 'email' => 'admin@test.tg', 'password' => 'secret12345', 'role' => 'admin', 'status' => 'active']);
    }

    private function eventPayload(array $extra = []): array
    {
        return array_merge([
            'title' => "Univers 2 M'boulè", 'description' => 'Stand-up', 'date' => now()->addDays(10)->toDateString(),
            'start_time' => '19:00', 'venue' => 'Palais', 'status' => 'published',
        ], $extra);
    }

    public function test_poster_is_stored_in_database_and_served_without_touching_the_disk(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.events.store'), $this->eventPayload(['image' => UploadedFile::fake()->image('affiche.png', 300, 400)]))
            ->assertRedirect(route('admin.events.index'));

        $event = Event::firstOrFail();
        $this->assertSame(1, EventPoster::count());
        $this->assertEmpty(Storage::disk('public')->allFiles());
        $this->assertNull($event->image);

        $response = $this->get(route('events.poster', $event));
        $response->assertOk()->assertHeader('Content-Type', 'image/png');
        $this->assertStringStartsWith("\x89PNG", $response->getContent());

        // Le site public référence l'affiche via la route (et non un fichier disque).
        $this->get(route('home'))->assertOk()->assertSee('/events/' . $event->id . '/affiche', false);
        $this->get(route('events.show', $event))->assertOk()->assertSee('/affiche', false);
        $this->actingAs($this->admin)->get(route('admin.events.index'))->assertOk()->assertSee('/affiche', false);
    }

    public function test_replacing_the_poster_updates_it_instead_of_duplicating(): void
    {
        $this->actingAs($this->admin)->post(route('admin.events.store'), $this->eventPayload(['image' => UploadedFile::fake()->image('a.png')]));
        $event = Event::firstOrFail();
        $first = EventPoster::firstOrFail()->data;

        $this->actingAs($this->admin)->put(route('admin.events.update', $event), $this->eventPayload(['image' => UploadedFile::fake()->image('b.jpg', 50, 50)]))
            ->assertRedirect();

        $this->assertSame(1, EventPoster::count());
        $this->assertNotSame($first, EventPoster::firstOrFail()->data);

        // Modifier le spectacle sans nouvelle image garde l'affiche.
        $this->actingAs($this->admin)->put(route('admin.events.update', $event), $this->eventPayload(['title' => 'Nouveau titre']))->assertRedirect();
        $this->assertSame(1, EventPoster::count());
    }

    public function test_unpublished_event_poster_is_not_public(): void
    {
        $this->actingAs($this->admin)->post(route('admin.events.store'), $this->eventPayload(['status' => 'draft', 'image' => UploadedFile::fake()->image('a.png')]));

        $this->get(route('events.poster', Event::firstOrFail()))->assertNotFound();
    }

    public function test_qr_codes_are_generated_on_the_fly_on_page_and_pdf(): void
    {
        config(['services.support.whatsapp' => '+22890000000']);
        $event = Event::create(['user_id' => $this->admin->id] + $this->eventPayload());
        $type = TicketType::create(['event_id' => $event->id, 'name' => 'Standard', 'price' => 5000, 'quantity' => 10, 'available_quantity' => 10, 'status' => 'active']);

        $order = app(TicketService::class)->createReservation($event, ['name' => 'Ama', 'phone' => '+22890123456'], [['ticket_type_id' => $type->id, 'quantity' => 1]]);
        app(TicketService::class)->markAsPaid($order, 'cash');

        $this->assertEmpty(Storage::disk('public')->allFiles(), 'aucun fichier ne doit être écrit sur le disque');

        $this->get(route('ticket.show', $order->reference))->assertOk()->assertSee('data:image/svg+xml;base64,', false);

        $ticket = $order->tickets()->firstOrFail();
        $pdf = $this->get(route('ticket.download', $ticket->ticket_number));
        $pdf->assertOk();
        $this->assertStringStartsWith('%PDF', $pdf->getContent());
        file_put_contents(sys_get_temp_dir() . '/billet-test.pdf', $pdf->getContent());
    }

    public function test_expired_orders_are_released_without_any_scheduler(): void
    {
        $event = Event::create(['user_id' => $this->admin->id] + $this->eventPayload());
        $type = TicketType::create(['event_id' => $event->id, 'name' => 'Standard', 'price' => 5000, 'quantity' => 10, 'available_quantity' => 10, 'status' => 'active']);

        $order = app(TicketService::class)->createReservation($event, ['name' => 'Ama', 'phone' => '+22890123456'], [['ticket_type_id' => $type->id, 'quantity' => 4]]);
        $this->assertSame(6, $type->fresh()->available_quantity);

        $order->update(['expires_at' => now()->subMinute()]);

        // Il suffit qu'un visiteur ouvre le site : les places reviennent en vente.
        $this->get(route('home'))->assertOk();

        $this->assertSame('expired', $order->fresh()->status);
        $this->assertSame(10, $type->fresh()->available_quantity);
    }

    public function test_a_new_reservation_frees_expired_stock_first(): void
    {
        $event = Event::create(['user_id' => $this->admin->id] + $this->eventPayload());
        $type = TicketType::create(['event_id' => $event->id, 'name' => 'VIP', 'price' => 15000, 'quantity' => 2, 'available_quantity' => 2, 'status' => 'active']);
        $service = app(TicketService::class);

        $old = $service->createReservation($event, ['name' => 'A', 'phone' => '+22890000001'], [['ticket_type_id' => $type->id, 'quantity' => 2]]);
        $old->update(['expires_at' => now()->subHour()]);

        $new = $service->createReservation($event, ['name' => 'B', 'phone' => '+22890000002'], [['ticket_type_id' => $type->id, 'quantity' => 2]]);

        $this->assertSame('expired', $old->fresh()->status);
        $this->assertSame('pending', $new->status);
        $this->assertSame(0, $type->fresh()->available_quantity);
    }

    public function test_first_admin_is_created_once_and_never_overwritten(): void
    {
        User::query()->delete();

        $this->artisan('admin:create', ['--email' => 'boss@test.tg', '--password' => 'un-mot-de-passe-solide'])->assertSuccessful();
        $boss = User::where('email', 'boss@test.tg')->firstOrFail();
        $this->assertTrue($boss->isAdmin());
        $hash = $boss->password;

        $this->artisan('admin:create', ['--email' => 'autre@test.tg', '--password' => 'autre-mot-de-passe-solide'])->assertSuccessful();

        $this->assertSame(1, User::where('role', 'admin')->count());
        $this->assertSame($hash, $boss->fresh()->password);
    }

    public function test_admin_creation_refuses_a_weak_password(): void
    {
        User::query()->delete();

        $this->artisan('admin:create', ['--email' => 'boss@test.tg', '--password' => 'court'])->assertFailed();
        $this->assertSame(0, User::count());
    }
}
