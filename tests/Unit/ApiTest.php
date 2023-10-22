<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);


it('needs valid user in api route', function () {
    $response = $this->postJson('/api/1/send', []);
    $response->assertStatus(404);
});

it('unsuccessful with valid user but missing api token in api route', function () {
    User::factory()->create();
    $response = $this->postJson('/api/1/send', []);
    $response->assertStatus(422);
});

it('is unsuccessful with valid user but different api token in api route', function () {
    User::factory()->create();
    $response = $this->postJson('/api/1/send?api_token=RANDOMWORDSHERE', []);
    $response->assertStatus(422);
});
//it('is successful with valid user but missing api token in api route', function () {
//    User::factory()->create();
//    $response = $this->postJson('/api/1/send', []);
//    $response->assertStatus(422);
//});
