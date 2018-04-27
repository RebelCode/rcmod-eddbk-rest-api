<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Bookings;

use Dhii\Storage\Resource\SelectCapableInterface;
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
     * The SELECT resource model for booking status counts.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface|null
     */
    protected $statusCountsRm;

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
     * @param ControllerInterface         $controller     The booking resource controller.
     * @param SelectCapableInterface|null $statusCountsRm A SELECT resource model for booking status counts.
     * @param string[]                    $statuses       The booking statuses.
     */
    public function __construct(ControllerInterface $controller, $statusCountsRm, $statuses)
    {
        $this->_setController($controller);
        $this->_setStatusCountsRm($statusCountsRm);
        $this->statuses = $statuses;
    }

    /**
     * Sets the SELECT resource model for booking status counts.
     *
     * @since [*next-version*]
     *
     * @return SelectCapableInterface|null The SELECT booking status counts resource model instance.
     */
    protected function _getStatusCountsRm()
    {
        return $this->statusCountsRm;
    }

    /**
     * Retrieves the SELECT resource model for booking status counts.
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface|null $statusCountsRm The SELECT booking status counts resource model instance.
     */
    protected function _setStatusCountsRm($statusCountsRm)
    {
        if ($statusCountsRm !== null && !($statusCountsRm instanceof SelectCapableInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a SELECT resource model'), null, null, $statusCountsRm
            );
        }

        $this->statusCountsRm = $statusCountsRm;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function _handle(WP_REST_Request $request)
    {
        $bookings = $this->_getController()->get($request);
        $bookings = iterator_to_array($bookings);
        $count = count($bookings);

        $response = [
            'items' => $bookings,
            'count' => $count
        ];

        $statusCountsRm = $this->_getStatusCountsRm();
        if ($statusCountsRm !== null) {
            $statuses = $statusCountsRm->select();

            // Fill in with zeroes for all statuses that were not found in bookings list
            foreach ($this->statuses as $_status) {
                if (!isset($statuses[$_status])) {
                    $statuses[$_status] = 0;
                }
            }

            $response['statuses'] = $statuses;
        }

        return new WP_REST_Response($response, 200);
    }
}
