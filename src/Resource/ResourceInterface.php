<?php

namespace RebelCode\EddBookings\RestApi\Resource;

use Dhii\Collection\MapInterface;

/**
 * Represents an API resource - something that can be provided through the API.
 *
 * @since [*next-version*]
 */
interface ResourceInterface extends MapInterface
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
