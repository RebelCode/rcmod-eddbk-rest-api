<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use Psr\Container\ContainerInterface;
use stdClass;
use Traversable;

/**
 * An API controller - something that can work with API resources.
 *
 * @since [*next-version*]
 */
interface ControllerInterface
{
    /**
     * Retrieves resources based on given parameters.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The parameters.
     *
     * @return array|Traversable A list of container elements (array, stdClass, ArrayAccess or ContainerInterface).
     */
    public function get($params = []);
}
