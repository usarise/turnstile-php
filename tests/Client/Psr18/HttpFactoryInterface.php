<?php

declare(strict_types=1);

namespace TurnstileTests\Client\Psr18;

use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\{RequestFactoryInterface, StreamFactoryInterface};

interface HttpFactoryInterface extends ClientInterface, RequestFactoryInterface, StreamFactoryInterface {}
