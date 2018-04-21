<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\RestApi\Resource\ResourceInterface;
use stdClass;

/**
 * An API controller - something that can work with API resources.
 *
 * @since [*next-version*]
 */
interface ControllerInterface
{
    /**
     * Retrieves resources.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The params.
     *
     * @return ResourceInterface[] The resource instances.
     */
    public function get($params = []);
}
