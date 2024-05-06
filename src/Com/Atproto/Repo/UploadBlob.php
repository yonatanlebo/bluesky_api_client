<?php

namespace suizumasahar01\BlueskyApi\Com\Atproto\Repo;

use GuzzleHttp\Exception\GuzzleException;
use suizumasahar01\BlueskyApi\AbstractClass;

class UploadBlob extends AbstractClass
{

    /**
     * Uploads an image file.
     *
     * @param string $filePath The path to the image file
     *
     * @return array The response as an associative array
     * @throws GuzzleException
     */
    public function uploadBlob(string $filePath): array
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
                'Authorization' => sprintf('Bearer %s', parent::$token)
            ],
            'body'    => fopen($filePath, "r"),
        ];

        return $this->request('POST', $path, $options);
    }

}