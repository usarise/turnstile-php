# Turnstile PHP client library

[![PHP Version](https://img.shields.io/packagist/dependency-v/usarise/turnstile/php.svg?colorB=%238892BF&style=flat-square&logo=php&logoColor=fff)](https://php.net)
[![Latest Version](https://img.shields.io/github/v/release/usarise/turnstile-php.svg?style=flat-square&logo=semver)](https://github.com/usarise/turnstile-php/releases)
[![License](https://img.shields.io/github/license/usarise/turnstile-php?style=flat-square&colorB=darkcyan&logo=unlicense&logoColor=fff)](LICENSE)
[![Total Downloads](https://img.shields.io/packagist/dt/usarise/turnstile.svg?style=flat-square&logo=packagist&logoColor=fff)](https://packagist.org/packages/usarise/turnstile)
[![GitHub CI](https://img.shields.io/github/actions/workflow/status/usarise/turnstile-php/ci.yml?style=flat-square&logo=github&label=GitHub%20CI)](https://github.com/usarise/turnstile-php/actions/workflows/ci.yml)

Inspired on [recaptcha](https://github.com/google/recaptcha)

## Table of contents
1. [Installation](#installation)
2. [Getting started](#getting-started)
3. Usage
   - [Turnstile](#usage-turnstile)
   - [Client](#usage-client)
     - [Examples http clients](#examples-http-clients)
       - [Guzzle http client](#guzzle-http-client)
       - [Symfony http client and Nyholm PSR-7](#symfony-http-client-and-nyholm-psr-7)
       - [Symfony http client and Guzzle PSR-7](#symfony-http-client-and-guzzle-psr-7)
       - [Curl http client and Nyholm PSR-7](#curl-http-client-and-nyholm-psr-7)
   - [secret key](#usage-secret-key)
   - [idempotency key](#usage-idempotency-key)
   - [verify](#usage-verify)
   - [response](#usage-response)
   - [error codes to description](#usage-error-codes-to-description)

## Installation
```
composer require usarise/turnstile
```

## Getting started
#### Installation symfony http client and nyholm psr7 and usarise turnstile
```
composer require symfony/http-client nyholm/psr7 usarise/turnstile
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
        $token, // The response provided by the Turnstile client-side render on your site.
        $_SERVER['REMOTE_ADDR'], // With usage CloudFlare: $_SERVER['HTTP_CF_CONNECTING_IP']
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
  <title>Turnstile example</title>
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

## Usage `Turnstile`
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

## Usage `Client`
### Construct
```php
use Turnstile\Client\Client;
use Turnstile\TurnstileInterface;

$client = new Client(
    client: ..., // implementation Psr\Http\Client\ClientInterface
    requestFactory: ..., // implementation Psr\Http\Message\RequestFactoryInterface (default: requestFactory = client)
    streamFactory: ..., // implementation Psr\Http\Message\StreamFactoryInterface (default: streamFactory = requestFactory)
    siteVerifyUrl: TurnstileInterface::SITE_VERIFY_URL, // https://challenges.cloudflare.com/turnstile/v0/siteverify (default)
);
```

### Examples http clients
#### Guzzle http client
##### Installation
```
composer require guzzlehttp/guzzle
```
##### Usage
```php
use GuzzleHttp\Client as GuzzleHttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use Turnstile\Client\Client;

$client = new Client(
    new GuzzleHttpClient(),
    new HttpFactory(),
);
```
#### Symfony http client and Nyholm PSR-7
##### Installation symfony http client and nyholm psr7
```
composer require symfony/http-client nyholm/psr7
```
##### Usage
```php
use Symfony\Component\HttpClient\Psr18Client;
use Turnstile\Client\Client;

$client = new Client(
    new Psr18Client(),
);
```
#### Symfony http client and Guzzle PSR-7
##### Installation symfony http client and guzzlehttp psr7
```
composer require symfony/http-client guzzlehttp/psr7
```
##### Usage
```php
use GuzzleHttp\Psr7\HttpFactory;
use Symfony\Component\HttpClient\{HttpClient, Psr18Client};
use Turnstile\Client\Client;

$client = new Client(
    new Psr18Client(
        HttpClient::create(),
        new HttpFactory(),
    ),
);
```
#### Curl http client and Nyholm PSR-7
##### Installation nyholm psr7 and php http curl client
```
composer require nyholm/psr7 php-http/curl-client
```
##### Usage
```php
use Http\Client\Curl\Client as CurlClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Turnstile\Client\Client;

$psr17Factory = new Psr17Factory();
$client = new Client(
    client: new CurlClient(
        responseFactory: $psr17Factory,
        streamFactory: $psr17Factory,
    ),
    requestFactory: $psr17Factory,
);
```

## Usage secret key
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
    client: $client,
    secretKey: $secretKey,
);
```

## Usage idempotency key
If an application requires to retry failed requests, it must utilize the idempotency functionality.

You can do so by providing a UUID as the `idempotencyKey` parameter and then use `$turnstile->verify(...)` with the same token the required number of times.

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

$turnstile = new Turnstile(
    client: $client,
    secretKey: $secretKey, // The site’s secret key.
    idempotencyKey: (string) Uuid::uuid4(), // The UUID to be associated with the response.
);

$response = $turnstile->verify(
    $token, // The response that will be associated with the UUID (idempotencyKey)
);

if ($response->success) {
    // ...
}

$response = $turnstile->verify(
    $token, // The response associated with UUID (idempotencyKey)
);

if ($response->success) {
    // ...
}
```

## Usage verify
#### Sample
```php
$response = $turnstile->verify(
    token: $_POST['cf-turnstile-response'], // The response provided by the Turnstile client-side render on your site.
);
```
#### Remote IP
The `remoteIp` parameter helps to prevent abuse by ensuring the current visitor is the one who received the token.

*This is currently not strictly validated.*

##### Basic usage
```php
$response = $turnstile->verify(
    token: $_POST['cf-turnstile-response'], // The response provided by the Turnstile client-side render on your site.
    remoteIp: $_SERVER['REMOTE_ADDR'], // The user’s IP address.
);
```
##### With usage CloudFlare
```php
$response = $turnstile->verify(
    token: $_POST['cf-turnstile-response'], // The response provided by the Turnstile client-side render on your site.
    remoteIp: $_SERVER['HTTP_CF_CONNECTING_IP'], // The user’s IP address.
);
```
#### Extended
```php
$response = $turnstile->verify(
    ...
    challengeTimeout: 300, // Number of allowed seconds after the challenge was solved.
    expectedHostname: $_SERVER['SERVER_NAME'], // Expected hostname for which the challenge was served.
    expectedAction: 'login', // Expected customer widget identifier passed to the widget on the client side.
    expectedCdata: 'sessionid-123456789', // Expected customer data passed to the widget on the client side.
);
```

## Usage response
#### Success status
```php
$response->success
```
#### Error codes
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
#### Customer data
```php
$response->cdata
```
#### To string
String with raw json data
```php
(string) $response
```
#### To array
Decoded json data
```php
$response->toArray()
```
#### Object to array
Array of processed json data based on properties of `Response` class:
`success`, `errorCodes`, `challengeTs`, `hostname`, `action`, `cdata`
```php
$response->toArray(strict: true)
```

## Usage error codes to description
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