<?php

namespace suizumasahar01\BlueskyApi;

use GuzzleHttp\Exception\GuzzleException;
use suizumasahar01\BlueskyApi\App\Bsky\Feed\SearchPosts;
use suizumasahar01\BlueskyApi\Com\Atproto\Repo\CreateRecord;
use suizumasahar01\BlueskyApi\Com\Atproto\Server\CreateSession;

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
     *
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
     * Sets the quote properties.
     *
     * @param string $uri The URI to set.
     * @param string $cid The CID to set.
     *
     * @return void
     */
    public function setQuote(string $uri, string $cid): void
    {
        parent::$uri = $uri;
        parent::$cid = $cid;
    }

    /**
     * Sends a POST request to create a record
     *
     * @return array <br>
     * ["uri" => "xxx", "cid" => "xxx"]
     *
     * @throws GuzzleException
     */
    public function post(): array
    {
        $response = (new CreateRecord())->createRecord(parent::$message, parent::$tags, parent::$imageFilePaths);

        parent::$message = '';
        parent::$tags = [];
        parent::$imageFilePaths = [];
        parent::$uri = '';
        parent::$cid = '';

        return $response;
    }

    /**
     * Searches posts using query parameters.
     *
     * @param array $queryParameters The array of query parameters to search posts with.
     *
     * @return array The array of search results.
     *
     * @throws GuzzleException
     */
    public function searchPosts(array $queryParameters): array
    {
        return (new SearchPosts())->searchPosts($queryParameters);

    }
}
