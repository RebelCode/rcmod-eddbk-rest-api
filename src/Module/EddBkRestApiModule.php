<?php

namespace RebelCode\EddBookings\RestApi\Module;

use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\InternalException;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\EddBookings\RestApi\Controller\BookingsController;
use RebelCode\EddBookings\RestApi\Controller\ClientsController;
use RebelCode\EddBookings\RestApi\Controller\ServicesController;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\BookingsQueryHandler;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\SingleBookingHandler;
use RebelCode\EddBookings\RestApi\Handlers\Clients\RestApiClientsHandler;
use RebelCode\EddBookings\RestApi\Resource\BookingResource;
use RebelCode\EddBookings\RestApi\Resource\ClientResource;
use RebelCode\EddBookings\RestApi\Resource\GenericCallbackResourceFactory;
use RebelCode\EddBookings\RestApi\Resource\ResourceFactoryInterface;
use RebelCode\EddBookings\RestApi\Resource\ServiceResource;
use RebelCode\Modular\Module\AbstractBaseModule;

/**
 * The REST API module class.
 *
 * @since [*next-version*]
 */
class EddBkRestApiModule extends AbstractBaseModule
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable         $key                  The module key.
     * @param string[]|Stringable[]     $dependencies         The module dependencies.
     * @param ContainerFactoryInterface $configFactory        The config factory.
     * @param ContainerFactoryInterface $containerFactory     The container factory.
     * @param ContainerFactoryInterface $compContainerFactory The composite container factory.
     * @param EventManagerInterface     $eventManager         The event manager.
     * @param EventFactoryInterface     $eventFactory         The event factory.
     */
    public function __construct(
        $key,
        $dependencies,
        $configFactory,
        $containerFactory,
        $compContainerFactory,
        $eventManager,
        $eventFactory
    ) {
        $this->_initModule($key, $dependencies, $configFactory, $containerFactory, $compContainerFactory);
        $this->_initModuleEvents($eventManager, $eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @throws InternalException If an error occurred while trying to read from the config file.
     */
    public function setup()
    {
        return $this->_setupContainer(
            $this->_loadPhpConfigFile(RCMOD_EDDBK_REST_API_CONFIG_FILE),
            [
                /*-------------------------------------------------------------*\
                 * Resource Factories                                          *
                \*-------------------------------------------------------------*/

                /*
                 * The bookings REST API resource factory.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_booking_resource_factory'          => function (ContainerInterface $c) {
                    return new GenericCallbackResourceFactory(function ($config = null) use ($c) {
                        return new BookingResource(
                            $this->_containerGet($config, ResourceFactoryInterface::K_CFG_DATA),
                            $c->get('eddbk_services_controller'),
                            $c->get('eddbk_clients_controller')
                        );
                    });
                },

                /*
                 * The services REST API resource factory.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_service_resource_factory'          => function (ContainerInterface $c) {
                    return new GenericCallbackResourceFactory(function ($config = null) {
                        return new ServiceResource(
                            $this->_containerGet($config, ResourceFactoryInterface::K_CFG_DATA)
                        );
                    });
                },

                /*
                 * The clients REST API resource factory.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_client_resource_factory'           => function (ContainerInterface $c) {
                    return new GenericCallbackResourceFactory(function ($config = null) {
                        return new ClientResource(
                            $this->_containerGet($config, ResourceFactoryInterface::K_CFG_DATA)
                        );
                    });
                },

                /*-------------------------------------------------------------*\
                 * Resource Controllers                                        *
                \*-------------------------------------------------------------*/

                /*
                 * The bookings REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_bookings_controller'               => function (ContainerInterface $c) {
                    return new BookingsController(
                        $c->get('eddbk_booking_resource_factory'),
                        $c->get('bookings_select_rm'),
                        $c->get('sql_expression_builder')
                    );
                },

                /*
                 * The services REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_services_controller'               => function (ContainerInterface $c) {
                    return new ServicesController($c->get('eddbk_service_resource_factory'));
                },

                /*
                 * The clients REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_clients_controller'                => function (ContainerInterface $c) {
                    return new ClientsController($c->get('eddbk_client_resource_factory'), EDD()->customers);
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Bookings                          *
                \*-------------------------------------------------------------*/

                /*
                 * Handles the bookings route that receives generic booking queries.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_get_bookings_handler'     => function (ContainerInterface $c) {
                    return new BookingsQueryHandler($c->get('eddbk_bookings_controller'));
                },

                /*
                 * Handles the bookings route that provides information about a single booking.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_get_booking_info_handler' => function (ContainerInterface $c) {
                    return new SingleBookingHandler($c->get('eddbk_bookings_controller'));
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Clients                           *
                \*-------------------------------------------------------------*/

                /*
                 * Handles the clients route that receives generic client queries.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_get_client_info_handler'  => function (ContainerInterface $c) {
                    return new RestApiClientsHandler($c->get('eddbk_clients_controller'));
                },

                /*-------------------------------------------------------------*\
                 * Misc. REST API services                                     *
                \*-------------------------------------------------------------*/

                /*
                 * The REST API initializer - initializes the routes and handlers when invoked.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_initializer'              => function (ContainerInterface $c) {
                    return new RestApiInitializer($c->get('eddbk_rest_api'), $c);
                },
            ]
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        if ($c === null) {
            return;
        }

        $this->_attach('rest_api_init', $c->get('eddbk_rest_api_initializer'));
    }
}
