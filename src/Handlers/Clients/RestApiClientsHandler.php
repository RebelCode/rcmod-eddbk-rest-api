<?php

namespace RebelCode\EddBookings\RestApi\Handlers\Clients;

use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\Invocation\InvocableInterface;
use Exception;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\EddBookings\RestApi\Controller\ControllerInterface;
use RebelCode\EddBookings\RestApi\Resource\ResourceInterface;
use WP_Error;
use WP_REST_Request;
use WP_REST_Response;

class RestApiClientsHandler implements InvocableInterface
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
    public function __invoke()
    {
        /* @var $request WP_REST_Request */
        $request = func_get_arg(0);

        try {
            $clients = $this->controller->get($request);
            $result  = array_map(function (ResourceInterface $resource) {
                return $resource->toArray();
            }, $clients);

            return new WP_REST_Response($result, 200);
        } catch (NotFoundExceptionInterface $notFoundException) {
            return new WP_Error('eddbk_client_invalid_id', 'Invalid client ID.', ['status' => 404]);
        } catch (Exception $exception) {
            return new WP_Error('eddbk_client_error', $exception->getMessage(), ['status' => 500]);
        }
    }
}
