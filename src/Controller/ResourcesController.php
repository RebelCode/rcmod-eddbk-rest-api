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
use Dhii\Factory\FactoryAwareTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Entity\EntityManagerInterface;
use stdClass;

/**
 * The API controller for resources.
 *
 * @since [*next-version*]
 */
class ResourcesController extends AbstractBaseController
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
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

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
     * The resources entity manager.
     *
     * @since [*next-version*]
     *
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param FactoryInterface       $iteratorFactory The iterator factory to use for the results.
     * @param EntityManagerInterface $entityManager   The resources entity manager.
     */
    public function __construct(
        FactoryInterface $iteratorFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->_setIteratorFactory($iteratorFactory);
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _get($params = [])
    {
        // Get number of items per page
        $numPerPage = $this->_containerHas($params, 'numItems')
            ? $this->_containerGet($params, 'numItems')
            : static::DEFAULT_NUM_ITEMS_PER_PAGE;
        $numPerPage = $this->_normalizeInt($numPerPage);

        // Get page number
        $pageNum = $this->_containerHas($params, 'page')
            ? $this->_containerGet($params, 'page')
            : static::DEFAULT_PAGE_NUMBER;
        $pageNum = $this->_normalizeInt($pageNum);

        if ($numPerPage < 1) {
            throw $this->_createControllerException($this->__('Invalid number of items per page'), 400, null, $this);
        }

        if ($pageNum < 1) {
            throw $this->_createControllerException($this->__('Invalid page number'), 400, null, $this);
        }

        // Calculate query offset
        $offset = ($pageNum - 1) * $numPerPage;

        // Prepare query from params
        $query = $this->_paramsToResourceData($params);

        return $this->entityManager->query($query, $numPerPage, $offset);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _post($params = [])
    {
        $resource   = $this->_paramsToResourceData($params);
        $resourceId = $this->entityManager->add($resource);

        return $this->_get(['id' => $resourceId]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _put($params = [])
    {
        throw $this->_createControllerException($this->__('Cannot PUT a resource - use PATCH'), 405, null, $this);
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
                $this->__('A resource ID must be specified'), 400, $exception, $this, $params
            );
        }

        $changeSet = $this->_paramsToResourceData($params);

        $this->entityManager->update($id, $changeSet);

        return [];
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
                $this->__('A resource ID must be specified'), 400, $exception, $this, $params
            );
        }

        $this->entityManager->delete($id);

        return [];
    }

    /**
     * Extracts and creates resource data from request params.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The request parameters.
     *
     * @return array The resulting resource data.
     */
    protected function _paramsToResourceData($params = [])
    {
        $mapping = $this->_getResourceDataParamMapping();
        $data    = [];

        foreach ($mapping as $_key => $_map) {
            try {
                $_value = $this->_containerGet($params, $_key);
            } catch (NotFoundExceptionInterface $exception) {
                continue;
            }

            // Get the optional transformation callback
            $_transform = isset($_map['transform']) ? $_map['transform'] : null;
            // Transform the value
            if ($_transform !== null) {
                $_value = call_user_func_array($_transform, [$_value]);
            }
            // Get the field name
            $_field = $_map['field'];
            // Save in data
            $data[$_field] = $_value;
        }

        return $data;
    }

    /**
     * Retrieves the data param mapping.
     *
     * @since [*next-version*]
     *
     * @return array A mapping of param keys to sub-arrays that contain a `field` (the key used by the resource manager)
     *               and an optional `transform` callback for transforming a value for this field.
     */
    protected function _getResourceDataParamMapping()
    {
        return [
            'id'           => [
                'field'     => 'id',
                'transform' => function ($id) {
                    return $this->_normalizeInt($id);
                },
            ],
            'name'         => [
                'field'     => 'name',
                'transform' => function ($name) {
                    return $this->_normalizeString($name);
                },
            ],
            'type'         => [
                'field'     => 'type',
                'transform' => function ($desc) {
                    return $this->_normalizeString($desc);
                },
            ],
            'data'         => [
                'required' => false,
                'field'    => 'data',
            ],
            'availability' => [
                'field' => 'availability',
            ],
        ];
    }
}
