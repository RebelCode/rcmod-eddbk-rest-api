<?php

namespace RebelCode\EddBookings\RestApi\Module;

use ArrayIterator;
use DateTime;
use DateTimeZone;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\InternalException;
use Dhii\Factory\GenericCallbackFactory;
use Dhii\Iterator\NormalizeIteratorCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use IteratorIterator;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\EddBookings\RestApi\Auth\UserIsAdminAuthValidator;
use RebelCode\EddBookings\RestApi\Controller\BookingsController;
use RebelCode\EddBookings\RestApi\Controller\ClientsController;
use RebelCode\EddBookings\RestApi\Controller\ServicesController;
use RebelCode\EddBookings\RestApi\Controller\SessionsController;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\BookingInfoHandler;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\CreateBookingHandler;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\DeleteBookingHandler;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\QueryBookingsHandler;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\UpdateBookingHandler;
use RebelCode\EddBookings\RestApi\Handlers\Clients\ClientInfoHandler;
use RebelCode\EddBookings\RestApi\Handlers\Clients\CreateClientHandler;
use RebelCode\EddBookings\RestApi\Handlers\Clients\QueryClientsHandler;
use RebelCode\EddBookings\RestApi\Handlers\Services\QueryServicesHandler;
use RebelCode\EddBookings\RestApi\Handlers\Services\ServiceInfoHandler;
use RebelCode\EddBookings\RestApi\Handlers\Sessions\QuerySessionsHandler;
use RebelCode\Modular\Module\AbstractBaseModule;
use RebelCode\Transformers\CallbackTransformer;
use RebelCode\Transformers\MapTransformer;
use RebelCode\Transformers\NoOpTransformer;
use RebelCode\Transformers\TransformerIterator;
use Traversable;
use WP_Post;

/**
 * The REST API module class.
 *
 * @since [*next-version*]
 */
class EddBkRestApiModule extends AbstractBaseModule
{
    /* @since [*next-version*] */
    use NormalizeIteratorCapableTrait;

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
                 * Resource Controllers                                        *
                \*-------------------------------------------------------------*/

                /*
                 * The bookings REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_bookings_controller' => function (ContainerInterface $c) {
                    return new BookingsController(
                        $c->get('eddbk_rest_api_bookings_iterator_factory'),
                        $c->get('booking_factory'),
                        $c->get('booking_transitioner'),
                        $c->get('bookings_select_rm'),
                        $c->get('bookings_insert_rm'),
                        $c->get('bookings_update_rm'),
                        $c->get('bookings_delete_rm'),
                        $c->get('sql_expression_builder'),
                        $c->get('eddbk_clients_controller')
                    );
                },

                /*
                 * The services REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_services_controller' => function (ContainerInterface $c) {
                    return new ServicesController(
                        $c->get('eddbk_services_select_rm'),
                        $c->get('sql_expression_builder'),
                        $c->get('eddbk_rest_api_services_iterator_factory')
                    );
                },

                /*
                 * The clients REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_clients_controller' => function (ContainerInterface $c) {
                    return new ClientsController($c->get('eddbk_rest_api_clients_iterator_factory'), EDD()->customers);
                },

                /*
                 * The sessions REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_sessions_controller' => function (ContainerInterface $c) {
                    return new SessionsController(
                        $c->get('eddbk_rest_api_sessions_iterator_factory'),
                        $c->get('unbooked_sessions_select_rm'),
                        $c->get('eddbk_rest_api_sessions_ordering'),
                        $c->get('sql_expression_builder'),
                        $c->get('eddbk_rest_api/controllers/sessions/default_num_sessions_per_page'),
                        $c->get('eddbk_rest_api/controllers/sessions/max_num_sessions_per_page')
                    );
                },

                /**
                 * The order to use for sessions in the REST API.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_sessions_ordering' => function (ContainerInterface $c) {
                    return [
                        $c->get('sql_order_factory')->make([
                            'entity'    => 'session',
                            'field'     => 'start',
                            'ascending' => true,
                        ]),
                    ];
                },

                /*-------------------------------------------------------------*\
                 * Controller Result Iterator Factories                        *
                \*-------------------------------------------------------------*/

