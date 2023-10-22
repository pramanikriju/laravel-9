<?php

namespace App\Utilities\Services;

use App\Utilities\Contracts\RedisHelperInterface;
use Illuminate\Support\Facades\Cache;

class RedisStore implements RedisHelperInterface
{

    public function storeRecentMessage(mixed $id, string $messageSubject, string $toEmailAddress,  string $emailBody): void
    {
        $existingData = Cache::tags(['emails'])->get($id);
        $existingData[] = [$toEmailAddress, $messageSubject, $emailBody];
        Cache::tags(['emails'])->put($id,$existingData);
    }
}
