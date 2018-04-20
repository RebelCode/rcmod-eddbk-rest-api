<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use RebelCode\EddBookings\RestApi\Resource\ResourceInterface;

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
     * @param array $params The params.
     *
     * @return ResourceInterface[] The resource instances.
     */
    public function get($params = []);
}
