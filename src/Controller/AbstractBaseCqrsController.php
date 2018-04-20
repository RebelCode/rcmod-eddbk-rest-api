<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\Container\ContainerInterface;
use RebelCode\EddBookings\RestApi\Resource\ResourceInterface;
use stdClass;
use Traversable;

/**
 * Abstract base functionality for REST API controllers that use CQRS resource models.
 *
 * @since [*next-version*]
 */
abstract class AbstractBaseCqrsController implements ControllerInterface
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
    public function get($params = [])
    {
        $condition = $this->_buildCondition($params);
        $selected  = $this->selectRm->select($condition);

        $results = [];
        foreach ($selected as $_idx => $_data) {
            $results[$_idx] = $this->_createResource($_data);
        }

        return $results;
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

        foreach ($this->_getParamsInfo() as $_param => $_info) {
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
     * @param LogicalExpressionInterface|null $condition The root condition.
     * @param string                          $entity    The entity name to use in the expression.
     * @param string                          $field     The name of field.
     * @param mixed                           $value     The query value.
     * @param string                          $compare   The comparison mode - the  expression builder method.
     *
     * @return LogicalExpressionInterface|null The amended condition.
     */
    protected function _addQueryCondition($condition, $entity, $field, $value, $compare = 'eq')
    {
        if ($value === null) {
            return $condition;
        }

        $b = $this->exprBuilder;

        $queryCondition = $b->$compare(
            $b->ef($entity, $field),
            $b->lit($value)
        );

        // If query condition is null, make it the query condition
        if ($condition === null) {
            $condition = $queryCondition;
        } else {
            // Otherwise, AND the existing condition with the query condition
            $condition = $this->exprBuilder->and($condition, $queryCondition);
        }

        return $condition;
    }

    /**
     * Retrieves the information about the input parameters.
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
    abstract protected function _getParamsInfo();

    /**
     * Creates a resource instance.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface $data The resource data container.
     *
     * @return ResourceInterface
     */
    abstract protected function _createResource($data);
}
