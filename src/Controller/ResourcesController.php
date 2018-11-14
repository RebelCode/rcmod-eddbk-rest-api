<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use Dhii\Factory\FactoryAwareTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\Storage\Resource\DeleteCapableInterface;
use Dhii\Storage\Resource\InsertCapableInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Storage\Resource\UpdateCapableInterface;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;

/**
 * The API controller for resources.
 *
 * @since [*next-version*]
 */
class ResourcesController extends AbstractBaseCqrsController
{
    /* @since [*next-version*] */
    use FactoryAwareTrait {
        _getFactory as _getIteratorFactory;
        _setFactory as _setIteratorFactory;
    }

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

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
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param FactoryInterface       $iteratorFactory The iterator factory to use for the results.
     * @param SelectCapableInterface $selectRm        The resources SELECT resource model.
     * @param InsertCapableInterface $insertRm        The resources INSERT resource model.
     * @param UpdateCapableInterface $updateRm        The resources UPDATE resource model.
     * @param DeleteCapableInterface $deleteRm        The resources DELETE resource model.
     * @param object                 $exprBuilder     The expression builder.
     */
    public function __construct(
        FactoryInterface $iteratorFactory,
        SelectCapableInterface $selectRm,
        InsertCapableInterface $insertRm,
        UpdateCapableInterface $updateRm,
        DeleteCapableInterface $deleteRm,
        $exprBuilder
    ) {
        $this->_setIteratorFactory($iteratorFactory);
        $this->_setSelectRm($selectRm);
        $this->_setInsertRm($insertRm);
        $this->_setUpdateRm($updateRm);
        $this->_setDeleteRm($deleteRm);
        $this->_setExprBuilder($exprBuilder);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _get($params = [])
    {
        $selectRm = $this->_getSelectRm();

        if ($selectRm === null) {
            throw $this->_createRuntimeException($this->__('The SELECT resource model is null'));
        }

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

        return $selectRm->select($this->_buildSelectCondition($params), [], $numPerPage, $offset);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _post($params = [])
    {
        $record    = $this->_buildInsertRecord($params);
        $recordIds = $this->_getInsertRm()->insert([$record]);
        $recordId  = reset($recordIds);

        return $this->_get(['id' => $recordId]);
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
        $updateRm  = $this->_getUpdateRm();
        $condition = $this->_buildUpdateCondition($params);
        $changeSet = $this->_buildUpdateChangeSet($params);

        $updateRm->update($changeSet, $condition);

        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _delete($params = [])
    {
        $deleteRm  = $this->_getDeleteRm();
        $condition = $this->_buildDeleteCondition($params);

        $deleteRm->delete($condition);

        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSelectConditionParamMapping()
    {
        return [
            'id'   => [
                'compare' => 'eq',
                'entity'  => 'resource',
                'field'   => 'id',
            ],
            'type' => [
                'compare' => 'eq',
                'entity'  => 'resource',
                'field'   => 'type',
            ],
            'name' => [
                'compare' => 'eq',
                'entity'  => 'resource',
                'field'   => 'name',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getInsertParamFieldMapping()
    {
        return [
            'type' => [
                'required' => true,
                'field'    => 'type',
            ],
            'name' => [
                'required' => true,
                'field'    => 'name',
            ],
            'data' => [
                'required'  => false,
                'field'     => 'data',
                'transform' => function ($value) {
                    return serialize($value);
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getUpdateParamFieldMapping()
    {
        return [
            'id' => [
                'type' => [
                    'field'     => 'type',
                    'transform' => function ($value) {
                        if (empty($value)) {
                            throw $this->_createInvalidArgumentException(
                                $this->__('Resource type cannot be an empty value'), null, null, $value
                            );
                        }

                        return $value;
                    },
                ],
                'name' => [
                    'field'     => 'name',
                    'transform' => function ($value) {
                        if (empty($value)) {
                            throw $this->_createInvalidArgumentException(
                                $this->__('Resource name cannot be an empty value'), null, null, $value
                            );
                        }

                        return $value;
                    },
                ],
                'data' => [
                    'field'     => 'data',
                    'transform' => function ($value) {
                        return serialize($value);
                    },
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getUpdateConditionParamMapping()
    {
        return [
            'id' => [
                'compare' => 'eq',
                'field'   => 'id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getDeleteConditionParamMapping()
    {
        return [
            'id' => [
                'compare' => 'eq',
                'field'   => 'id',
            ],
        ];
    }
}
