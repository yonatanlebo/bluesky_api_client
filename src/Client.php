<?php
namespace suizumasahar01\BlueskyApi;

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
     * @param string $message      The message for the record
     * @param array  $filePathList List of file paths for images (optional)
     *
     * @throws GuzzleException
     */
    public function post(string $message, array $filePathList = []): void
    {
        $path = '/xrpc/com.atproto.repo.createRecord';

        $options = [
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $this->token)
            ],
            'json'    => [
                'collection' => 'app.bsky.feed.post',
                'repo'       => $this->did,
                'record'     => [
                    '$type'     => 'app.bsky.feed.post',
                    'text'      => $message,
                    'createdAt' => date('c'),
                ],
            ],
        ];

        if (! empty($filePathList)) {
            $images = [];
            foreach($filePathList as $filePath) {
                $response = $this->uploadImage($filePath);
                $images[] = [
                    'alt'   => 'This is image.',
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
    public function uploadImage(string $filePath): array
    {
        $path = '/xrpc/com.atproto.repo.uploadBlob';

        $options = [
            'headers' => [
                'Content-Type'  => mime_content_type($filePath),
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
}
