<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Bookings;

use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
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
    use CreateRuntimeExceptionCapableTrait;

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

            if (($count = count($bookings)) !== 1) {
                throw $this->_createRuntimeException(__('Found %d matching bookings', [$count]));
            }

            foreach ($bookings as $booking) {
                break;
            }

            return $booking->toArray();
        } catch (NotFoundExceptionInterface $notFoundException) {
            return new WP_Error('eddbk_booking_invalid_id', 'Invalid booking ID.', ['status' => 404]);
        } catch (Exception $exception) {
            return new WP_Error('eddbk_booking_error', $exception->getMessage(), ['status' => 500]);
        }
    }
}
