<?php

namespace RebelCode\EddBookings\RestApi\Resource;

use Psr\Container\ContainerInterface;

/**
 * Represents an API resource - something that can be provided through the API.
 *
 * @since [*next-version*]
 */
interface ResourceInterface extends ContainerInterface
{
    /**
     * Retrieves the resource in array form.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    public function toArray();
}
