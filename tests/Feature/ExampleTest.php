<?php

use App\Models\User;

it('redirects guests from the root url to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login'));
});

it('redirects authenticated users from the root url to the login page', function () {
    $response = $this->actingAs(User::factory()->create())->get('/');

    $response->assertRedirect(route('login'));
});
