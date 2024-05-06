<?php

namespace suizumasahar01\BlueskyApi\Com\Atproto\Server;

use GuzzleHttp\Exception\GuzzleException;
use suizumasahar01\BlueskyApi\AbstractClass;

class CreateSession extends AbstractClass
{
    /**
     * Logs in a user with the given credentials.
     *
     * @param string $user     The username or identifier of the user
     * @param string $password The password of the user
     *
     * @throws GuzzleException
     */
    public function createSession(string $user, string $password): array
    {
        $path = '/xrpc/com.atproto.server.createSession';

        $options = [
            'json'    => [
                'identifier' => $user,
                'password'   => $password,
            ],
        ];

        return $this->request('POST', $path, $options);
    }
}