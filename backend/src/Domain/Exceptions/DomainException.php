<?php

namespace App\Domain\Exceptions;

use Exception;

/**
 * Base exception for all domain-level errors.
 * Prevents leakage from infrastructure/application layers.
 */
abstract class DomainException extends Exception
{
}
