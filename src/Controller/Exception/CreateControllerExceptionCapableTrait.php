<?php

namespace RebelCode\EddBookings\RestApi\Controller\Exception;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;

/**
 * Functionality for creating controller exceptions.
 *
 * @since [*next-version*]
 */
trait CreateControllerExceptionCapableTrait
{
    /**
     * Creates a new Dhii Out Of Range exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message    The message, if any.
     * @param int|float|string|Stringable|null      $code       The numeric error code, if any.
     * @param RootException|null                    $previous   The inner exception, if any.
     * @param ControllerInterface                   $controller The controller that erred, if any.
     *
     * @return ControllerExceptionInterface The new exception.
     */
    protected function _createOutOfRangeException(
        $message = null,
        $code = null,
        RootException $previous = null,
        ControllerInterface $controller = null
    ) {
        return new ControllerException($message, $code, $previous, $controller);
    }
}
