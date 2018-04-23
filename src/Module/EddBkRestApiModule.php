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
use RebelCode\EddBookings\RestApi\Controller\BookingsController;
use RebelCode\EddBookings\RestApi\Controller\ClientsController;
use RebelCode\EddBookings\RestApi\Controller\ServicesController;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\BookingsQueryHandler;
use RebelCode\EddBookings\RestApi\Handlers\Bookings\SingleBookingHandler;
use RebelCode\EddBookings\RestApi\Handlers\Clients\ClientsQueryHandler;
use RebelCode\EddBookings\RestApi\Handlers\Clients\SingleClientHandler;
use RebelCode\EddBookings\RestApi\Transformer\CallbackTransformer;
use RebelCode\EddBookings\RestApi\Transformer\MapTransformer;
use RebelCode\EddBookings\RestApi\Transformer\NoOpTransformer;
use RebelCode\EddBookings\RestApi\Transformer\TransformerIterator;
use RebelCode\Modular\Module\AbstractBaseModule;
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
                'eddbk_bookings_controller'                => function (ContainerInterface $c) {
                    return new BookingsController(
                        $c->get('eddbk_rest_api_bookings_iterator_factory'),
                        $c->get('bookings_select_rm'),
                        $c->get('sql_expression_builder'),
                        $c->get('eddbk_clients_controller')
                    );
                },

                /*
                 * The services REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_services_controller'                => function (ContainerInterface $c) {
                    return new ServicesController($c->get('eddbk_rest_api_services_iterator_factory'));
                },

                /*
                 * The clients REST API resource controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_clients_controller'                 => function (ContainerInterface $c) {
                    return new ClientsController($c->get('eddbk_rest_api_clients_iterator_factory'), EDD()->customers);
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
                        $items    = $this->_containerGet($config, 'items');
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

                        // Iterator of results, which are WP_Post instances
                        $postsIterator = $this->_normalizeIterator($items);

                        // Iterate that transforms WP_Post instances to arrays
                        $arrayIterator = new TransformerIterator(
                            $postsIterator,
                            $c->get('eddbk_post_array_transformer')
                        );

                        return new TransformerIterator($arrayIterator, $c->get('eddbk_rest_api_services_transformer'));
                    });
                },

                /*
                 * The iterator factory for the clients controller.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_clients_iterator_factory'  => function (ContainerInterface $c) {
                    return new GenericCallbackFactory(function ($config) use ($c) {
                        $items    = $this->_containerGet($config, 'items');
                        $iterator = $this->_normalizeIterator($items);

                        return new TransformerIterator($iterator, $c->get('eddbk_rest_api_clients_transformer'));
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
                'eddbk_rest_api_query_bookings_handler'    => function (ContainerInterface $c) {
                    return new BookingsQueryHandler(
                        $c->get('eddbk_bookings_controller'),
                        $c->get('booking_logic/statuses')
                    );
                },

                /*
                 * Handles the bookings route that provides information about a single booking.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_get_booking_info_handler'  => function (ContainerInterface $c) {
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
                'eddbk_rest_api_query_clients_handler'     => function (ContainerInterface $c) {
                    return new ClientsQueryHandler($c->get('eddbk_clients_controller'));
                },

                /*
                 * Handles the clients route that receives generic client queries.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_get_client_info_handler'   => function (ContainerInterface $c) {
                    return new SingleClientHandler($c->get('eddbk_clients_controller'));
                },

                /*-------------------------------------------------------------*\
                 * Transformers - for data, not robots in disguise             *
                \*-------------------------------------------------------------*/

                /*
                 * A no-operation transformer.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_noop_transformer'                   => function (ContainerInterface $c = null) {
                    return new NoOpTransformer();
                },

                /*
                 * Transformer that transforms WP_Post instances into arrays.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_post_array_transformer'             => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($post) {
                        if (!$post instanceof WP_Post) {
                            throw $this->_createInvalidArgumentException(
                                $this->__('Argument is not a WP_Post instance'), null, null, $post
                            );
                        }

                        return $post->to_array();
                    });
                },

                /*
                 * Transformer that transforms timestamps into formatted datetime strings.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_timestamp_datetime_transformer'     => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($timestamp) use ($c) {
                        $tzName   = get_option('timezone_string');
                        $tzName   = empty($tzName) ? 'UTC' : $tzName;
                        $timezone = new DateTimeZone($tzName);
                        $dateTime = new DateTime('@' . $timestamp, $timezone);

                        return $dateTime->format($c->get('eddbk_rest_api/datetime_format'));
                    });
                },

                /*
                 * The transformer that transforms bookings into the result that is send in REST API responses.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_bookings_transformer'      => function (ContainerInterface $c) {
                    return new MapTransformer([
                        [
                            'source' => 'id',
                        ],
                        [
                            'source'      => 'start',
                            'transformer' => $c->get('eddbk_timestamp_datetime_transformer'),
                        ],
                        [
                            'source'      => 'end',
                            'transformer' => $c->get('eddbk_timestamp_datetime_transformer'),
                        ],
                        [
                            'source' => 'status',
                        ],
                        [
                            'source'      => 'service_id',
                            'target'      => 'service',
                            'transformer' => $c->get('eddbk_rest_api_service_id_transformer'),
                        ],
                        [
                            'source' => 'resource_id',
                            'target' => 'resource',
                        ],
                        [
                            'source'      => 'client_id',
                            'target'      => 'client',
                            'transformer' => $c->get('eddbk_rest_api_client_id_transformer'),
                        ],
                        [
                            'source' => 'client_tz',
                            'target' => 'clientTzName',
                        ],
                        [
                            'target'      => 'clientTzOffset',
                            'transformer' => $c->get('eddbk_rest_api_booking_timezone_offset'),
                        ],
                        [
                            'source' => 'payment_id',
                            'target' => 'paymentNumber',
                        ],
                        [
                            'source' => 'admin_notes',
                            'target' => 'notes',
                        ],
                    ]);
                },

                /*
                 * The transformer that transforms services into the result that is send in REST API responses.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_services_transformer'      => function (ContainerInterface $c) {
                    return new MapTransformer([
                        [
                            'source' => 'ID',
                            'target' => 'id',
                        ],
                        [
                            'source' => 'post_title',
                            'target' => 'name',
                        ],
                        [
                            'target'      => 'color',
                            'transformer' => function () {
                                return '#00ccff';
                            },
                        ],
                    ]);
                },

                /*
                 * The transformer that transforms clients into the result that is send in REST API responses.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_clients_transformer'       => function (ContainerInterface $c) {
                    return new MapTransformer([
                        [
                            'source' => 'id',
                        ],
                        [
                            'source' => 'name',
                        ],
                        [
                            'source' => 'email',
                        ],

                    ]);
                },

                /*
                 * The transformer that transforms a service ID into service data.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_service_id_transformer'    => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($serviceId) use ($c) {
                        $controller = $c->get('eddbk_services_controller');
                        $services   = $controller->get(['id' => $serviceId]);

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
                'eddbk_rest_api_client_id_transformer'     => function (ContainerInterface $c) {
                    return new CallbackTransformer(function ($clientId) use ($c) {
                        $controller = $c->get('eddbk_clients_controller');
                        $clients    = $controller->get(['id' => $clientId]);

                        foreach ($clients as $client) {
                            break;
                        }

                        return $client;
                    });
                },

                /*
                 * Generates the timezone offset from a booking's client timezone name.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_booking_timezone_offset_transformer' => function() {
                    return function ($value, $source) {
                        $timezone = new DateTimeZone($this->_containerGet($source, 'client_tz'));
                        $time     = new DateTime('@' . $this->_containerGet($source, 'start'));

                        return $timezone->getOffset($time);
                    };
                },

                /*-------------------------------------------------------------*\
                 * Misc. REST API services                                     *
                \*-------------------------------------------------------------*/

                /*
                 * The REST API initializer - initializes the routes and handlers when invoked.
                 *
                 * @since [*next-version*]
                 */
                'eddbk_rest_api_initializer'               => function (ContainerInterface $c) {
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
