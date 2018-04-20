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
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface $controller The booking resource controller.
     */
    public function __construct(ControllerInterface $controller)
    {
        $this->controller = $controller;
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

            $status = isset($request['status'])
                ? $request['status']
                : null;

            if ($status === null || strpos($status, 'all') !== false) {
                $status = null;
            } else {
                $status = array_map('trim', explode(',', $status));
            }

            $results = [];

            foreach ($bookings as $_booking) {
                $_array = $_booking->toArray();

                if ($status === null || in_array($_array['status'], $status)) {
                    $results[] = $_array;
                }
            }

            return new WP_REST_Response($results, 200);
        } catch (NotFoundExceptionInterface $notFoundException) {
            return new WP_Error('eddbk_booking_invalid_id', 'Invalid booking ID.', ['status' => 404]);
        } catch (Exception $exception) {
            return new WP_Error('eddbk_booking_error', $exception->getMessage(), ['status' => 500]);
        }
    }
}
