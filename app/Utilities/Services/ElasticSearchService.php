<?php

namespace App\Utilities\Services;

use App\Utilities\Contracts\ElasticsearchHelperInterface;
use Elasticsearch;

class ElasticSearchService implements ElasticsearchHelperInterface
{

    /**
     * @param string $messageBody
     * @param string $messageSubject
     * @param string $toEmailAddress
     * @param int $id
     * @return mixed
     */
    public function storeEmail(string $messageBody, string $messageSubject, string $toEmailAddress, int $id): mixed
    {
        $data = [
            'body' => [
                'toEmail' => $toEmailAddress,
                'subject' => $messageSubject,
                'body' => $messageBody
            ],
            'index' => 'emails',
            'id' => $id . '-' . now()->getTimestampMs(),
        ];
        return Elasticsearch::index($data);
    }
}
