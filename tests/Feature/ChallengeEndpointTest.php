<?php

declare(strict_types=1);

it('returns a challenge when altcha is enabled', function (): void {
    $this->getJson('/altcha')
        ->assertSuccessful()
        ->assertJsonStructure(['parameters', 'signature']);
});

it('returns 404 when altcha is disabled', function (): void {
    config(['altcha.enabled' => false]);

    $this->getJson('/altcha')->assertNotFound();
});
