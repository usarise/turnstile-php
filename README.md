## Turnstile PHP client library

### Installation

```
composer require usarise/turnstile
```

### Usage sample
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\HttpClient\Psr18Client;
use Turnstile\Client\Client;
use Turnstile\Error\Code;
use Turnstile\Turnstile;

// API keys at https://dash.cloudflare.com/?to=/:account/turnstile
$secret = '';

$turnstile = new Turnstile(
    client: new Client(
        new Psr18Client(),
    ),
    secret: $secret,
);

$response = $turnstile->verify(
    $_POST['cf-turnstile-response'],
    $_SERVER['REMOTE_ADDR'],
);

if ($response->success) {
    echo 'Success!';
} else {
    $errors = $response->errorCodes;
    var_dump($errors);
    var_dump(Code::toDescription($errors));
}
```

### Usage full
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\HttpClient\Psr18Client;
use Turnstile\Client\Client;
use Turnstile\Error\Code;
use Turnstile\Turnstile;

// API keys at https://dash.cloudflare.com/?to=/:account/turnstile
$secret = '';

$turnstile = new Turnstile(
    client: new Client(
        new Psr18Client(),
    ),
    secret: $secret,
    timeoutSeconds: 30,
    hostname: $_SERVER['SERVER_NAME'],
    action: 'login',
    cdata: 'sessionid-123456789',
);

$response = $turnstile->verify(
    $_POST['cf-turnstile-response'],
    $_SERVER['REMOTE_ADDR'],
);

if ($response->success) {
    echo 'Success!';
} else {
    $errors = $response->errorCodes;
    var_dump($errors);
    var_dump(Code::toDescription($errors));
}
```

### Usage Guzzle
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use Turnstile\Client\Client;
use Turnstile\Error\Code;
use Turnstile\Turnstile;

// API keys at https://dash.cloudflare.com/?to=/:account/turnstile
$secret = '';

$turnstile = new Turnstile(
    client: new Client(
        new GuzzleHttpClient(),
        new HttpFactory(),
    ),
    secret: $secret,
);

$response = $turnstile->verify(
    $_POST['cf-turnstile-response'],
    $_SERVER['REMOTE_ADDR'],
);

if ($response->success) {
    echo 'Success!';
} else {
    $errors = $response->errorCodes;
    var_dump($errors);
    var_dump(Code::toDescription($errors));
}
```

### Usage Guzzle and Nyholm/psr7
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use GuzzleHttp\Client as GuzzleHttpClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Turnstile\Client\Client;
use Turnstile\Error\Code;
use Turnstile\Turnstile;

// API keys at https://dash.cloudflare.com/?to=/:account/turnstile
$secret = '';

$turnstile = new Turnstile(
    client: new Client(
        new GuzzleHttpClient(),
        new Psr17Factory(),
    ),
    secret: $secret,
);

$response = $turnstile->verify(
    $_POST['cf-turnstile-response'],
    $_SERVER['REMOTE_ADDR'],
);

if ($response->success) {
    echo 'Success!';
} else {
    $errors = $response->errorCodes;
    var_dump($errors);
    var_dump(Code::toDescription($errors));
}
```

### Usage Symfony HttpClient and Nyholm/psr7
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;
use Turnstile\Client\Client;
use Turnstile\Error\Code;
use Turnstile\Turnstile;

// API keys at https://dash.cloudflare.com/?to=/:account/turnstile
$secret = '';

$turnstile = new Turnstile(
    client: new Client(
        new Psr18Client(),
        new Psr17Factory(),
    ),
    secret: $secret,
);

$response = $turnstile->verify(
    $_POST['cf-turnstile-response'],
    $_SERVER['REMOTE_ADDR'],
);

if ($response->success) {
    echo 'Success!';
} else {
    $errors = $response->errorCodes;
    var_dump($errors);
    var_dump(Code::toDescription($errors));
}
```