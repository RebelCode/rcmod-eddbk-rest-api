<?php

namespace RebelCode\EddBookings\RestApi\Resource;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Data\Object\DataStoreAwareContainerTrait;
use Dhii\Data\Object\GetDataCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use stdClass;

/**
 * Abstract base functionality for a resource.
 *
 * Provides construction from a container and the ability to read data and default to a value.
 *
 * @since [*next-version*]
 */
abstract class AbstractBaseResource implements ResourceInterface
{
    /* @since [*next-version*] */
    use DataStoreAwareContainerTrait;

    /* @since [*next-version*] */
    use GetDataCapableTrait;

    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $resource The resource data container.
     */
    public function __construct($resource)
    {
        $this->_setDataStore($resource);
    }

    /**
     * Retrieves data from the internal resource data container.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $key     The key of the data to retrieve.
     * @param mixed|null        $default The default value to return if no value was found for the given $key.
     *
     * @return mixed|null The value if found, the $default argument otherwise.
     */
    protected function _get($key, $default = null)
    {
        try {
            return $this->_getData($key);
        } catch (ContainerExceptionInterface $exception) {
            return $default;
        }
    }
}