                /*
                 * The iterator factory for the bookings controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_bookings_iterator_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) use ($c) {
                        $items = $this->_containerGet($config, 'items');
                        $iterator = $this->_normalizeIterator($items);

                        return new TransformerIterator($iterator, $c->get('eddbk_rest_api_bookings_transformer'));
                    });
                },

                /*
                 * The iterator factory for the services controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_services_iterator_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) use ($c) {
                        $items = $this->_containerGet($config, 'items');

                        // Iterator of results, and transformer to apply to each
                        $iterator    = $this->_normalizeIterator($items);
                        $transformer = $c->get('eddbk_rest_api_services_transformer');

                        return new TransformerIterator($iterator, $transformer);
                    });
                },

                /*
                 * The iterator factory for the clients controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_clients_iterator_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) use ($c) {
                        $items = $this->_containerGet($config, 'items');
                        $iterator = $this->_normalizeIterator($items);

                        return new TransformerIterator($iterator, $c->get('eddbk_rest_api_clients_transformer'));
                    });
                },

                /*
                 * The iterator factory for the session controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_sessions_iterator_factory' => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) use ($c) {
                        $items = $this->_containerGet($config, 'items');
                        $iterator = $this->_normalizeIterator($items);

                        return new TransformerIterator($iterator, $c->get('eddbk_rest_api_sessions_transformer'));
                    });
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Bookings                          *
                \*-------------------------------------------------------------*/

                /*
                 * Handles the bookings route that receives generic booking queries.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_query_bookings_handler' => function (ContainerInterface $c) {
                    return new QueryBookingsHandler(
                        $c->get('eddbk_bookings_controller'),
                        $c->get('booking_status_select_rm'),
                        $c->get('booking_logic/statuses')
                    );
                },

                /*
                 * Handles the bookings route that provides information about a single booking.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_get_booking_info_handler' => function (ContainerInterface $c) {
                    return new BookingInfoHandler($c->get('eddbk_bookings_controller'));
                },

                /*
                 * Handles the bookings route for creating new bookings.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_create_booking_handler' => function (ContainerInterface $c) {
                    return new CreateBookingHandler($c->get('eddbk_bookings_controller'), $c->get('eddbk_rest_api'));
                },

                /*
                 * Handles the bookings route for updating bookings.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_update_booking_handler' => function (ContainerInterface $c) {
                    return new UpdateBookingHandler($c->get('eddbk_bookings_controller'));
                },

                /*
                 * Handles the bookings route for deleting bookings.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_delete_booking_handler' => function (ContainerInterface $c) {
                    return new DeleteBookingHandler($c->get('eddbk_bookings_controller'));
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Clients                           *
                \*-------------------------------------------------------------*/

                /*
                 * Handles the clients route that receives generic client queries.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_query_clients_handler' => function (ContainerInterface $c) {
                    return new QueryClientsHandler($c->get('eddbk_clients_controller'));
                },

                /*
                 * Handles the clients route that provides information about a single client.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_get_client_info_handler' => function (ContainerInterface $c) {
                    return new ClientInfoHandler($c->get('eddbk_clients_controller'));
                },

                /*
                 * Handles the clients route for creating new clients.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_create_client_handler' => function (ContainerInterface $c) {
                    return new CreateClientHandler($c->get('eddbk_clients_controller'), $c->get('eddbk_rest_api'));
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Sessions                          *
                \*-------------------------------------------------------------*/

                /*
                 * Handles the sessions route that receives generic session queries.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_query_sessions_handler' => function (ContainerInterface $c) {
                    return new QuerySessionsHandler($c->get('eddbk_sessions_controller'));
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Services                          *
                \*-------------------------------------------------------------*/

                /*
                 * Handles the services route that receives generic service queries.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_query_services_handler' => function (ContainerInterface $c) {
                    return new QueryServicesHandler($c->get('eddbk_services_controller'));
                },

                /*
                 * Handles the services route that provides information about a single service.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_get_service_info_handler' => function (ContainerInterface $c) {
                    return new ServiceInfoHandler($c->get('eddbk_services_controller'));
                },

                /*-------------------------------------------------------------*\
                 * Transformers - for data, not robots in disguise             *
                \*-------------------------------------------------------------*/

