<?php

declare(strict_types=1);

use Climactic\Altcha\Rules\Altcha;
use Illuminate\Support\Facades\Validator;

it('passes with a valid payload', function (): void {
    $v = Validator::make(
        ['token' => altchaPayload()],
        ['token' => ['required', new Altcha]],
    );

    expect($v->passes())->toBeTrue();
});

it('fails when the payload is missing', function (): void {
    $v = Validator::make(
        ['token' => ''],
        ['token' => [new Altcha]],
    );

    expect($v->fails())->toBeTrue();
});

it('fails with a malformed payload', function (): void {
    $v = Validator::make(
        ['token' => 'not-valid'],
        ['token' => [new Altcha]],
    );

    expect($v->fails())->toBeTrue();
});

it('passes when altcha is disabled', function (): void {
    config(['altcha.enabled' => false]);

    $v = Validator::make(
        ['token' => ''],
        ['token' => [new Altcha]],
    );

    expect($v->passes())->toBeTrue();
});
