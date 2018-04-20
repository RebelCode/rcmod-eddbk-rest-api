<?php

namespace RebelCode\EddBookings\RestApi\Resource;

use DateTime;
use DateTimeZone;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;

/**
 * Resource for bookings.
 *
 * @since [*next-version*]
 */
class BookingResource extends AbstractBaseDataStoreResource
{
    /**
     * The clients resource controller.
     *
     * @since [*next-version*]
     *
     * @var ControllerInterface
     */
    protected $clientsController;

    /**
     * The services resource controller.
     *
     * @since [*next-version*]
     *
     * @var ControllerInterface
     */
    protected $servicesController;

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface $servicesController The services resource controller.
     * @param ControllerInterface $clientsController  The clients resource controller.
     */
    public function __construct(
        $resource,
        ControllerInterface $servicesController,
        ControllerInterface $clientsController
    ) {
        parent::__construct($resource);

        $this->servicesController = $servicesController;
        $this->clientsController  = $clientsController;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function toArray()
    {
        $utc = new DateTimeZone('UTC');

        // Format start time
        $start    = $this->_get('start');
        $startDt  = new DateTime('@' . $start, $utc);
        $fmtStart = $startDt->format('Y-m-d H:i:s');

        // Format end time
        $end    = $this->_get('end');
        $endDt  = new DateTime('@' . $end, $utc);
        $fmtEnd = $endDt->format('Y-m-d H:i:s');

        // Calculate client timezone info
        $tzName   = $this->_get('client_tz', 'UTC');
        $tzObj    = new DateTimeZone($tzName);
        $tzOffset = $tzObj->getOffset($startDt);

        // Expand client
        $clientId = $this->_get('client_id');
        $clients  = $this->clientsController->get(['id' => $clientId]);
        $client   = (count($clients) === 1)
            ? reset($clients)
            : null;

        // Expand service
        $serviceId = $this->_get('service_id');
        $services  = $this->servicesController->get(['id' => $serviceId]);
        $service   = (count($services) === 1)
            ? reset($services)
            : null;

        return [
            'id'             => $this->_get('id'),
            'start'          => $fmtStart,
            'end'            => $fmtEnd,
            'status'         => $this->_get('status', 'none'),
            'service'        => $service->toArray(),
            'resource_id'    => $this->_get('resource_id'),
            'client'         => $client->toArray(),
            'clientTzName'   => $tzName,
            'clientTzOffset' => $tzOffset,
            'paymentNumber'  => $this->_get('payment_id'),
            'notes'          => $this->_get('admin_notes', ''),
        ];
    }
}
