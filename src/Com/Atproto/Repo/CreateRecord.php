<?php

namespace suizumasahar01\BlueskyApi\Com\Atproto\Repo;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Exception\GuzzleException;
use suizumasahar01\BlueskyApi\AbstractClass;

class CreateRecord extends AbstractClass
{

    /**
     * Sends a POST request to create a record with a message and optional images.
     *
     * @param string $message   The message for the record
     * @param array  $tagList   List of tags (optional)
     * @param array  $filePaths List of file paths for images (optional)
     *
     * @throws GuzzleException
     */
    public function createRecord(string $message, array $tagList = [], array $filePaths = []): void
    {
        $path = '/xrpc/com.atproto.repo.createRecord';

        // @see https://docs.bsky.app/docs/advanced-guides/posts#mentions-and-links
        $embed = [];
        list($linkFacets, $url) = $this->parseUrls($message);
        if ($url !== '') {
            $embed = $this->linkCard($url);
        }

        $hashTagFacets  = [];
        $messageWithTag = '';
        if (! empty($tagList)) {
            list($hashTagFacets, $messageWithTag) = $this->addHashTag($message, $tagList);
        }

        $options = [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', parent::$token)
            ],
            'json'    => [
                'collection' => 'app.bsky.feed.post',
                'repo'       => parent::$did,
                'record'     => [
                    '$type'     => 'app.bsky.feed.post',
                    'text'      => ($messageWithTag === '') ? $message : $messageWithTag,
                    'createdAt' => date('c'),
                ],
            ],
        ];

        $facets = array_merge($hashTagFacets, $linkFacets);
        if (! empty($facets)) {
            $options['json']['record']['facets'] = $facets;
        }

        if (! empty($embed)) {
            $options['json']['record']['embed'] = $embed;
        }

        if (! empty($filePaths)) {
            $uploadBlob = new UploadBlob();
            // @see https://docs.bsky.app/docs/advanced-guides/posts#images-embeds
            $images = [];
            foreach($filePaths as $alt => $filePath) {
                $response = $uploadBlob->uploadBlob($filePath);
                $images[] = [
                    'alt'   => $alt,
                    'image' => $response['blob'],
                ];
            }

            $options['json']['record']['embed'] = [
                '$type'  => 'app.bsky.embed.images',
                'images' => $images,
            ];
        }

        $this->request('POST', $path, $options);
    }


    /**
     * Parses URLs from the given text and returns an array of facets and card target.
     *
     * @param string $text The text from which to extract URLs
     *
     * @return array An array containing the facets and card target
     */
    private function parseUrls(string $text): array
    {
        $facets = [];
        $cardTarget = '';
        $matchedCount = preg_match_all(
            '(https?://[-_.!~*\'()a-zA-Z0-9;/?:@&=+$,%#]+)', $text, $matches);
        if ($matchedCount > 0) {
            foreach ($matches[0] as $match) {
                $startPosition = strpos($text, $match);
                $endPosition   = $startPosition + strlen($match);

                $facets[] = [
                    'index'    => [
                        'byteStart' => $startPosition,
                        'byteEnd'   => $endPosition
                    ],
                    'features' => [
                        0 => [
                            '$type' => 'app.bsky.richtext.facet#link',
                            'uri'   => $match,
                        ],
                    ],
                ];

                if ($cardTarget === '') {
                    $cardTarget = $match;
                }
            }
        }

        return [0 => $facets, 1 => $cardTarget];
    }

    /**
     * Fetches data from a URL and extracts meta information to create an embed object.
     *
     * @param string $url The URL to fetch and extract information from
     *
     * @return array The embed object as an associative array
     * @throws GuzzleException
     */
    private function linkCard(string $url): array
    {
        $dom = new DOMDocument();
        $isSuccess = @$dom->loadHTMLFile($url);
        if (! $isSuccess) {
            return [];
        }

        $xpath = new DOMXPath($dom);

        $result = $xpath->query('//meta[@property="og:title"]/@content');
        if ($result->length > 0) {
            $title = $result[0]->nodeValue;
        } else {
            $title = $dom->getElementsByTagName('title')->item(0)->nodeValue;
        }

        $description = '';
        $result = $xpath->query('//meta[@property="og:description"]/@content');
        if ($result->length > 0) {
            $description = $result[0]->nodeValue;
        }

        $thumb = [];
        $result = $xpath->query('//meta[@property="og:image"]/@content');
        if ($result->length > 0) {
            $imageUrl = $result[0]->nodeValue;
            $response = (new UploadBlob())->uploadBlob($imageUrl);
            $thumb    = $response['blob'];
        }

        $embed = [
            '$type'    => 'app.bsky.embed.external',
            'external' => [
                'uri'         => $url,
                'title'       => $title,
                'description' => $description,
                'thumb'       => $thumb,
            ],
        ];

        if (empty($thumb)) {
            unset($embed['external']['thumb']);
        }

        return $embed;
    }

    /**
     * Adds hashtags to a message and returns the facets and updated message.
     *
     * @param string $message The original message.
     * @param array  $tagList The list of tags to add.
     *
     * @return array An array containing the facets and updated message.
     */
    private function addHashTag(string $message, array $tagList): array
    {
        $facets = [];
        foreach ($tagList as $tag) {
            $startPosition = strlen($message) + 1;
            $endPosition   = $startPosition + strlen(sprintf('#%s', $tag));

            $message = sprintf('%s #%s', $message, $tag);

            $facets[] = [
                'index'    => [
                    'byteStart' => $startPosition,
                    'byteEnd'   => $endPosition
                ],
                'features' => [
                    0 => [
                        '$type' => 'app.bsky.richtext.facet#tag',
                        'tag'   => $tag,
                    ],
                ],
            ];

        }

        return [0 => $facets, 1 => $message];
    }
}