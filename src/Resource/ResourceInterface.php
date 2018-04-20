<?php

namespace RebelCode\EddBookings\RestApi\Resource;

/**
 * Represents an API resource - something that can be provided through the API.
 *
 * @since [*next-version*]
 */
interface ResourceInterface
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
