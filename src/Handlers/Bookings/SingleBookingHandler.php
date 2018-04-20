<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Bookings;

use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Exception;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use WP_Error;
use WP_REST_Request;

/**
 * Handles the REST API endpoint for retrieving the info for a particular booking.
 *
 * @since [*next-version*]
 */
class SingleBookingHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

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
        $id      = $request['id'];

        try {
            $bookings = $this->controller->get(['id' => $id]);
            $booking  = null;

            foreach ($bookings as $_booking) {
                $booking = $_booking;
                break;
            }

            if ($booking === null) {
                throw $this->_createNotFoundException(
                    __('Booking with ID "%s" was not found', [$id]), null, null, null, $id
                );
            }

            return $booking->toArray();
        } catch (NotFoundExceptionInterface $notFoundException) {
            return new WP_Error('eddbk_booking_invalid_id', 'Invalid booking ID.', ['status' => 404]);
        } catch (Exception $exception) {
            return new WP_Error('eddbk_booking_error', $exception->getMessage(), ['status' => 500]);
        }
    }
}
