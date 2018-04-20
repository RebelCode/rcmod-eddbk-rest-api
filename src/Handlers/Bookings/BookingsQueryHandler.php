<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Bookings;

use Dhii\Invocation\InvocableInterface;
use Exception;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Handles the REST API endpoint for querying bookings.
 *
 * @since [*next-version*]
 */
class BookingsQueryHandler implements InvocableInterface
{
    /**
     * The resource controller.
     *
     * @since [*next-version*]
     *
     * @var ControllerInterface
     */
    protected $controller;

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
        $this->controller = $controller;
        $this->statuses   = $statuses;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        /* @var $request WP_REST_Request */
        $request = func_get_arg(0);

        try {
            $bookings = $this->controller->get($request);

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
                $_array         = $_booking->toArray();
                $_bookingStatus = $_array['status'];

                // If no status filter given, OR booking matches queried status
                if ($status === null || in_array($_bookingStatus, $status)) {
                    $items[] = $_array;
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
        } catch (NotFoundExceptionInterface $notFoundException) {
            return new WP_Error('eddbk_booking_invalid_id', 'Invalid booking ID.', ['status' => 404]);
        } catch (Exception $exception) {
            return new WP_Error('eddbk_booking_error', $exception->getMessage(), ['status' => 500]);
        }
    }
}
