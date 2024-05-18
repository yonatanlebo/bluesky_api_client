<?php

namespace suizumasahar01\BlueskyApi\App\Bsky\Feed;

use GuzzleHttp\Exception\GuzzleException;
use suizumasahar01\BlueskyApi\AbstractClass;

class SearchPosts extends AbstractClass
{
    /**
     * Search posts based on query parameters.
     *
     * @param array $queryParameters The query parameters for searching posts.
     *
     * @return array The response from the search posts request.
     *
     * @throws GuzzleException
     */
    public function searchPosts(array $queryParameters): array
    {
        $path = '/xrpc/app.bsky.feed.searchPosts';
        $options = [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', parent::$token)
            ],
            'query' => $queryParameters
        ];

        return $this->request('GET', $path, $options);
    }
}