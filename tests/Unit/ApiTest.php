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
    $response->assertStatus(403);
});
it('is successful with valid inputs', function (){
    //Test successful job dispatch
    Queue::fake();
    $user = User::factory()->create();
    $token = $user->createToken('random_device')->plainTextToken;
    $response = $this->postJson('/api/1/send?api_token=' . $token, [
        'data' => [
            [
                'email' => 'test@example.com',
                'subject' => 'subject goes here',
                'body' => 'Body goes here'
            ]
        ]
    ]);
    $response->assertStatus(200);
    Queue::assertPushed(\App\Jobs\SendEmail::class);
});

it('is unsuccessful with invalid inputs', function () {
    $user = User::factory()->create();
    $token = $user->createToken('random_device')->plainTextToken;
    $responseInvalidEmail = $this->postJson('/api/1/send?api_token=' . $token, [
        'data' => [
            [
                'email' => 'NOTANEMAIL',
                'subject' => 'subject goes here',
                'body' => 'Body goes here'
            ]
        ]
    ]);
    $responseInvalidEmail->assertStatus(422);
    $responseMissingEmail = $this->postJson('/api/1/send?api_token=' . $token, [
        'data' => [
            [
                //'email' => 'NOTANEMAIL', //Missing email field
                'subject' => 'subject goes here',
                'body' => 'Body goes here'
            ]
        ]
    ]);
    $responseMissingEmail->assertStatus(422);
});
it('is successful with list api', function (){
    //Test successful job dispatch
    Queue::fake();
    $user = User::factory()->create();
    $token = $user->createToken('random_device')->plainTextToken;
    $response = $this->postJson('/api/1/send?api_token=' . $token, [
        'data' => [
            [
                'email' => 'test@example.com',
                'subject' => 'subject goes here',
                'body' => 'Body goes here'
            ]
        ]
    ]);
    $response->assertStatus(200);

    $listAPIresponse = $this->postJson('/api/list?api_token=' . $token);
    $data = $listAPIresponse->getOriginalContent()['data'];
    $this->assertSame('test@example.com',$data[0][0]);
    $this->assertSame('subject goes here',$data[0][1]);
    $this->assertSame('Body goes here',$data[0][2]);
});
