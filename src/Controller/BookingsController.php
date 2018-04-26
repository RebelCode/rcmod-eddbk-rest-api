<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use Dhii\Data\Container\Exception\NotFoundExceptionInterface as DhiiNotFoundExceptionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Factory\FactoryAwareTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\Storage\Resource\DeleteCapableInterface;
use Dhii\Storage\Resource\InsertCapableInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Storage\Resource\UpdateCapableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\String\StringableInterface;
use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Bookings\BookingFactoryInterface;
use RebelCode\Bookings\Exception\CouldNotTransitionExceptionInterface;
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

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

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
     * @param UpdateCapableInterface  $updateRm            The UPDATE bookings resource model.
     * @param DeleteCapableInterface  $deleteRm            The DELETE bookings resource model.
     * @param object                  $exprBuilder         The expression builder.
     * @param ControllerInterface     $clientsController   The clients controller.
     */
    public function __construct(
        FactoryInterface $iteratorFactory,
        BookingFactoryInterface $bookingFactory,
        TransitionerInterface $bookingTransitioner,
        SelectCapableInterface $selectRm,
        InsertCapableInterface $insertRm,
        UpdateCapableInterface $updateRm,
        DeleteCapableInterface $deleteRm,
        $exprBuilder,
        ControllerInterface $clientsController = null
    ) {
        $this->_setIteratorFactory($iteratorFactory);
        $this->_setBookingFactory($bookingFactory);
        $this->_setTransitioner($bookingTransitioner);
        $this->_setSelectRm($selectRm);
        $this->_setInsertRm($insertRm);
        $this->_setUpdateRm($updateRm);
        $this->_setDeleteRm($deleteRm);
        $this->_setExprBuilder($exprBuilder);
        $this->_setClientsController($clientsController);
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
     * @since [*next-version*]
     */
    protected function _get($params = [])
    {
        $selectRm = $this->_getSelectRm();

        if ($selectRm === null) {
            throw $this->_createRuntimeException($this->__('The SELECT resource model is null'));
        }

        return $selectRm->select($this->_buildSelectCondition($params));
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
        $insertRm = $this->_getInsertRm();
        if ($insertRm === null) {
            throw $this->_createRuntimeException($this->__('The INSERT resource model is null'));
        }

        $booking = $this->_getBookingFactory()->make($this->_buildInsertRecord($params));
        if (empty($booking)) {
            throw $this->_createControllerException($this->__('Cannot transition empty booking'), 400, null, $this);
        }

        try {
            // Read the status as a "transition"
            $transition = $this->_containerGet($params, 'transition');
            // Attempt transition
            $booking = $this->_getTransitioner()->transition($booking, $transition);

            $ids = $insertRm->insert([$booking]);
            $id = null;
            foreach ($ids as $id) {
                break;
            }

            return $this->_get(['id' => $id]);
        } catch (CouldNotTransitionExceptionInterface $transitionEx) {
            $validationEx = $transitionEx;
            while ($validationEx !== null && !($validationEx instanceof ValidationFailedExceptionInterface)) {
                $validationEx = $validationEx->getPrevious();
            }

            $errors = ($validationEx instanceof ValidationFailedExceptionInterface)
                ? $validationEx->getValidationErrors()
                : [];

            throw $this->_createControllerException(
                $transitionEx->getMessage(), 403, $transitionEx, $this, [
                    'errors' => $this->_normalizeArray($errors)
                ]
            );
        } catch (NotFoundExceptionInterface $notFoundEx) {
            throw $this->_createControllerException(
                $this->__('Must provide an initial "transition" as either "draft" or "cart"'), 400, $notFoundEx, $this
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _put($params = [])
    {
        throw $this->_createControllerException($this->__('Cannot PUT a booking - use PATCH'), 405, null, $this);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _patch($params = [])
    {
        $updateRm = $this->_getUpdateRm();

        if ($updateRm === null) {
            throw $this->_createRuntimeException($this->__('The UPDATE resource model is null'));
        }

        $changeSet = [];

        foreach ($this->_getUpdateParamFieldMapping() as $_param => $_field) {
            if ($this->_containerHas($params, $_param)) {
                $changeSet[$_field] = $this->_containerGet($params, $_param);
            }
        }

        $updateRm->update($changeSet, $this->_buildUpdateCondition($params));

        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _delete($params = [])
    {
        $deleteRm = $this->_getDeleteRm();

        if ($deleteRm === null) {
            throw $this->_createRuntimeException($this->__('The DELETE resource model is null'));
        }

        $deleteRm->delete($this->_buildDeleteCondition($params));

        return [];
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
    protected function _getSelectConditionParamMapping()
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
            'status' => [
                'compare' => 'in',
                'entity'  => 'booking',
                'field'   => 'status'
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
            'payment'   => [
                'compare' => 'eq',
                'entity'  => 'booking',
                'field'   => 'payment_id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getUpdateConditionParamMapping()
    {
        return [
            'id' => [
                'compare' => 'eq',
                'field'   => 'id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getDeleteConditionParamMapping()
    {
        return [
            'id' => [
                'compare' => 'eq',
                'field'   => 'id',
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getInsertParamFieldMapping()
    {
        return [
            'start'    => [
                'field'    => 'start',
                'required' => true,
            ],
            'end'      => [
                'field'    => 'end',
                'required' => true,
            ],
            'service'  => [
                'field'    => 'service_id',
                'required' => true,
            ],
            'resource' => [
                'field'    => 'resource_id',
                'required' => true,
            ],
            'client'   => [
                'field'    => 'client_id',
                'required' => false,
            ],
            'clientTz' => [
                'field'    => 'client_tz',
                'required' => false,
            ],
            'payment'  => [
                'field'    => 'payment_id',
                'required' => false,
            ],
            'notes'    => [
                'field'    => 'admin_notes',
                'required' => false,
                "default"  => "",
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getUpdateParamFieldMapping()
    {
        return [
            'start'    => 'start',
            'end'      => 'end',
            'service'  => 'service_id',
            'resource' => 'resource_id',
            'client'   => 'client_id',
            'clientTz' => 'client_tz',
            'payment'  => 'payment_id',
            'notes'    => 'admin_notes',
        ];
    }
}
