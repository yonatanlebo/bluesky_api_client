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
  - Post message
  - Post message with image
  - Post image

# Usage
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
$client->post();
```

# Author
Masaharu Suizu <legendary_fine_horse@hotmail.com>

# License
MIT
