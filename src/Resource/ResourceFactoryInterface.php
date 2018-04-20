<?php

namespace RebelCode\EddBookings\RestApi\Resource;

use Dhii\Factory\FactoryInterface;

/**
 * Something that can create resource instances.
 *
 * @since [*next-version*]
 */
interface ResourceFactoryInterface extends FactoryInterface
{
    /**
     * The key for the resource data in the factory config.
     *
     * @since [*next-version*]
     */
    const K_CFG_DATA = 'data';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @return ResourceInterface
     */
    public function make($config = null);
}
