<?php

use App\Services\SpesoSmsService;
use Illuminate\Support\Facades\Http;

it('normalises Ghanaian phone numbers to the local format', function (string $in, string $out) {
    expect((new SpesoSmsService)->normalizePhone($in))->toBe($out);
})->with([
    ['0244 123 456', '0244123456'],
    ['+233244123456', '0244123456'],
    ['233244123456', '0244123456'],
    ['024-412-3456', '0244123456'],
]);

it('refuses to send when the API key is missing', function () {
    config(['services.speso_sms.api_key' => '']);
    Http::fake();

    $r = (new SpesoSmsService)->sendSms('0244123456', 'hi');

    expect($r['success'])->toBeFalse()->and($r['code'])->toBe('config');
    Http::assertNothingSent();
});

it('posts to Speso with a bearer token and the normalised number', function () {
    config(['services.speso_sms.api_key' => 'k', 'services.speso_sms.sender_id' => 'PRESCO', 'services.speso_sms.base_url' => 'https://speso.test/api/v1']);
    Http::fake(['speso.test/*' => Http::response(['success' => true, 'message' => 'queued'])]);

    $r = (new SpesoSmsService)->sendSms('+233244123456', 'hello');

    expect($r)->toMatchArray(['success' => true, 'message' => 'queued']);
    Http::assertSent(fn ($req) => $req->url() === 'https://speso.test/api/v1/messages/send'
        && $req->hasHeader('Authorization', 'Bearer k')
        && $req['phone'] === '0244123456' && $req['sender_id'] === 'PRESCO' && $req['message'] === 'hello');
});

it('reports gateway failures and exceptions without throwing', function () {
    config(['services.speso_sms.api_key' => 'k', 'services.speso_sms.base_url' => 'https://speso.test/api/v1']);

    Http::fake(['speso.test/*' => Http::response(['success' => false, 'message' => 'No credit'], 402)]);
    expect((new SpesoSmsService)->sendSms('0244123456', 'x'))->toMatchArray(['success' => false, 'message' => 'No credit']);

    Http::fake(['speso.test/*' => fn () => throw new RuntimeException('timeout')]);
    expect((new SpesoSmsService)->sendSms('0244123456', 'x'))->toMatchArray(['success' => false, 'code' => 'error']);
});
