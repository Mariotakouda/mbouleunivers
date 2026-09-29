<?php

namespace Tests\Feature;

use App\Livewire\CheckoutForm;
use App\Models\Event;
use App\Models\Order;
use App\Models\TicketType;
use App\Models\User;
use App\Notifications\NewOrderNotification;
use App\Services\TicketService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class WhatsAppOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Event $event;
    private TicketType $standard;
    private TicketType $vip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('public');
        config(['services.support.whatsapp' => '+228 90 00 00 00']);

        $this->admin = User::create([
            'name' => 'Admin', 'email' => 'admin@test.tg', 'password' => 'secret123',
            'role' => 'admin', 'status' => 'active', 'phone' => '+22890000000',
        ]);

        $this->event = Event::create([
            'user_id' => $this->admin->id, 'title' => "Univers 2 M'boulè", 'description' => 'Stand-up',
            'date' => now()->addDays(20)->toDateString(), 'start_time' => '19:00', 'venue' => 'Palais des Congrès',
            'status' => 'published',
        ]);

        $this->standard = TicketType::create([
            'event_id' => $this->event->id, 'name' => 'Standard', 'price' => 5000,
            'quantity' => 100, 'available_quantity' => 100, 'status' => 'active',
        ]);
        $this->vip = TicketType::create([
            'event_id' => $this->event->id, 'name' => 'VIP', 'price' => 15000,
            'quantity' => 10, 'available_quantity' => 10, 'status' => 'active',
        ]);
    }

    /** Simule l'étape 1 : les billets choisis sont gardés en session jusqu'au formulaire. */
    private function selectTickets(array $items): void
    {
        session(['checkout_items' => $items]);
    }

    private function placeOrder(array $overrides = []): Order
    {
        return app(TicketService::class)->createReservation(
            $this->event,
            array_merge(['name' => 'Kofi Mensah', 'phone' => '+22890123456', 'email' => null, 'note' => null], $overrides),
            [
                ['ticket_type_id' => $this->standard->id, 'quantity' => 2],
                ['ticket_type_id' => $this->vip->id, 'quantity' => 1],
            ],
        );
    }

    public function test_client_submits_checkout_and_order_is_recorded_without_any_payment(): void
    {
        Notification::fake();

        $this->selectTickets([['ticket_type_id' => $this->standard->id, 'quantity' => 2]]);

        Livewire::test(CheckoutForm::class, ['event' => $this->event])
            ->set('customerName', 'Kofi Mensah')
            ->set('customerPhone', '90 12 34 56')
            ->set('customerNote', 'Places côte à côte svp')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect();

        $order = Order::firstOrFail();

        $this->assertSame('pending', $order->status);
        $this->assertSame('+22890123456', $order->customer_phone);
        $this->assertNull($order->customer_email);
        $this->assertSame('Places côte à côte svp', $order->customer_note);
        $this->assertEquals(10000, $order->total_amount);
        $this->assertTrue($order->expires_at->isAfter(now()->addHours(23)));
        $this->assertSame(98, $this->standard->fresh()->available_quantity);
        $this->assertDatabaseCount('payments', 0);
    }

    public function test_checkout_rejects_invalid_phone_but_accepts_diaspora_numbers(): void
    {
        $this->selectTickets([['ticket_type_id' => $this->standard->id, 'quantity' => 1]]);

        $component = Livewire::test(CheckoutForm::class, ['event' => $this->event])
            ->set('customerName', 'Ama')
            ->set('customerPhone', '123')
            ->call('submit')
            ->assertHasErrors(['customerPhone']);

        $component->set('customerPhone', '+33 6 12 34 56 78')->call('submit')->assertHasNoErrors();

        $this->assertSame('+33612345678', Order::firstOrFail()->customer_phone);
    }

    public function test_admin_is_emailed_when_notify_address_is_configured(): void
    {
        Notification::fake();
        config(['ticketing.notify_email' => 'orga@test.tg']);

        $this->selectTickets([['ticket_type_id' => $this->standard->id, 'quantity' => 1]]);

        Livewire::test(CheckoutForm::class, ['event' => $this->event])
            ->set('customerName', 'Ama')
            ->set('customerPhone', '90123456')
            ->call('submit')
            ->assertHasNoErrors();

        Notification::assertSentOnDemand(NewOrderNotification::class);
    }

    public function test_whatsapp_message_contains_every_detail_the_admin_needs(): void
    {
        $order = $this->placeOrder(['email' => 'kofi@test.tg', 'note' => 'Places côte à côte']);

        $message = app(WhatsAppService::class)->orderMessage($order);

        foreach ([$order->reference, "Univers 2 M'boulè", 'Palais des Congrès', '2 × Standard', '1 × VIP',
                  '25 000 FCFA', 'Kofi Mensah', '+228 90 12 34 56', 'kofi@test.tg', 'Places côte à côte'] as $expected) {
            $this->assertStringContainsString($expected, $message);
        }
    }

    public function test_tracking_page_and_whatsapp_redirect_mark_the_click(): void
    {
        $order = $this->placeOrder();

        $this->get(route('order.show', $order->reference))
            ->assertOk()
            ->assertSee('Commande enregistrée')
            ->assertSee('Envoyer ma commande sur WhatsApp');

        $response = $this->get(route('order.whatsapp', $order->reference));

        $response->assertRedirectContains('https://wa.me/22890000000?text=');
        $this->assertNotNull($order->fresh()->whatsapp_clicked_at);
    }

    public function test_tracking_page_still_works_when_no_whatsapp_number_is_configured(): void
    {
        config(['services.support.whatsapp' => null]);
        $order = $this->placeOrder();

        $this->get(route('order.show', $order->reference))
            ->assertOk()
            ->assertDontSee('Envoyer ma commande sur WhatsApp')
            ->assertSee('Nous vous contactons');

        $this->get(route('order.whatsapp', $order->reference))
            ->assertRedirect(route('order.show', $order->reference));
    }

    public function test_admin_sees_order_in_list_and_can_search_it(): void
    {
        $order = $this->placeOrder();
        $this->placeOrder(['name' => 'Ama Dossou', 'phone' => '+22891999999']);

        $this->actingAs($this->admin)->get(route('admin.orders.index'))
            ->assertOk()->assertSee($order->reference)->assertSee('Kofi Mensah')->assertSee('+228 90 12 34 56');

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['q' => '90 12 34']))
            ->assertOk()->assertSee('Kofi Mensah')->assertDontSee('Ama Dossou');

        $this->actingAs($this->admin)->get(route('admin.orders.index', ['statut' => 'pending']))
            ->assertOk()->assertSee('Ama Dossou');

        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))
            ->assertOk()->assertSee('Confirmer le paiement')->assertSee('Écrire au client');

        $this->actingAs($this->admin)->get(route('admin.dashboard'))->assertOk();
    }

    public function test_admin_confirms_payment_and_tickets_are_generated(): void
    {
        Queue::fake();
        $order = $this->placeOrder(['email' => 'kofi@test.tg']);

        $this->actingAs($this->admin)
            ->post(route('admin.orders.confirm-manual', $order), ['method' => 'tmoney', 'reference' => 'TM-12345'])
            ->assertRedirect()
            ->assertSessionHas('success');

        $order->refresh();
        $this->assertSame('paid', $order->status);
        $this->assertSame(3, $order->tickets()->count());
        $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'method' => 'tmoney', 'transaction_id' => 'TM-12345', 'status' => 'successful']);
        Queue::assertPushed(\App\Jobs\SendTicketEmailJob::class);

        // Le client voit sa commande confirmée et peut ouvrir ses billets.
        $this->get(route('order.show', $order->reference))->assertOk()->assertSee('Commande confirmée');
        $this->get(route('ticket.show', $order->reference))->assertOk();

        // Le bouton WhatsApp de l'admin contient le lien des billets.
        $this->actingAs($this->admin)->get(route('admin.orders.show', $order))
            ->assertOk()->assertSee('Envoyer les billets sur WhatsApp');

        // Double confirmation impossible : pas de doublon de billets ni d'encaissement.
        $this->actingAs($this->admin)
            ->post(route('admin.orders.confirm-manual', $order), ['method' => 'cash'])
            ->assertStatus(422);
        $this->assertSame(3, $order->tickets()->count());
        $this->assertDatabaseCount('payments', 1);
    }

    public function test_confirming_an_order_without_email_sends_no_email(): void
    {
        Queue::fake();
        $order = $this->placeOrder();

        $this->actingAs($this->admin)
            ->post(route('admin.orders.confirm-manual', $order), ['method' => 'cash'])
            ->assertRedirect();

        $this->assertSame('paid', $order->fresh()->status);
        Queue::assertNothingPushed();
    }

    public function test_payment_method_must_be_one_of_the_known_ones(): void
    {
        $order = $this->placeOrder();

        $this->actingAs($this->admin)
            ->post(route('admin.orders.confirm-manual', $order), ['method' => 'bitcoin'])
            ->assertSessionHasErrors('method');

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_cancelling_releases_the_seats_once(): void
    {
        $order = $this->placeOrder();
        $this->assertSame(98, $this->standard->fresh()->available_quantity);

        $this->actingAs($this->admin)->post(route('admin.orders.cancel', $order))->assertRedirect();

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->cancelled_at);
        $this->assertSame(100, $this->standard->fresh()->available_quantity);
        $this->assertSame(10, $this->vip->fresh()->available_quantity);

        // Un second clic ne libère pas les places une deuxième fois.
        $this->actingAs($this->admin)->post(route('admin.orders.cancel', $order))->assertStatus(422);
        $this->assertSame(100, $this->standard->fresh()->available_quantity);

        $this->get(route('order.show', $order->reference))->assertOk()->assertSee('annulée');
    }

    public function test_expired_orders_release_seats_and_cannot_be_confirmed(): void
    {
        $order = $this->placeOrder();
        $order->update(['expires_at' => now()->subMinute()]);

        $this->artisan('reservations:expire')->assertSuccessful();

        $this->assertSame('expired', $order->fresh()->status);
        $this->assertSame(100, $this->standard->fresh()->available_quantity);

        $this->actingAs($this->admin)
            ->post(route('admin.orders.confirm-manual', $order), ['method' => 'cash'])
            ->assertStatus(422);

        $this->get(route('order.show', $order->reference))->assertOk()->assertSee('Le délai de réservation est écoulé');
    }

    public function test_admin_can_extend_a_reservation(): void
    {
        $order = $this->placeOrder();
        $before = $order->expires_at->copy();

        $this->actingAs($this->admin)->post(route('admin.orders.extend', $order))->assertRedirect();

        $this->assertTrue($order->fresh()->expires_at->greaterThan($before->copy()->addHours(23)));
    }

    public function test_one_phone_number_cannot_hoard_the_stock(): void
    {
        config(['ticketing.max_pending_per_phone' => 2]);

        $this->placeOrder();
        $this->placeOrder();

        $this->expectException(\RuntimeException::class);
        $this->placeOrder();
    }

    public function test_order_cannot_exceed_available_stock(): void
    {
        $this->expectException(\RuntimeException::class);

        app(TicketService::class)->createReservation(
            $this->event,
            ['name' => 'Ama', 'phone' => '+22890123456'],
            [['ticket_type_id' => $this->vip->id, 'quantity' => 11]],
        );
    }

    public function test_only_admins_can_manage_orders(): void
    {
        $order = $this->placeOrder();

        $this->post(route('admin.orders.confirm-manual', $order), ['method' => 'cash'])->assertRedirect(route('admin.login'));

        $agent = User::create(['name' => 'Agent', 'email' => 'agent@test.tg', 'password' => 'secret123', 'role' => 'agent', 'status' => 'active']);
        $this->actingAs($agent)->post(route('admin.orders.confirm-manual', $order), ['method' => 'cash'])->assertForbidden();
        $this->actingAs($agent)->get(route('admin.orders.index'))->assertForbidden();

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_phone_normalisation(): void
    {
        $wa = app(WhatsAppService::class);

        $this->assertSame('22890123456', $wa->digits('90 12 34 56'));
        $this->assertSame('22890123456', $wa->digits('+228 90 12 34 56'));
        $this->assertSame('22890123456', $wa->digits('0022890123456'));
        $this->assertSame('33612345678', $wa->digits('+33 6 12 34 56 78'));
        $this->assertSame('+228 90 12 34 56', $wa->pretty('90123456'));
        $this->assertNull($wa->digits(''));
    }
}
