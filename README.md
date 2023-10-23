<p align="center"><a href="https://autoklose.com" target="_blank"><img src="https://app.autoklose.com/images/svg/autoklose-logo-white.svg" width="400"></a></p>

## Evaluation project

### Steps to run
1. ```composer install``` after cloning the repository
2. ```php artisan migrate --seed``` to setup the database
3. The API endpoints are detailed below

### Some assumptions for the project
1. Only authorized users can send emails - the user in the URL has to match the token's user
2. List API uses the cache to retrieve all the emails - not using persistent storage was a choice, but the instructions weren't too clear about this
3. The list API only lists the emails for the specific user using the auth token. This can be easily modified to list all emails without any auth.

### API Endpoints
I chose to use sanctum to handle authentication tokens. To get a user's access token, you can use the following endpoint - 

``` /api/sanctum/token```

| Request Params | Expected response |
|----------------|-------------------|
| `email`        | `success`         |
| `password`     | `token`           |
| `device_name`  |                   |

![img](https://github.com/pramanikriju/laravel-9/assets/9090334/9b648abc-5f80-42df-882d-ce40105e69e5)

After getting the token, you can make requests to the following API endpoint, which dispatches a job to send emails

```api/{{user}}/send?api_token={{API_TOKEN}}```

| Request Params | Expected response |
|----------------|-------------------|
| `data`         | `success`         |

**NOTE:** The `data` parameter is an array of objects containing the email, body and subject

![img_2](https://github.com/pramanikriju/laravel-9/assets/9090334/fb2f0e06-6a49-4b5e-8235-fdad0bce824d)

After dispatching the job, the emails are stored in Elasticsearch and Redis. 

I also completed the endpoint to list all the emails sent per user.

```api/list?api_token={{API_TOKEN}}```

| Request Params | Expected response |
|----------------|-------------------|
|                | `success`         |
|                | `data`            |

**NOTE:** The `data` parameter contains an array of all sent emails. 


![img_3](https://github.com/pramanikriju/laravel-9/assets/9090334/5eb86129-24b6-4b76-89ed-95152d5a2e21)

### Tasks completed 
1. API route to get user tokens
2. API route to send emails
3. Validate incoming request 
4. Dispatch job to send emails asynchronously
5. Store the email information in Elasticsearch using the provided interface
6. Cache the email information using Redis in RedisHelperInterface 
7. Added Tests to check for the validation of API requests, dispatch of Jobs using PEST


### Bonus Tasks
1. ✅ API route for `api/list` to see all emails sent by a user
2. ✅ Unit test the `api/list` route 
3. ✅ Upgrade the project to Laravel 10 
