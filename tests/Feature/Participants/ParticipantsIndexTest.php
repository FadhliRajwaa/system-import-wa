<?php

use App\Livewire\Participants\Index;
use App\Models\Company;
use App\Models\Package;
use App\Models\Participant;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $user = User::factory()->create();
    $this->actingAs($user);
});

it('can render participants index component', function () {
    Livewire::test(Index::class)
        ->assertStatus(200);
});

it('displays participants table', function () {
    $package = Package::factory()->create(['code' => 'PKG001', 'is_active' => true]);
    $company = Company::factory()->create(['code' => 'COMP001', 'is_active' => true]);

    Participant::factory()->create([
        'package_id' => $package->id,
        'package_code' => 'PKG001',
        'company_id' => $company->id,
        'company_code' => 'COMP001',
    ]);

    Livewire::test(Index::class)
        ->assertSee('PKG001')
        ->assertSee('COMP001');
});

it('can filter by search query', function () {
    Participant::factory()->create(['name' => 'John Doe']);
    Participant::factory()->create(['name' => 'Jane Smith']);

    Livewire::test(Index::class)
        ->set('search', 'John')
        ->assertSee('John Doe')
        ->assertDontSee('Jane Smith');
});

it('can filter by exam date range', function () {
    Participant::factory()->create(['exam_date' => now()->subDays(5)]);
    Participant::factory()->create(['exam_date' => now()->addDays(5)]);

    Livewire::test(Index::class)
        ->set('filters.exam_date_start', now()->subDays(10)->format('Y-m-d'))
        ->set('filters.exam_date_end', now()->format('Y-m-d'))
        ->assertViewHas('participants', fn ($participants) => $participants->count() === 1);
});

it('can filter by wa_status', function () {
    Participant::factory()->create(['wa_status' => 'sent']);
    Participant::factory()->create(['wa_status' => 'not_sent']);

    Livewire::test(Index::class)
        ->set('filters.wa_status', 'sent')
        ->assertViewHas('participants', fn ($participants) => $participants->count() === 1);
});

it('can sort by different fields', function () {
    Participant::factory()->create(['name' => 'Alice']);
    Participant::factory()->create(['name' => 'Bob']);

    Livewire::test(Index::class)
        ->set('sortField', 'name')
        ->set('sortDirection', 'desc')
        ->call('sortBy', 'name')
        ->assertSet('sortDirection', 'asc');
});

it('can reset filters', function () {
    Livewire::test(Index::class)
        ->set('search', 'test')
        ->set('filters.package_code', 'PKG001')
        ->call('resetFilters')
        ->assertSet('search', '')
        ->assertSet('filters.package_code', null);
});

it('can select individual rows', function () {
    $participant = Participant::factory()->create();

    Livewire::test(Index::class)
        ->set('selectedRows', [(string) $participant->id])
        ->assertSet('selectedRows', [(string) $participant->id]);
});

it('can toggle select all', function () {
    Participant::factory()->count(3)->create();

    Livewire::test(Index::class)
        ->call('toggleSelectAll')
        ->assertSet('selectAll', true)
        ->assertCount('selectedRows', 3);
});

it('queues bulk wa for opt-in participants only', function () {
    $participant1 = Participant::factory()->create(['wa_opt_in_at' => now(), 'wa_status' => 'not_sent']);
    $participant2 = Participant::factory()->create(['wa_opt_in_at' => now(), 'wa_status' => 'sent']);
    $participant3 = Participant::factory()->create(['wa_opt_in_at' => null, 'wa_status' => 'not_sent']);

    config()->set('services.whatsapp.provider', 'mock');

    Livewire::test(Index::class)
        ->set('selectedRows', [(string) $participant1->id, (string) $participant2->id, (string) $participant3->id])
        ->call('queueBulkWa');

    $this->assertDatabaseHas('wa_messages', [
        'participant_id' => $participant1->id,
    ]);

    $this->assertDatabaseMissing('wa_messages', [
        'participant_id' => $participant2->id,
    ]);

    $this->assertDatabaseMissing('wa_messages', [
        'participant_id' => $participant3->id,
    ]);
});

it('does not queue wa for non-opt-in participants', function () {
    $participant = Participant::factory()->create(['wa_opt_in_at' => null, 'wa_status' => 'not_sent']);

    Livewire::test(Index::class)
        ->set('selectedRows', [(string) $participant->id])
        ->call('queueBulkWa');

    $this->assertDatabaseHas('participants', [
        'id' => $participant->id,
        'wa_status' => 'not_sent',
    ]);

    $this->assertDatabaseMissing('wa_messages', [
        'participant_id' => $participant->id,
    ]);
});

it('does not queue wa for already sent or queued participants', function () {
    $participant1 = Participant::factory()->create(['wa_opt_in_at' => now(), 'wa_status' => 'sent']);
    $participant2 = Participant::factory()->create(['wa_opt_in_at' => now(), 'wa_status' => 'queued']);

    Livewire::test(Index::class)
        ->set('selectedRows', [(string) $participant1->id, (string) $participant2->id])
        ->call('queueBulkWa');

    $this->assertDatabaseMissing('wa_messages', [
        'participant_id' => $participant1->id,
        'status' => 'queued',
    ]);
});

it('resets selected rows after queueing bulk wa', function () {
    $participant = Participant::factory()->create(['wa_opt_in_at' => now(), 'wa_status' => 'not_sent']);

    config()->set('services.whatsapp.provider', 'mock');

    Livewire::test(Index::class)
        ->set('selectedRows', [(string) $participant->id])
        ->set('selectAll', true)
        ->call('queueBulkWa')
        ->assertSet('selectedRows', [])
        ->assertSet('selectAll', false);
});
