<?php

namespace RebelCode\EddBookings\RestApi\Controller;

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
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Traversable;
use WP_Query;

/**
 * The API controller for services.
 *
 * @since [*next-version*]
 */
class ServicesController extends AbstractBaseCqrsController
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
     * The services SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface
     */
    protected $servicesSelectRm;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface $servicesSelectRm The services SELECT resource model.
     * @param object|null            $exprBuilder      The expression builder.
     * @param FactoryInterface       $iteratorFactory  The iterator factory to use for the results.
     */
    public function __construct(
        SelectCapableInterface $servicesSelectRm,
        $exprBuilder,
        FactoryInterface $iteratorFactory
    ) {
        $this->_setIteratorFactory($iteratorFactory);
        $this->_setExprBuilder($exprBuilder);
        $this->servicesSelectRm = $servicesSelectRm;
    }

    /**
     * Retrieves the services SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @return SelectCapableInterface The services SELECT resource model.
     */
    protected function _getServicesSelectRm()
    {
        return $this->servicesSelectRm;
    }

    /**
     * Sets the services SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface $servicesSelectRm The services SELECT resource model.
     */
    protected function _setServicesSelectRm($servicesSelectRm)
    {
        if ($servicesSelectRm !== null && !($servicesSelectRm instanceof SelectCapableInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a SELECT resource model'), null, null, $servicesSelectRm
            );
        }

        $this->servicesSelectRm = $servicesSelectRm;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function _get($params = [])
    {
        $selectRm = $this->_getServicesSelectRm();

        if ($selectRm === null) {
            throw $this->_createRuntimeException(
                $this->__('The services SELECT resource model is null'), null, null
            );
        }

        $exprBuilder = $this->_getExprBuilder();

        if ($exprBuilder === null) {
            throw $this->_createRuntimeException(
                $this->__('The SQL expression builder is null'), null, null
            );
        }

        $condition = $this->_buildSelectCondition($params);

        // The services RM is known to require AND as top-level expression
        if ($condition !== null) {
            $condition = $exprBuilder->and($condition);
        }

        $services  = $selectRm->select($condition);

        return $services;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _post($params = [])
    {
        return;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _put($params = [])
    {
        throw $this->_createControllerException($this->__('Not implemented'), 405, null, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _patch($params = [])
    {
        throw $this->_createControllerException($this->__('Not implemented'), 405, null, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _delete($params = [])
    {
        throw $this->_createControllerException($this->__('Not implemented'), 405, null, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSelectConditionParamMapping()
    {
        return [
            'id' => [
                'compare' => 'eq',
                'entity'  => 'service',
                'field'   => 'id',
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
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getUpdateParamFieldMapping()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getUpdateConditionParamMapping()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getDeleteConditionParamMapping()
    {
        return [];
    }
}
