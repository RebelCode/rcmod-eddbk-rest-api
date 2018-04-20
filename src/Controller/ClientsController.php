<?php

namespace RebelCode\EddBookings\RestApi\Controller;

use EDD_DB_Customers;
use RebelCode\EddBookings\RestApi\Resource\ResourceFactoryInterface;

/**
 * The API controller for clients.
 *
 * @since [*next-version*]
 */
class ClientsController implements ControllerInterface
{
    /* @since [*next-version*] */
    use CreateResourceCapableTrait;

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
        $customers = $this->eddCustomersDb->get_customers($params);
        $clients   = array_map(function ($customer) {
            return $this->_createResource($customer);
        }, $customers);

        return $clients;
    }
}
