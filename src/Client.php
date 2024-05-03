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
     * Sends a POST request to create a record.
     *
     * @param string $message The message to be posted as a record
     *
     * @throws GuzzleException
     */
    public function post(string $message): void
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

        $responseBody = $this->request('POST', $path, $options);
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
