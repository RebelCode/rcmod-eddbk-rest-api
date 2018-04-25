<?php

namespace RebelCode\EddBookings\RestApi\Controller\Exception;

use Dhii\Exception\ThrowableInterface;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;

/**
 * An exception thrown in relation to a controller.
 *
 * @since [*next-version*]
 */
interface ControllerExceptionInterface extends ThrowableInterface
{
    /**
     * Retrieves the controller that erred.
     *
     * @since [*next-version*]
     *
     * @return ControllerInterface
     */
    public function getController();
}
