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
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
            $elasticsearchHelper->storeEmail($data['body'], $data['subject'], $data['email'], $user->id);
        }



        //Return success JSON
        return response()->json([
            'success' => true,
        ]);

    }

    //  TODO - BONUS: implement list method
    public function list()
    {

    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
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
