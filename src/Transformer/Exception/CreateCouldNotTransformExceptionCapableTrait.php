<?php

namespace RebelCode\EddBookings\RestApi\Transformer\Exception;

use Throwable;
use Exception as RootException;
use Dhii\Util\String\StringableInterface as Stringable;
use RebelCode\EddBookings\RestApi\Transformer\TransformerInterface;

/**
 * Functionality for creating an exception related to a transformer failing to transform some source data.
 *
 * @since [*next-version*]
 */
trait CreateCouldNotTransformExceptionCapableTrait
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null       $message     The error message, if any.
     * @param int|null                     $code        The error code, if any.
     * @param RootException|Throwable|null $previous    The previous exception, if any.
     * @param TransformerInterface|null    $transformer The transformer that erred, if any.
     * @param mixed|null                   $source      The source data that could not be transformed, if any.
     *
     * @return CouldNotTransformExceptionInterface
     */
    protected function _createCouldNotTransformException(
        $message = null,
        $code = null,
        $previous = null,
        $transformer = null,
        $source = null
    ) {
        return new CouldNotTransformException($message, $code, $previous, $transformer, $source);
    }
}
