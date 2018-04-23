<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use ArrayIterator;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use IteratorIterator;
use Psr\Container\ContainerInterface;
use stdClass;
use Traversable;

/**
 * Abstract base functionality for REST API controllers that use CQRS resource models.
 *
 * @since [*next-version*]
 */
abstract class AbstractBaseCqrsController extends AbstractBaseController implements ControllerInterface
{
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
    use CreateRuntimeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The bookings resource model.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface
     */
    protected $selectRm;

    /**
     * The expression builder.
     *
     * @since [*next-version*]
     *
     * @var object
     */
    protected $exprBuilder;

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

        return $this->_getSelectRm()->select($this->_buildCondition($params));
    }

    /**
     * Retrieves the SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @return SelectCapableInterface|null The resource model instance, if any.
     */
    protected function _getSelectRm()
    {
        return $this->selectRm;
    }

    /**
     * Sets the SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface|null $selectRm The resource model instance, if any.
     */
    protected function _setSelectRm($selectRm)
    {
        if ($selectRm === null || $selectRm instanceof SelectCapableInterface) {
            $this->selectRm = $selectRm;
        }

        throw $this->_createInvalidArgumentException(
            $this->__('Argument is not a SELECT resource model'), null, null, $selectRm
        );
    }

    /**
     * Retrieves the expression builder.
     *
     * @since [*next-version*]
     *
     * @return object|null The expression builder, if any.
     */
    protected function _getExprBuilder()
    {
        return $this->exprBuilder;
    }

    /**
     * Sets the expression builder.
     *
     * @since [*next-version*]
     *
     * @param object|null $exprBuilder The expression builder, if any.
     */
    protected function _setExprBuilder($exprBuilder)
    {
        $this->exprBuilder = $exprBuilder;
    }

    /**
     * Builds a logical expression from a set of params.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $params The input parameters.
     *
     * @return LogicalExpressionInterface|null The built condition.
     */
    protected function _buildCondition($params)
    {
        // The query condition
        $condition = null;

        foreach ($this->_getParamCqrsCompareInfo() as $_param => $_info) {
            $_compare = $this->_containerGet($_info, 'compare');
            $_entity  = $this->_containerGet($_info, 'entity');
            $_field   = $this->_containerGet($_info, 'field');
            $_value   = $this->_containerHas($params, $_param)
                ? $this->_containerGet($params, $_param)
                : null;

            $condition = $this->_addQueryCondition($condition, $_entity, $_field, $_value, $_compare);
        }

        return $condition;
    }

    /**
     * Adds a query condition to another root condition.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null  $condition The root condition.
     * @param string|Stringable                $entity    The entity name to use in the expression.
     * @param string|Stringable                $field     The name of field.
     * @param mixed                            $value     The query value.
     * @param string|Stringable|int|float|bool $compare   The comparison mode - the  expression builder method.
     *
     * @return LogicalExpressionInterface|null The amended condition.
     */
    protected function _addQueryCondition($condition, $entity, $field, $value, $compare = 'eq')
    {
        if ($value === null) {
            return $condition;
        }

        $queryCondition = $this->_createComparisonExpression($compare, $entity, $field, $value);

        // If query condition is null, return it as the query condition
        if ($condition === null) {
            return $queryCondition;
        }

        $exprBuilder = $this->_getExprBuilder();
        if ($exprBuilder === null) {
            throw $this->_createRuntimeException($this->__('The expression builder is null'));
        }

        // Otherwise, AND the existing condition with the query condition
        $condition = $this->_getExprBuilder()->and($condition, $queryCondition);

        return $condition;
    }

    /**
     * Creates a comparison expression.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable                $type   The expression type.
     * @param string|Stringable                $entity The entity name.
     * @param string|Stringable                $field  The field name.
     * @param string|Stringable|int|float|bool $value  The comparison value.
     *
     * @return LogicalExpressionInterface
     */
    protected function _createComparisonExpression($type, $entity, $field, $value)
    {
        $b = $this->_getExprBuilder();
        if ($b === null) {
            throw $this->_createRuntimeException($this->__('The expression builder is null'));
        }

        return call_user_func_array([$b, $type], [
            $b->ef($entity, $field),
            $b->lit($value),
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createArrayIterator(array $array)
    {
        return new ArrayIterator($array);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createTraversableIterator(Traversable $traversable)
    {
        return new IteratorIterator($traversable);
    }

    /**
     * Retrieves the information about the input parameters and how they map to CQRS comparison expressions.
     *
     * The information about the params is a mapping of input param names to containers as values.
     * The containers are expected to have two keys: 'compare', 'entity' and 'field'.
     * The 'compare' index should have the relational mode to use in the expression. The 'entity' and 'field' indexes
     * should map to the names of the entity field value to compare to.
     *
     * @since [*next-version*]
     *
     * @return array|Traversable
     */
    abstract protected function _getParamCqrsCompareInfo();
}
