## Turnstile PHP client library

[![PHP Version](https://img.shields.io/packagist/dependency-v/usarise/turnstile/php.svg?colorB=%238892BF&style=flat-square)](https://php.net)
[![Latest Version](https://img.shields.io/github/v/release/usarise/turnstile-php.svg?style=flat-square)](https://github.com/usarise/turnstile-php/releases)
[![License](https://img.shields.io/github/license/usarise/turnstile-php?style=flat-square&colorB=darkcyan)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/usarise/turnstile.svg?style=flat-square)](https://packagist.org/packages/usarise/turnstile)
[![Continuous Integration](https://github.com/usarise/turnstile-php/actions/workflows/ci.yml/badge.svg)](https://github.com/usarise/turnstile-php/actions/workflows/ci.yml)

Inspired on [recaptcha](https://github.com/google/recaptcha)

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
$secretKey = '';

$turnstile = new Turnstile(
    client: new Client(
        new Psr18Client(),
    ),
    secretKey: $secretKey,
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
##### Response to string
```php
var_dump((string) $response);
```
##### Response to array
```php
var_dump($response->toArray());
```
##### Response object to array
```php
var_dump($response->toArray(strict: true));
```

### Usage full
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpClient\Psr18Client;
use Turnstile\Client\Client;
use Turnstile\Error\Code;
use Turnstile\Turnstile;

// API keys at https://dash.cloudflare.com/?to=/:account/turnstile
$secretKey = '';
// The UUID to be associated with the response.
$idempotencyKey = (string) Uuid::uuid4();

$turnstile = new Turnstile(
    client: new Client(
        new Psr18Client(),
    ),
    secretKey: $secretKey,
    idempotencyKey: $idempotencyKey,
    timeoutSeconds: 300,
    hostname: $_SERVER['SERVER_NAME'],
    action: 'login',
    cData: 'sessionid-123456789',
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
$secretKey = '';

$turnstile = new Turnstile(
    client: new Client(
        new GuzzleHttpClient(),
        new HttpFactory(),
    ),
    secretKey: $secretKey,
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
$secretKey = '';

$turnstile = new Turnstile(
    client: new Client(
        new GuzzleHttpClient(),
        new Psr17Factory(),
    ),
    secretKey: $secretKey,
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

### Usage Symfony/HttpClient and Nyholm/psr7
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
$secretKey = '';

$turnstile = new Turnstile(
    client: new Client(
        new Psr18Client(),
        new Psr17Factory(),
    ),
    secretKey: $secretKey,
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