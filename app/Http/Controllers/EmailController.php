<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendEmailRequest;
use App\Jobs\SendEmail;
use App\Models\User;
use App\Utilities\Contracts\ElasticsearchHelperInterface;
use App\Utilities\Contracts\RedisHelperInterface;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class EmailController extends Controller
{
    // TODO: finish implementing send method
    /**
     * @param SendEmailRequest $request
     * @param User $user
     * @return JsonResponse
     * @throws BindingResolutionException
     */
    public function send(SendEmailRequest $request, User $user)
    {
        //Validate the form data using FormRequest and get the validated data
        $validated = $request->validated();
        //Authorize the current user to be the same user sending the request
        $token = PersonalAccessToken::findToken($validated['api_token']);

        if(!empty($token) && $token->tokenable->id === $user->id)
        {
            /** @var ElasticsearchHelperInterface $elasticsearchHelper */
            $elasticsearchHelper = app()->make(ElasticsearchHelperInterface::class);

            /** @var RedisHelperInterface $redisHelper */
            $redisHelper = app()->make(RedisHelperInterface::class);

            //Loop over validated array of emails to send
            foreach ($validated['data'] as $data)
            {
                //Dispatch a job for each email to be sent
                SendEmail::dispatch($data['body'],$data['subject'], $data['email']);
                //Created implementation for storeRecentMessage helper for Redis, on a per-email basis
                $redisHelper->storeRecentMessage($user->id,$data['subject'],$data['email'], $data['body']);
                //Check for elasticsearch for test environments
                if(!empty(config('elasticsearch.connections.default.hosts.host')))
                {
                    //Created implementation for storeRecentMessage helper for Redis, on a per-email basis
                    $elasticsearchHelper->storeEmail($data['body'], $data['subject'], $data['email'], $user->id);
                }
            }
            //Return success JSON
            return response()->json([
                'success' => true,
            ]);
        }
        else {
            //Token doesn't match the user provided in the API
            return response()->json([
                'success' => false,
                'message' => 'Token error'
            ],403);
        }
    }

    //  TODO - BONUS: implement list method

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        //Validate the token is present
        $validated = $request->validate([
            'api_token' => 'required',
        ]);
        //Get the token from Sanctum helper
        $token = PersonalAccessToken::findToken($validated['api_token']);
        //Check if its a valid token
        if(!empty($token))
        {
            //Get the user from the token
            $tokenUser = $token->tokenable;
            return response()->json([
                'success' => true,
                'data' =>  Cache::tags(['emails'])->get($tokenUser->id) //Get the cached emails for the user
            ]);
        }
        //Return 403 if invalid token
        return response()->json([
            'success' => false,
        ],403);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getToken(Request $request)
    {
        //Validate login credentials
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);
        //Get the user
        $user = User::where('email', $request->email)->first();
        //Check the password is correct and user exists
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        //Return the token
        return response()->json([
            'success' => true,
            'token' =>  $user->createToken($request->device_name)->plainTextToken,
        ]);

    }
}
