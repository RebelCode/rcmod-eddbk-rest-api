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
    public function post($params = [])
    {
        return $this->_createResultsIterator($this->_post($params));
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
     * Retrieves resources based on given parameters.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The parameters.
     *
     * @return array|stdClass|Traversable The response, as a traversable list consisting of container elements: array,
     *                                    {@see stdClass}, {@see ArrayAccess} or {@see ContainerInterface}.
     */
    abstract protected function _get($params = []);

    /**
     * Creates resources based on given parameters.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The parameters.
     *
     * @return array|stdClass|Traversable|null The response, as a traversable list consisting of container elements:
     *                                         array, {@see stdClass}, {@see ArrayAccess} or {@see ContainerInterface}.
     *                                         Null may be returned to signify that the resource could not be created.
     */
    abstract protected function _post($params = []);
}
