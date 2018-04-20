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
     * The services resource controller.
     *
     * @since [*next-version*]
     *
     * @var ControllerInterface
     */
    protected $servicesController;

    /**
     * The clients resource controller.
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
     * @param ResourceFactoryInterface $resourceFactory    The resource factory.
     * @param SelectCapableInterface   $selectRm           The bookings resource model.
     * @param object                   $exprBuilder        The expression builder.
     * @param ControllerInterface      $servicesController The services resource controller.
     * @param ControllerInterface      $clientController   The clients resource controller.
     */
    public function __construct(
        ResourceFactoryInterface $resourceFactory,
        SelectCapableInterface $selectRm,
        $exprBuilder,
        $servicesController,
        $clientController
    ) {
        $this->resourceFactory    = $resourceFactory;
        $this->selectRm           = $selectRm;
        $this->exprBuilder        = $exprBuilder;
        $this->servicesController = $servicesController;
        $this->clientsController  = $clientController;
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
