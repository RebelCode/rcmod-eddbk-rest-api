<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use Dhii\Data\Container\ContainerSetCapableTrait;
use Dhii\Data\Container\ContainerUnsetCapableTrait;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Factory\FactoryAwareTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\Storage\Resource\InsertCapableInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\String\StringableInterface;
use RebelCode\Bookings\BookingFactoryInterface;
use RebelCode\Bookings\Factory\BookingFactoryAwareTrait;
use RebelCode\Bookings\TransitionerAwareTrait;
use RebelCode\Bookings\TransitionerInterface;

/**
 * The API controller for bookings.
 *
 * @since [*next-version*]
 */
class BookingsController extends AbstractBaseCqrsController
{
    /* @since [*next-version*] */
    use FactoryAwareTrait {
        _getFactory as _getIteratorFactory;
        _setFactory as _setIteratorFactory;
    }

    /* @since [*next-version*] */
    use BookingFactoryAwareTrait;

    /* @since [*next-version*] */
    use TransitionerAwareTrait;

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
     * @param FactoryInterface        $iteratorFactory     The iterator factory to use for the results.
     * @param BookingFactoryInterface $bookingFactory      The booking factory.
     * @param TransitionerInterface   $bookingTransitioner The booking transitioner.
     * @param SelectCapableInterface  $selectRm            The SELECT bookings resource model.
     * @param InsertCapableInterface  $insertRm            The INSERT bookings resource model.
     * @param object                  $exprBuilder         The expression builder.
     * @param ControllerInterface     $clientsController   The clients controller.
     */
    public function __construct(
        FactoryInterface $iteratorFactory,
        BookingFactoryInterface $bookingFactory,
        TransitionerInterface $bookingTransitioner,
        SelectCapableInterface $selectRm,
        InsertCapableInterface $insertRm,
        $exprBuilder,
        ControllerInterface $clientsController = null
    ) {
        $this->_setIteratorFactory($iteratorFactory);
        $this->_setBookingFactory($bookingFactory);
        $this->_setTransitioner($bookingTransitioner);
        $this->_setSelectRm($selectRm);
        $this->_setInsertRm($insertRm);
        $this->_setExprBuilder($exprBuilder);
        $this->_setClientsController($clientsController);
    }

    /**
     * {@inheritdoc}
     *
     * Overrides to attempt to transition the booking before insertion.
     *
     * @since [*next-version*]
     */
    protected function _post($params = [])
    {
        $booking = $this->_getBookingFactory()->make([
            'start'       => $this->_containerGet($params, 'start'),
            'end'         => $this->_containerGet($params, 'end'),
            'service_id'  => $this->_containerGet($params, 'start'),
            'resource_id' => $this->_containerGet($params, 'resource_id'),
            'status'      => null,
        ]);
        $booking = $this->_getTransitioner()->transition($booking, 'draft');

        return parent::_post($booking);
    }

    /**
     * Retrieves the clients controller.
     *
     * @since [*next-version*]
     *
     * @return ControllerInterface|null The clients controller instance, if any.
     */
    protected function _getClientsController()
    {
        return $this->clientsController;
    }

    /**
     * Sets the clients controller.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface|null $clientsController The controller instance, if any.
     */
    protected function _setClientsController($clientsController)
    {
        if ($clientsController !== null && !($clientsController instanceof ControllerInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a controller instance'), null, null, $clientsController
            );
        }

        $this->clientsController = $clientsController;
    }

    /**
     * {@inheritdoc}
     *
     * Extends the condition building to add query conditions for searching by clients.
     *
     * @since [*next-version*]
     */
    protected function _buildSelectCondition($params)
    {
        $condition = parent::_buildSelectCondition($params);

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
        $clients   = $this->_getClientsController()->get(['search' => $search]);
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
    protected function _getParamCqrsCompareInfo()
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
