<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\String\StringableInterface;
use RebelCode\EddBookings\RestApi\Resource\ResourceFactoryInterface;

/**
 * The API controller for bookings.
 *
 * @since [*next-version*]
 */
class BookingsController extends AbstractBaseCqrsController
{
    /**
     * The clients controller.
     *
     * @since [*next-version*]
     *
     * @var ControllerInterface
     */
    protected $clientsController;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ResourceFactoryInterface $resourceFactory The resource factory.
     * @param SelectCapableInterface   $selectRm        The bookings resource model.
     * @param object                   $exprBuilder     The expression builder.
     */
    public function __construct(
        ResourceFactoryInterface $resourceFactory,
        SelectCapableInterface $selectRm,
        $exprBuilder,
        ControllerInterface $clientsController
    ) {
        $this->resourceFactory   = $resourceFactory;
        $this->selectRm          = $selectRm;
        $this->exprBuilder       = $exprBuilder;
        $this->clientsController = $clientsController;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _buildCondition($params)
    {
        $condition = parent::_buildCondition($params);

        if (!$this->_containerHas($params, 'search')) {
            return $condition;
        }

        // Add condition to search by client
        $search    = $this->_containerGet($params, 'search');
        $condition = $this->_addClientsSearchCondition($condition, $search);

        return $condition;
    }

    /**
     * Adds a client search condition to an existing query condition.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $condition The condition to add to.
     * @param string|StringableInterface      $search    The client search string.
     *
     * @return LogicalExpressionInterface The new condition.
     */
    protected function _addClientsSearchCondition($condition, $search)
    {
        $clients   = $this->clientsController->get(['search' => $search]);
        $clientIds = [];

        foreach ($clients as $_client) {
            $clientIds[] = $this->_containerGet($_client, 'id');
        }

        $clientIdList = implode(',', $clientIds);

        return $this->_addQueryCondition($condition, 'booking', 'client_id', $clientIdList, 'like');
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getParamsInfo()
    {
        return [
            'id'       => [
                'compare' => 'eq',
                'entity'  => 'booking',
                'field'   => 'id',
            ],
            'start'    => [
                'compare' => 'gte',
                'entity'  => 'booking',
                'field'   => 'start',
            ],
            'end'      => [
                'compare' => 'lte',
                'entity'  => 'booking',
                'field'   => 'end',
            ],
            'service'  => [
                'compare' => 'eq',
                'entity'  => 'booking',
                'field'   => 'service_id',
            ],
            'resource' => [
                'compare' => 'eq',
                'entity'  => 'booking',
                'field'   => 'resource_id',
            ],
            'client'   => [
                'compare' => 'eq',
                'entity'  => 'booking',
                'field'   => 'client_id',
            ],
        ];
    }
}
