<?php

namespace RebelCode\EddBookings\RestApi\Transformer\Exception;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use RebelCode\EddBookings\RestApi\Transformer\TransformerInterface;
use Throwable;

/**
 * Functionality for creating an exception related to a transformer.
 *
 * @since [*next-version*]
 */
trait CreateTransformerExceptionCapableTrait
{
    /**
     * Creates a transformer exception instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null       $message     The error message, if any.
     * @param int|null                     $code        The error code, if any.
     * @param RootException|Throwable|null $previous    The previous exception, if any.
     * @param TransformerInterface|null    $transformer The transformer that erred, if any.
     *
     * @return TransformerExceptionInterface
     */
    protected function _createTransformerException($message = null, $code = null, $previous = null, $transformer = null)
    {
        return new TransformerException($message, $code, $previous, $transformer);
    }
}
