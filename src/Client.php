<?php

namespace suizumasahar01\BlueskyApi;

use GuzzleHttp\Exception\GuzzleException;
use suizumasahar01\BlueskyApi\Com\Atproto\Server\CreateSession;
use suizumasahar01\BlueskyApi\Com\Atproto\Repo\CreateRecord;

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
     * Set the message to be sent.
     *
     * @param string $message The message to set
     * @return void
     */
    public function setMessage(string $message): void
    {
        parent::$message = $message;
    }

    /**
     * Sets the tags property.
     *
     * @param array $tags The array of tags to set.
     *
     * @return void
     */
    public function setTags(array $tags): void
    {
        parent::$tags = $tags;
    }

    /**
     * Sets the images property.
     *
     * @param array $imageFilePaths The array of image file paths to set.
     *
     * @return void
     */
    public function setImages(array $imageFilePaths): void
    {
        parent::$imageFilePaths = $imageFilePaths;
    }

    /**
     * Sends a POST request to create a record
     *
     * @throws GuzzleException
     */
    public function post(): void
    {
        (new CreateRecord())->createRecord(parent::$message, parent::$tags, parent::$imageFilePaths);
    }

}
