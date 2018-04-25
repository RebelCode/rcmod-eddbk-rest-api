<?php

namespace RebelCode\EddBookings\RestApi\Controller\Exception;

use Dhii\Exception\AbstractBaseException;
use Throwable;

/**
 * An exception related to a controller.
 *
 * @since [*next-version*]
 */
class ControllerException extends AbstractBaseException implements ControllerExceptionInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __construct($message = null, $code = null, Throwable $previous = null)
    {
        $this->_initParent($message, $code, $previous);
    }
}
