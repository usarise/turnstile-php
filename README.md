# Turnstile PHP client library

[![PHP Version](https://img.shields.io/packagist/dependency-v/usarise/turnstile/php.svg?colorB=%238892BF&style=flat-square)](https://php.net)
[![Latest Version](https://img.shields.io/github/v/release/usarise/turnstile-php.svg?style=flat-square)](https://github.com/usarise/turnstile-php/releases)
[![License](https://img.shields.io/github/license/usarise/turnstile-php?style=flat-square&colorB=darkcyan)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/usarise/turnstile.svg?style=flat-square)](https://packagist.org/packages/usarise/turnstile)
[![Continuous Integration](https://github.com/usarise/turnstile-php/actions/workflows/ci.yml/badge.svg)](https://github.com/usarise/turnstile-php/actions/workflows/ci.yml)

Inspired on [recaptcha](https://github.com/google/recaptcha)

## Installation

```
composer require usarise/turnstile
```

## Getting started
#### Installation Symfony Http Client
```
composer require symfony/http-client
```
#### Installation Turnstile
```
composer require usarise/turnstile
```
#### TurnstileExample.php
```php
<?php

declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';

use Symfony\Component\HttpClient\Psr18Client;
use Turnstile\Client\Client;
use Turnstile\Error\Code;
use Turnstile\Turnstile;

// Get real API keys at https://dash.cloudflare.com/?to=/:account/turnstile
$siteKey = '1x00000000000000000000AA'; // Always passes (Dummy Testing)
$secretKey = '1x0000000000000000000000000000000AA'; // Always passes (Dummy Testing)

if ($token = $_POST['cf-turnstile-response'] ?? null) {
    $turnstile = new Turnstile(
        client: new Client(
            new Psr18Client(),
        ),
        secretKey: $secretKey,
    );

    $response = $turnstile->verify(
        $token,
        $_SERVER['REMOTE_ADDR'],
    );

    if ($response->success) {
        echo 'Success!';
    } else {
        $errors = $response->errorCodes;
        var_dump($errors);
        var_dump(Code::toDescription($errors));
    }

    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Turnstile Example</title>
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>
<body>
<form action="" method="POST">
  <!-- The following line controls and configures the Turnstile widget. -->
  <div class="cf-turnstile" data-sitekey="<?php echo $siteKey; ?>" data-theme="light"></div>
  <!-- end. -->
  <button type="submit" value="Submit">Verify</button>
</form>
</body>
</html>
```
#### Response to string
```php
var_dump((string) $response);
```
#### Response to array
```php
var_dump($response->toArray());
```
#### Response object to array
```php
var_dump($response->toArray(strict: true));
```

## Usage Turnstile
### Construct
```php
use Turnstile\Client\Client;
use Turnstile\Turnstile;

$turnstile = new Turnstile(
    client: new Client(...),
    secretKey: 'secret key',
    idempotencyKey: 'idempotency key',
);
```

## Usage Client
### Construct
```php
use Turnstile\Client\Client;
use Turnstile\TurnstileInterface;

new Client(
    client: ..., // implementation Psr\Http\Client\ClientInterface
    requestFactory: ..., // implementation Psr\Http\Message\RequestFactoryInterface (default: requestFactory = client)
    streamFactory: ..., // implementation Psr\Http\Message\StreamFactoryInterface (default: streamFactory = requestFactory)
    siteVerifyUrl: TurnstileInterface::SITE_VERIFY_URL, // https://challenges.cloudflare.com/turnstile/v0/siteverify (default)
)
```

### Examples http clients
#### Symfony Http Client
##### Installation
```
composer require symfony/http-client
```
##### Usage
```php
use Symfony\Component\HttpClient\Psr18Client;
use Turnstile\Client\Client;

new Client(
    new Psr18Client(),
)
```
#### Guzzle Http Client
##### Installation
```
composer require guzzlehttp/guzzle
```
##### Usage
```php
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use Turnstile\Client\Client;

new Client(
    new GuzzleHttpClient(),
    new HttpFactory(),
)
```
#### Symfony Http Client and Nyholm PSR-7
##### Installation Symfony Http Client
```
composer require symfony/http-client
```
##### Installation Nyholm PSR-7
```
composer require nyholm/psr7
```
##### Usage
```php
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;
use Turnstile\Client\Client;

new Client(
    new Psr18Client(),
    new Psr17Factory(),
)
```
#### Guzzle Http Client and Nyholm PSR-7
##### Installation Guzzle Http Client
```
composer require guzzlehttp/guzzle
```
##### Installation Nyholm PSR-7
```
composer require nyholm/psr7
```
##### Usage
```php
use GuzzleHttp\Client as GuzzleHttpClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Turnstile\Client\Client;

new Client(
    new GuzzleHttpClient(),
    new Psr17Factory(),
)
```

## Usage Secret Key
#### Real keys
API keys at https://dash.cloudflare.com/?to=/:account/turnstile
#### Test keys
`1x0000000000000000000000000000000AA` Always passes

`2x0000000000000000000000000000000AA` Always fails

`3x0000000000000000000000000000000AA` Yields a “token already spent” error
#### Example
```php
use Turnstile\Client\Client;
use Turnstile\Turnstile;

// Real API keys at https://dash.cloudflare.com/?to=/:account/turnstile
$secretKey = '1x0000000000000000000000000000000AA';

$turnstile = new Turnstile(
    client: new Client(...),
    secretKey: $secretKey,
);
```

## Usage Idempotency Key
#### Example with Ramsey UUID
##### Installation
```
composer require ramsey/uuid
```
##### Usage
```php
use Ramsey\Uuid\Uuid;
use Turnstile\Client\Client;
use Turnstile\Turnstile;

// The UUID to be associated with the response.
$idempotencyKey = (string) Uuid::uuid4();

$turnstile = new Turnstile(
    client: new Client(...),
    secretKey: 'secret key',
    idempotencyKey: $idempotencyKey,
);
```

## Usage Verify
#### Sample
```php
$response = $turnstile->verify(
    token: $_POST['cf-turnstile-response'], // The response provided by the Turnstile client-side render on your site.
);
```
#### Remote IP
```php
$response = $turnstile->verify(
    token: $_POST['cf-turnstile-response'],
    remoteIp: $_SERVER['REMOTE_ADDR'], // The user’s IP address.
);
```
#### Extended
```php
$response = $turnstile->verify(
    token: $_POST['cf-turnstile-response'],
    remoteIp: $_SERVER['REMOTE_ADDR'],
    challengeTimeout: 300, // Number of allowed seconds after the challenge was solved.
    expectedHostname: $_SERVER['SERVER_NAME'], // Expected hostname for which the challenge was served.
    expectedAction: 'login', // Expected customer widget identifier passed to the widget on the client side.
    expectedCData: 'sessionid-123456789', // Expected customer data passed to the widget on the client side.
);
```

## Usage Response
#### Success status
```php
$response->success
```
#### Error Codes
```php
$response->errorCodes
```
#### Challenge timestamp
```php
$response->challengeTs
```
#### Hostname
```php
$response->hostname
```
#### Action
```php
$response->action
```
#### cData
```php
$response->cdata
```
#### To string
String with raw json data
```php
(string) $response;
```
#### To array
Decoded json data
```php
$response->toArray();
```
#### Object to array
Array of processed json data based on properties of `Response` class:
`success`, `errorCodes`, `challengeTs`, `hostname`, `action`, `cdata`
```php
$response->toArray(strict: true);
```

## Usage Error to description
Convert error codes to a description in a suitable language (default english)
```php
use Turnstile\Error\{Code, Description};

var_dump(
    Code::toDescription(
        codes: $response->errorCodes,
        descriptions: Description::TEXTS, // Default
    ),
);
```