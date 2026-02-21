<?php

declare(strict_types=1);

namespace MaxBotApi\Exceptions;

/**
 * Thrown when a network-level error occurs (connection failure, timeout, DNS error).
 */
class NetworkException extends \RuntimeException {}
