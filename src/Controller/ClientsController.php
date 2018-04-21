<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use ArrayAccess;
use ArrayIterator;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\NormalizeIteratorCapableTrait;
use EDD_DB_Customers;
use IteratorIterator;
use RebelCode\EddBookings\RestApi\Resource\ResourceFactoryInterface;
use Traversable;

/**
 * The API controller for clients.
 *
 * @since [*next-version*]
 */
class ClientsController implements ControllerInterface
{
    /* @since [*next-version*] */
    use CreateResourceCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIteratorCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The EDD Customers DB adapter.
     *
     * @since [*next-version*]
     *
     * @var EDD_DB_Customers
     */
    protected $eddCustomersDb;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ResourceFactoryInterface $resourceFactory The resource factory.
     * @param EDD_DB_Customers         $customers       The EDD customers DB adapter.
     */
    public function __construct(ResourceFactoryInterface $resourceFactory, EDD_DB_Customers $customers)
    {
        $this->resourceFactory = $resourceFactory;
        $this->eddCustomersDb  = $customers;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function get($params = [])
    {
        $queryArgs = $this->_generateEddCustomersQueryArgs($params);
        $customers = $this->eddCustomersDb->get_customers($queryArgs);
        $clients   = array_map(function ($customer) {
            return $this->_createResource($customer);
        }, $customers);

        return $this->_normalizeIterator($clients);
    }

    /**
     * Generates the EDD customers query args from the params given.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess $params The params.
     *
     * @return array
     */
    protected function _generateEddCustomersQueryArgs($params)
    {
        if (isset($params['search'])) {
            return [
                'name'           => $params['search'],
                'search_columns' => ['name', 'email'],
            ];
        }

        return [
            'id'      => isset($params['id']) ? $params['id'] : null,
            'name'    => isset($params['name']) ? $params['name'] : null,
            'email'   => isset($params['email']) ? $params['email'] : null,
            'orderby' => 'id',
            'order'   => 'ASC',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createArrayIterator(array $array)
    {
        return new ArrayIterator($array);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createTraversableIterator(Traversable $traversable)
    {
        return new IteratorIterator($traversable);
    }
}
