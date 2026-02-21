<?php

declare(strict_types=1);

namespace MaxBotApi\Exceptions;

/**
 * Thrown when the Max API returns an error response (HTTP 4xx or 5xx).
 */
class ApiException extends \RuntimeException {}
