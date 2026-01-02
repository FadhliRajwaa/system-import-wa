<?php

use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Company;
use App\Models\Participant;
use App\Models\User;
use App\Models\WaMessage;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

it('composes message with prolog, content, and footer', function () {
    $company = Company::factory()->create([
        'prolog_template' => 'Halo {name}, berikut informasi pemeriksaan Anda:',
        'footer_template' => 'Salam, {company_name}',
    ]);

    $participant = Participant::factory()->create([
        'company_id' => $company->id,
        'lab_number' => 'LAB001',
        'exam_date' => Carbon::parse('2025-01-15'),
    ]);

    $service = new WhatsAppService;
    $message = $service->composeMessage($participant);

    expect($message)->toContain('Halo '.$participant->name)
        ->toContain('No Lab: LAB001')
        ->toContain('Tgl Pemeriksaan: 15/01/2025')
        ->toContain('Salam, '.$company->name);
});

it('composes message with custom message template', function () {
    $company = Company::factory()->create([
        'prolog_template' => 'Halo {name}',
        'footer_template' => 'Terima kasih',
    ]);

    $participant = Participant::factory()->create([
        'company_id' => $company->id,
        'lab_number' => 'LAB002',
        'exam_date' => Carbon::parse('2025-01-20'),
    ]);

    $service = new WhatsAppService;
    $message = $service->composeMessage($participant, 'Silakan datang tepat waktu.');

    expect($message)->toContain('Silakan datang tepat waktu');
});

it('checks opt-in status', function () {
    $participant = Participant::factory()->create([
        'wa_opt_in_at' => now(),
    ]);

    $service = new WhatsAppService;

    expect($service->checkOptIn($participant))->toBeTrue();
});

it('checks opt-in status returns false when not opted in', function () {
    $participant = Participant::factory()->create([
        'wa_opt_in_at' => null,
    ]);

    $service = new WhatsAppService;

    expect($service->checkOptIn($participant))->toBeFalse();
});

it('can send message when all conditions met', function () {
    Queue::fake();

    $company = Company::factory()->create();
    $participant = Participant::factory()->create([
        'company_id' => $company->id,
        'wa_opt_in_at' => now(),
        'phone_e164' => '+628123456789',
    ]);

    $sender = User::factory()->create();
    $service = new WhatsAppService;

    $result = $service->canSendMessage($participant);

    expect($result)->toBeTrue();
});

it('cannot send message when not opted in', function () {
    $participant = Participant::factory()->create([
        'wa_opt_in_at' => null,
        'phone_e164' => '+628123456789',
    ]);

    $service = new WhatsAppService;

    expect($service->canSendMessage($participant))->toBeFalse();
});

it('cannot send message when phone is empty', function () {
    $participant = Participant::factory()->create([
        'wa_opt_in_at' => now(),
        'phone_e164' => null,
    ]);

    $service = new WhatsAppService;

    expect($service->canSendMessage($participant))->toBeFalse();
});

it('cannot send message when already sent', function () {
    $company = Company::factory()->create();
    $participant = Participant::factory()->create([
        'company_id' => $company->id,
        'wa_opt_in_at' => now(),
        'phone_e164' => '+628123456789',
    ]);

    WaMessage::factory()->create([
        'participant_id' => $participant->id,
        'status' => 'sent',
    ]);

    $service = new WhatsAppService;

    expect($service->canSendMessage($participant))->toBeFalse();
});

it('dispatches job when sending message', function () {
    Bus::fake([SendWhatsAppMessageJob::class]);

    $company = Company::factory()->create();
    $participant = Participant::factory()->create([
        'company_id' => $company->id,
        'phone_e164' => '+628123456789',
    ]);

    $sender = User::factory()->create();
    $service = new WhatsAppService;

    $waMessage = $service->sendMessage($participant, $sender);

    expect($waMessage)->toBeInstanceOf(WaMessage::class)
        ->and($waMessage->status)->toBe('queued')
        ->and($waMessage->to_phone_e164)->toBe('+628123456789')
        ->and($waMessage->created_by)->toBe($sender->id);

    Bus::assertDispatched(SendWhatsAppMessageJob::class, function ($job) use ($waMessage, $participant) {
        return $job->message->id === $waMessage->id
            && $job->participant->id === $participant->id;
    });
});
