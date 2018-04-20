<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\RestApi\Resource\ResourceFactoryInterface;
use RebelCode\EddBookings\RestApi\Resource\ResourceInterface;
use stdClass;

/**
 * Common functionality for creating resource instances.
 *
 * @since [*next-version*]
 */
trait CreateResourceCapableTrait
{
    /**
     * The resource factory.
     *
     * @since [*next-version*]
     *
     * @var ResourceFactoryInterface
     */
    protected $resourceFactory;

    /**
     * Creates a new resource instance.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface|null $config The config.
     *
     * @return ResourceInterface
     */
    protected function _createResource($config = null)
    {
        return $this->resourceFactory->make($config);
    }
}
