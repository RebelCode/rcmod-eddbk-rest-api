<?php

namespace RebelCode\EddBookings\RestApi\Module;

use ArrayAccess;
use Dhii\Data\Container\ContainerAwareTrait;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Object\DataStoreAwareContainerTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\Container\ContainerInterface;
use stdClass;
use Traversable;

/**
 * Initializes the REST API.
 *
 * @since [*next-version*]
 */
class RestApiInitializer implements InvocableInterface
{
    /*
     * @since [*next-version*]
     */
    use DataStoreAwareContainerTrait;

    /*
     * @since [*next-version*]
     */
    use ContainerAwareTrait;

    /*
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeKeyCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeArrayCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeContainerCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $restApiConfig
     * @param ContainerInterface|null                       $handlerContainer
     */
    public function __construct($restApiConfig, $handlerContainer)
    {
        $this->_setDataStore($restApiConfig);
        $this->_setContainer($handlerContainer);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $this->_initRestApi();
    }

    /**
     * Initializes the REST API.
     *
     * @since [*next-version*]
     */
    protected function _initRestApi()
    {
        $config = $this->_getDataStore();

        $namespace = $this->_containerGet($config, 'namespace');
        $routesCfg = $this->_containerGet($config, 'routes');
        $routes    = $this->_processRouteConfig($routesCfg);

        foreach ($routes as $_pattern => $_config) {
            $this->_registerRoute($namespace, $_pattern, $_config);
        }
    }

    /**
     * Process the route config and prepares it for registration.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $config The configuration of the routes.
     *
     * @return array The processed config.
     */
    protected function _processRouteConfig($config)
    {
        $routes = [];

        foreach ($config as $_methodConfig) {
            $_pattern = $this->_containerGet($_methodConfig, 'pattern');
            $_methods = $this->_containerGet($_methodConfig, 'methods');
            $_handler = $this->_containerGet($_methodConfig, 'handler');

            if (!isset($routes[$_pattern])) {
                $routes[$_pattern] = [];
            }

            $routes[$_pattern][] = [
                'methods'  => $this->_normalizeArray($_methods),
                'callback' => $this->_getContainer()->get($_handler),
            ];
        }

        return $routes;
    }

    /**
     * Registers an API route.
     *
     * @since [*next-version*]
     *
     * @param string $namespace The namespace.
     * @param string $pattern   The route pattern.
     * @param array  $args      The route args.
     */
    protected function _registerRoute($namespace, $pattern, $args)
    {
        register_rest_route($namespace, $pattern, $args);
    }
}
