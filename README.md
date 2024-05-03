# Description
Bluesky API client for PHP

# Version
0.1.0

# Require
PHP 8.1+

# Support APIs
- [com.atproto.server.createSession](https://docs.bsky.app/docs/api/com-atproto-server-create-session)
- [Posts](https://docs.bsky.app/docs/advanced-guides/posts)
  - [com.atproto.repo.createRecord](https://docs.bsky.app/docs/api/com-atproto-repo-create-record)
    - app.bsky.feed.post
   
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
```

# License
MIT
