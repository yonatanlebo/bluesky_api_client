<?php

namespace suizumasahar01\BlueskyApi;

use GuzzleHttp\Exception\GuzzleException;

abstract class AbstractClass
{
    protected static \GuzzleHttp\Client $guzzleClient;
    /** @var string $did The value of $did */
    protected static string $did = '';
    /** @var string $token The token value */
    protected static string $token = '';
    /** @var string $message The message variable */
    protected static string $message = '';
    /** @var array $tags An array to store tags */
    protected static array $tags = [];
    /** @var array $imageFilePaths An array to store image file paths */
    protected static array $imageFilePaths = [];

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
    protected function request(string $method, string $path, array $options): array
    {
        $response = self::$guzzleClient->request($method, $path, $options);
        return json_decode($response->getBody()->getContents(), true);
    }
}