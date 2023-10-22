<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendEmailRequest;
use App\Jobs\SendEmail;
use App\Models\User;
use App\Utilities\Contracts\ElasticsearchHelperInterface;
use App\Utilities\Contracts\RedisHelperInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EmailController extends Controller
{
    // TODO: finish implementing send method
    public function send(SendEmailRequest $request)
    {
        //Validate the form data using FormRequest and get the validated data
        $validated = $request->validated();
        //Loop over validated array of emails to send


        /** @var ElasticsearchHelperInterface $elasticsearchHelper */
        $elasticsearchHelper = app()->make(ElasticsearchHelperInterface::class);
        // TODO: Create implementation for storeEmail and uncomment the following line
        // $elasticsearchHelper->storeEmail(...);

        /** @var RedisHelperInterface $redisHelper */
        $redisHelper = app()->make(RedisHelperInterface::class);
        // TODO: Create implementation for storeRecentMessage and uncomment the following line
        // $redisHelper->storeRecentMessage(...);

        //Return success JSON
        return response()->json([
            'success' => true,
        ]);

    }

    //  TODO - BONUS: implement list method
    public function list()
    {

    }

    public function getToken(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        return response()->json([
            'success' => true,
            'token' =>  $user->createToken($request->device_name)->plainTextToken,
        ]);

    }
}
