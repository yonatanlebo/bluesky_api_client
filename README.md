# Description
Bluesky API client for PHP.

# Version
0.1.3

# Requirement
PHP 8.1+

# Installation
```
composer require suizumasahar01/blueky_api_client
```

# Providing functions
- Authentication
  - login
- Post (※Link function and card function are supported.)
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

// If you want to post image, please set image file paths as array in 2nd argument.
$filePathList = [
    __DIR__ . '/sample.png',
    __DIR__ . '/sample.JPG',
];
$client->post($message, $filePathList);
```

# Author
Masaharu Suizu <legendary_fine_horse@hotmail.com>

# License
MIT