                /*
                 * A no-operation transformer.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_noop_transformer' => function (ContainerInterface $c = null) {
                    return new NoOpTransformer();
                },

                /*
                 * The transformer that transforms bookings into the result that is sent in REST API responses.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_bookings_transformer' => function (ContainerInterface $c) {
                    return new MapTransformer([
                        [
                            MapTransformer::K_SOURCE => 'id',
                        ],
                        [
                            MapTransformer::K_SOURCE      => 'start',
                            MapTransformer::K_TRANSFORMER => $c->get('eddbk_timestamp_datetime_transformer'),
                        ],
                        [
                            MapTransformer::K_SOURCE      => 'end',
                            MapTransformer::K_TRANSFORMER => $c->get('eddbk_timestamp_datetime_transformer'),
                        ],
                        [
                            MapTransformer::K_SOURCE => 'status',
                        ],
                        [
                            MapTransformer::K_SOURCE      => 'service_id',
                            MapTransformer::K_TARGET      => 'service',
                            MapTransformer::K_TRANSFORMER => $c->get('eddbk_rest_api_service_id_transformer'),
                        ],
                        [
                            MapTransformer::K_SOURCE => 'resource_id',
                            MapTransformer::K_TARGET => 'resource',
                        ],
                        [
                            MapTransformer::K_SOURCE      => 'client_id',
                            MapTransformer::K_TARGET      => 'client',
                            MapTransformer::K_TRANSFORMER => $c->get('eddbk_rest_api_client_id_transformer'),
                        ],
                        [
                            MapTransformer::K_SOURCE => 'client_tz',
                            MapTransformer::K_TARGET => 'clientTzName',
                        ],
                        [
                            MapTransformer::K_SOURCE => 'payment_id',
                            MapTransformer::K_TARGET => 'payment',
                        ],
                        [
                            MapTransformer::K_SOURCE => 'admin_notes',
                            MapTransformer::K_TARGET => 'notes',
                        ],
                    ]);
                },

                /*
                 * The transformer that transforms services into the result that is sent in REST API responses.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_services_transformer' => function (ContainerInterface $c) {
                    return $c->get('eddbk_admin_edit_services_ui_state_transformer');
                },

                /*
                 * The transformer that transforms clients into the result that is sent in REST API responses.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_clients_transformer' => function (ContainerInterface $c) {
                    return new MapTransformer([
                        [
                            MapTransformer::K_SOURCE => 'id',
                        ],
                        [
                            MapTransformer::K_SOURCE => 'name',
                        ],
                        [
                            MapTransformer::K_SOURCE => 'email',
                        ],

                    ]);
                },

                /*
                 * The transformer that transformers sessions into the result that is sent in the REST API response.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_sessions_transformer' => function (ContainerInterface $c) {
                    return new MapTransformer([
                        [
                            MapTransformer::K_SOURCE => 'id',
                        ],
                        [
                            MapTransformer::K_SOURCE      => 'start',
                            MapTransformer::K_TRANSFORMER => $c->get('eddbk_timestamp_datetime_transformer'),
                        ],
                        [
                            MapTransformer::K_SOURCE      => 'end',
                            MapTransformer::K_TRANSFORMER => $c->get('eddbk_timestamp_datetime_transformer'),
                        ],
                        [
                            MapTransformer::K_SOURCE      => 'service_id',
                            MapTransformer::K_TARGET      => 'service',
                        ],
                        [
                            MapTransformer::K_SOURCE => 'resource_id',
                            MapTransformer::K_TARGET => 'resource',
                        ],
                    ]);
                },

                /*
                 * The transformer that transforms a service ID into service data.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_service_id_transformer' => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($serviceId) use ($c) {
                        if (empty($serviceId)) {
                            return;
                        }

                        $controller = $c->get('eddbk_services_controller');
                        $services = $controller->get(['id' => $serviceId]);
                        $service = null;

                        foreach ($services as $service) {
                            break;
                        }

                        return $service;
                    });
                },

                /*
                 * The transformer that transforms a client ID into client data.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_client_id_transformer' => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($clientId) use ($c) {
                        if (empty($clientId)) {
                            return;
                        }

                        $controller = $c->get('eddbk_clients_controller');
                        $clients = $controller->get(['id' => $clientId]);
                        $client = null;

                        foreach ($clients as $client) {
                            break;
                        }

                        return $client;
                    });
                },

                /*
                 * Transformer that transforms timestamps into formatted datetime strings.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_timestamp_datetime_transformer' => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($timestamp) use ($c) {
                        // Get WordPress timezone, defaulting to UTC
                        $tzName = get_option('timezone_string');
                        $tzName = empty($tzName) ? 'UTC' : $tzName;
                        // Create date time object with the timezone
                        $timezone = new DateTimeZone($tzName);
                        $dateTime = new DateTime('@' . $timestamp, $timezone);

                        // Return formatted
                        return $dateTime->format($c->get('eddbk_rest_api/datetime_format'));
                    });
                },

                /*
                 * Transformer that transforms WP_Post instances into arrays.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_post_array_transformer' => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($post) {
                        if (empty($post)) {
                            return;
                        }

                        if (!($post instanceof WP_Post)) {
                            throw $this->_createInvalidArgumentException(
                                $this->__('Argument is not a WP_Post instance'), null, null, $post
                            );
                        }

                        return $post->to_array();
                    });
                },

                /*-------------------------------------------------------------*\
                 * REST API route handlers - Auth Validators                   *
                \*-------------------------------------------------------------*/

                /*
                 * The authorization validator that only authorizes administrator users.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_user_is_admin_auth_validator' => function (ContainerInterface $c) {
                    return new UserIsAdminAuthValidator();
                },

                /*-------------------------------------------------------------*\
                 * Misc. REST API services                                     *
                \*-------------------------------------------------------------*/

                /*
                 * The REST API initializer - initializes the routes and handlers when invoked.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_initializer' => function (ContainerInterface $c) {
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
