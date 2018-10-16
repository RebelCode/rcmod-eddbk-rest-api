<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\Factory\FactoryAwareTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Entity\EntityManagerInterface;
use stdClass;

/**
 * The API controller for services.
 *
 * @since [*next-version*]
 */
class ServicesController extends AbstractBaseController
{
    /* @since [*next-version*] */
    use FactoryAwareTrait {
        _getFactory as _getIteratorFactory;
        _setFactory as _setIteratorFactory;
    }

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The default number of items to return per page.
     *
     * @since [*next-version*]
     */
    const DEFAULT_NUM_ITEMS_PER_PAGE = 20;

    /**
     * The default page number.
     *
     * @since [*next-version*]
     */
    const DEFAULT_PAGE_NUMBER = 1;

    /**
     * The services manager.
     *
     * @since [*next-version*]
     *
     * @var EntityManagerInterface
     */
    protected $servicesManager;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param EntityManagerInterface $servicesManager The services manager.
     * @param FactoryInterface       $iteratorFactory The iterator factory to use for the results.
     */
    public function __construct(
        EntityManagerInterface $servicesManager,
        FactoryInterface $iteratorFactory
    ) {
        $this->servicesManager = $servicesManager;
        $this->_setIteratorFactory($iteratorFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function _get($params = [])
    {
        // Get number of items per page
        $numPerPage = $this->_containerGetDefault($params, 'numItems', static::DEFAULT_NUM_ITEMS_PER_PAGE);
        $numPerPage = $this->_normalizeInt($numPerPage);
        if ($numPerPage < 1) {
            throw $this->_createControllerException($this->__('Invalid number of items per page'), 400, null, $this);
        }

        // Get page number
        $pageNum = $this->_containerGetDefault($params, 'page', static::DEFAULT_PAGE_NUMBER);
        $pageNum = $this->_normalizeInt($pageNum);
        if ($pageNum < 1) {
            throw $this->_createControllerException($this->__('Invalid page number'), 400, null, $this);
        }

        // Calculate query offset
        $offset = ($pageNum - 1) * $numPerPage;

        return $this->servicesManager->query($params, $numPerPage, $offset);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _post($params = [])
    {
        try {
            $id = $this->_containerGet($params, 'id');
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createControllerException(
                $this->__('A service ID must be specified'), 400, $exception, $this
            );
        }

        $this->servicesManager->set($id, $params);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _put($params = [])
    {
        try {
            $id = $this->_containerGet($params, 'id');
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createControllerException(
                $this->__('A service ID must be specified'), 400, $exception, $this
            );
        }

        $this->servicesManager->set($id, $params);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _patch($params = [])
    {
        try {
            $id = $this->_containerGet($params, 'id');
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createControllerException(
                $this->__('A service ID must be specified'), 400, $exception, $this
            );
        }

        $this->servicesManager->update($id, $params);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _delete($params = [])
    {
        try {
            $id = $this->_containerGet($params, 'id');
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createControllerException(
                $this->__('A service ID must be specified'), 400, $exception, $this
            );
        }

        $this->servicesManager->delete($id);
    }

    /**
     * Retrieves a value from a container or data set, defaulting to a given value if not found.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable              $key       The key of the value to retrieve.
     * @param mixed                                         $default   Optional value to default to.
     *
     * @throws InvalidArgumentException    If container is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     *
     * @return mixed The value mapped to the given key, or the $default value if the key was not found.
     */
    protected function _containerGetDefault($container, $key, $default = null)
    {
        try {
            return $this->_containerGet($container, $key);
        } catch (NotFoundExceptionInterface $exception) {
            return $default;
        }
    }
}
