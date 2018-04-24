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
     * @return array|stdClass|Traversable The response, as a traversable list consisting of container elements: array,
     *                                    {@see stdClass}, {@see ArrayAccess} or {@see ContainerInterface}.
     */
    public function get($params = []);
}
