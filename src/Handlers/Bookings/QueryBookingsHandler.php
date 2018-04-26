<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Bookings;

use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Handlers\AbstractWpRestApiHandler;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Handles the REST API endpoint for querying bookings.
 *
 * @since [*next-version*]
 */
class QueryBookingsHandler extends AbstractWpRestApiHandler
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /**
     * The booking statuses.
     *
     * @since [*next-version*]
     *
     * @var string[]
     */
    protected $statuses;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface $controller The booking resource controller.
     * @param string[]            $statuses   The booking statuses.
     */
    public function __construct(ControllerInterface $controller, $statuses)
    {
        $this->_setController($controller);
        $this->statuses = $statuses;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function _handle(WP_REST_Request $request)
    {
        $bookings = $this->_getController()->get($request);

        $status = $request->get_param('status');
        // If statuses given in request
        if ($status !== null) {
            $status = array_map('trim', explode(',', $status));
        }
        // If statuses given and contains 'all', use null
        if (is_array($status) && in_array('all', $status)) {
            $status = null;
        }

        $items    = [];
        $statuses = [];
        foreach ($bookings as $_booking) {
            $_bookingStatus = $_booking['status'];

            // If no status filter given, OR booking matches queried status
            if ($status === null || in_array($_bookingStatus, $status)) {
                $items[] = $_booking;
            }

            // Increment status count
            $statuses[$_bookingStatus] = isset($statuses[$_bookingStatus])
                ? $statuses[$_bookingStatus] + 1
                : 1;
        }

        // Fill in with zeroes for all statuses that were not found in bookings list
        foreach ($this->statuses as $_status) {
            if (!isset($statuses[$_status])) {
                $statuses[$_status] = 0;
            }
        }

        $response = [
            'items'    => $items,
            'count'    => count($items),
            'statuses' => $statuses,
        ];

        return new WP_REST_Response($response, 200);
    }
}
