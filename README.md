# Description
Bluesky API client for PHP

# Version
0.1.1

# Requirement
PHP 8.1+

# Installation
```
composer require suizumasahar01/blueky_api_client
```

# Providing functions
- Authentication
  - Get access token
- Post
  - Post message only
  - Post message with image

# Support APIs
- [com.atproto.server.createSession](https://docs.bsky.app/docs/api/com-atproto-server-create-session)
- [Posts](https://docs.bsky.app/docs/advanced-guides/posts)
  - [com.atproto.repo.createRecord](https://docs.bsky.app/docs/api/com-atproto-repo-create-record)
    - app.bsky.feed.post
    - app.bsky.embed.images
- [Upload Image files](https://docs.bsky.app/docs/advanced-guides/posts#images-embeds)
  - [com.atproto.repo.uploadBlob](https://docs.bsky.app/docs/api/com-atproto-repo-upload-blob)
   
Will support other APIs little by little.

# Usage
```
<?php
require_once __DIR__ . "/vendor/autoload.php";

use suizumasahar01\BlueskyApi\Client;

$baseUrl = 'https://bsky.social';
$client = new Client($baseUrl);

$user     = 'suizumasahar01.net';
$password = '?????';
$client->login($user, $password);

$message  = 'Post via Bluesky API';
$client->post($message);

$filePathList = [
    __DIR__ . '/sample.png',
    __DIR__ . '/sample.JPG',
];
$client->post($message, $filePathList);
```

# License
MIT
