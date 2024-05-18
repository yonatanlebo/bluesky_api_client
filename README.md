# Description
This is a PHP package. <br>
This package is intended to help develop PHP applications using the Bluesky API.

# Requirement
PHP 8.1+

# Installation
```
composer require suizumasahar01/blueky_api_client
```

# Providing functions
- Authentication
  - login
- Post (※link, card and tag functions are supported.)
  - Message
  - Image
  - Quote post
- Search

# Usage
## Sample 1 (Post message and images with tags)
```
<?php
require_once __DIR__ . "/vendor/autoload.php";

use suizumasahar01\BlueskyApi\Client;

// Please set base url to Client class constructor.
$baseUrl = 'https://bsky.social';
$client = new Client($baseUrl);

// As a first step, please login.
$user     = 'suizumasahar01.net';
$password = '?????';
$client->login($user, $password);

$message  = 'Post this message via bluesky_api_client. https://github.com/suizumasahar01/bluesky_api_client';
$tags = ['bluesky', 'github', 'php'];
// key is used as alt.
$imageFilePaths = [
    'This is dog png file.' => __DIR__ . '/sample.png',
    'This is dog jpg file.' => __DIR__ . '/sample.JPG',
];

$client->setMessage($message);
$client->setTags($tags);
$client->setImages($imageFilePaths);
$response = $client->post();

var_dump($response);
```

## Sample 2 (Search posts -> Quote post)
```
<?php
require_once __DIR__ . "/vendor/autoload.php";

use suizumasahar01\BlueskyApi\Client;

// Please set base url to Client class constructor.
$baseUrl = 'https://bsky.social';
$client = new Client($baseUrl);

// As a first step, please login.
$user     = 'suizumasahar01.net';
$password = '?????';
$client->login($user, $password);

// Regarding query parameters and response, please check below page.
// @see https://docs.bsky.app/docs/api/app-bsky-feed-search-posts
$queryParameters = [
    'q'      => 'ぬるぽ',
    'author' => 'suizumasahar01.net'
];
$response = $client->searchPosts($queryParameters);

$uri = $response['posts'][0]['uri'];
$cid = $response['posts'][0]['cid'];

$client->setMessage('ｶﾞｯ');
$client->setQuote($uri, $cid);
$response = $client->post();

var_dump($response);
```
# Author
Masaharu Suizu <legendary_fine_horse@hotmail.com>

# License
MIT
