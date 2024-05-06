<?php

namespace suizumasahar01\BlueskyApi;

use GuzzleHttp\Exception\GuzzleException;
use suizumasahar01\BlueskyApi\Com\Atproto\Server\CreateSession;
use suizumasahar01\BlueskyApi\Com\Atproto\repo\CreateRecord;

/**
 * Client for Bluesky API
 */
class Client extends AbstractClass
{
    /**
     * Constructor.
     *
     * @param string $baseUrl The base URL of the API
     */
    public function __construct(string $baseUrl)
    {
        parent::$guzzleClient = new \GuzzleHttp\Client(['base_uri' => $baseUrl]);
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
        $responseBody  = (new CreateSession())->createSession($user, $password);

        parent::$token = $responseBody['accessJwt'];
        parent::$did   = $responseBody['did'];
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
        (new CreateRecord())->createRecord($message, $tagList, $filePaths);
    }

}
