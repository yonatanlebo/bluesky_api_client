# Description
Bluesky API client for PHP.

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

// After login, you can post message from post() method.
$message  = 'Post via Bluesky API';
$client->post($message);

// (Optional) If you want to add tags, please set tags as array in 2nd argument.
$tagList = ['tag1', 'tag2', 'tag3'];
$client->post($message, $tagList);

// (Optional) If you want to post image, please set image file paths as array in 3rd argument.
// key is used as alt.
$filePaths = [
    'This is png file.' => __DIR__ . '/sample.png',
    'This is jpg file.' => __DIR__ . '/sample.JPG',
];
$client->post($message, [], $filePaths);
```

# Author
Masaharu Suizu <legendary_fine_horse@hotmail.com>

# License
MIT
