<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use Dhii\Storage\Resource\SelectCapableInterface;
use RebelCode\EddBookings\RestApi\Resource\BookingResource;
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

    protected function _buildCondition($params)
    {
        $condition = parent::_buildCondition($params);

        if (!$this->_containerHas($params, 'search')) {
            return $condition;
        }

        // Gather client IDs that match the search
        $search    = $this->_containerGet($params, 'search');
        $clients   = $this->clientsController->get(['search' => $search]);
        $clientIds = [];
        foreach ($clients as $_client) {
            $clientIds[] = $this->_containerGet($_client, 'id');
        }

        $condition = $this->_addQueryCondition($condition, 'booking', 'client_id', $clientIds, 'like');

        return $condition;
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
