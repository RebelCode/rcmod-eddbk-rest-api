<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Resources;

use RebelCode\EddBookings\RestApi\Controller\ControllerAwareTrait;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Handlers\AbstractWpRestApiHandler;
use WP_REST_Request;
use WP_REST_Response;

/**
 * REST API handler for updating resources.
 *
 * @since [*next-version*]
 */
class UpdateResourceHandler extends AbstractWpRestApiHandler
{
    /* @since [*next-version*] */
    use ControllerAwareTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ControllerInterface $resourcesController The resources controller.
     */
    public function __construct(ControllerInterface $resourcesController)
    {
        $this->_setController($resourcesController);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _handle(WP_REST_Request $request)
    {
        $response = $this->_getController()->patch($request);
        $response = $this->_normalizeArray($response);

        return new WP_REST_Response($response, 200);
    }
}
