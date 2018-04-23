<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Clients;

use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Handlers\AbstractWpRestApiHandler;
use RebelCode\EddBookings\RestApi\Resource\ResourceInterface;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Handles the REST API endpoint for querying clients.
 *
 * @since [*next-version*]
 */
class ClientsQueryHandler extends AbstractWpRestApiHandler
{
    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

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
     * @param ControllerInterface $controller The clients controller.
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
    public function _handle(WP_REST_Request $request)
    {
        $clients = $this->controller->get($request);
        $items = $this->_normalizeArray($clients);

        $response = [
            'items' => $items,
            'count' => count($items),
        ];

        return new WP_REST_Response($response, 200);
    }
}
