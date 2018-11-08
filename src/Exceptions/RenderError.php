<?php

namespace Spatie\Ssr\Exceptions;

use RuntimeException;

class RenderError extends RuntimeException
{
    public static function message(string $message) : self
    {
        return new self("RenderError: ${message}");
    }
}
