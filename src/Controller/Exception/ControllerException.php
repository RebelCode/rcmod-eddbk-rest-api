<?php

namespace RebelCode\EddBookings\RestApi\Controller\Exception;

use Dhii\Exception\AbstractBaseException;
use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use Throwable;

/**
 * An exception related to a controller.
 *
 * @since [*next-version*]
 */
class ControllerException extends AbstractBaseException implements ControllerExceptionInterface
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __construct(
        $message = null,
        $code = null,
        $previous = null,
        ControllerInterface $controller = null
    ) {
        $this->_initParent($message, $code, $previous);
        $this->_setController($controller);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getController()
    {
        return $this->_getController();
    }
}
