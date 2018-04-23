<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use Dhii\Factory\FactoryAwareTrait;
use Psr\Container\ContainerInterface;
use stdClass;
use Traversable;

/**
 * Base functionality for API controllers.
 *
 * @since [*next-version*]
 */
abstract class AbstractBaseController implements ControllerInterface
{
    /* @since [*next-version*] */
    use CreateResultsIteratorCapableFactoryTrait;

    /* @since [*next-version*] */
    use FactoryAwareTrait;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function get($params = [])
    {
        return $this->_createResultsIterator($this->_get($params));
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getResultsIteratorFactory($results)
    {
        return $this->_getFactory();
    }

    /**
     * Retrieves the results
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $params The parameters.
     *
     * @return array|Traversable A list of container elements (array, stdClass, ArrayAccess or ContainerInterface).
     */
    abstract protected function _get($params = []);
}
