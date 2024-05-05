<?php
namespace suizumasahar01\BlueskyApi;

use DOMDocument;
use DOMXPath;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Client for Bluesky API
 */
class Client
{
    /** @var \GuzzleHttp\Client */
    private \GuzzleHttp\Client $client;
    /** @var string $did The value of $did */
    private string $did;
    /** @var string $token The token value */
    private string $token;

    /**
     * Constructor.
     *
     * @param string $baseUrl The base URL of the API
     */
    public function __construct(string $baseUrl)
    {
        $this->client = new \GuzzleHttp\Client(['base_uri' => $baseUrl]);
    }

    /**
     * Logs in a user with the given credentials.
     *
     * @param string $user     The username or identifier of the user
     * @param string $password The password of the user
     *
     * @throws GuzzleException
     */
    public function login(string $user, string $password): void
    {
        $path = '/xrpc/com.atproto.server.createSession';

        $options = [
            'json'    => [
                'identifier' => $user,
                'password'   => $password,
            ],
        ];

        $responseBody = $this->request('POST', $path, $options);

        $this->token = $responseBody['accessJwt'];
        $this->did   = $responseBody['did'];
    }

    /**
     * Sends a POST request to create a record with a message and optional images.
     *
     * @param string $message   The message for the record
     * @param array  $tagList   List of tags (optional)
     * @param array  $filePaths List of file paths for images (optional)
     *
     * @throws GuzzleException
     */
    public function post(string $message, array $tagList = [], array $filePaths = []): void
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
                'Authorization' => sprintf('Bearer %s', $this->token)
            ],
            'json'    => [
                'collection' => 'app.bsky.feed.post',
                'repo'       => $this->did,
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
            // @see https://docs.bsky.app/docs/advanced-guides/posts#images-embeds
            $images = [];
            foreach($filePaths as $alt => $filePath) {
                $response = $this->uploadImage($filePath);
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
     * Uploads an image file.
     *
     * @param string $filePath The path to the image file
     *
     * @return array The response as an associative array
     * @throws GuzzleException
     */
    private function uploadImage(string $filePath): array
    {
        $path = '/xrpc/com.atproto.repo.uploadBlob';

        if (filter_var($filePath, FILTER_VALIDATE_URL)) {
            $headers     = get_headers($filePath, true);
            $contentType = $headers['Content-Type'];
        } else {
            $contentType = mime_content_type($filePath);
        }

        $options = [
            'headers' => [
                'Content-Type'  => $contentType,
                'Authorization' => sprintf('Bearer %s', $this->token)
            ],
            'body'    => fopen($filePath, "r"),
        ];

        return $this->request('POST', $path, $options);
    }

    /**
     * Sends an HTTP request and returns the response.
     *
     * @param string $method  The HTTP method to use
     * @param string $path    The path to the resource
     * @param array  $options Additional request options
     *
     * @return array The response as an associative array
     * @throws GuzzleException
     */
    private function request(string $method, string $path, array $options): array
    {
        $response = $this->client->request($method, $path, $options);
        return json_decode($response->getBody()->getContents(), true);
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
    public function linkCard(string $url): array
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
            $response = $this->uploadImage($imageUrl);
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
