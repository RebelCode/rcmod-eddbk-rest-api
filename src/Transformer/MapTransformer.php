<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInternalExceptionCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\CreateInvocationExceptionCapableTrait;
use Dhii\Invocation\CreateReflectionForCallableCapableTrait;
use Dhii\Invocation\InvokeCallableCapableTrait;
use Dhii\Invocation\NormalizeCallableCapableTrait;
use Dhii\Invocation\NormalizeMethodCallableCapableTrait;
use Dhii\Invocation\ValidateParamsCapableTrait;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Validation\CreateValidationFailedExceptionCapableTrait;
use Dhii\Validation\GetArgsListErrorsCapableTrait;
use Dhii\Validation\GetValueTypeErrorCapableTrait;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionFunction;
use ReflectionMethod;
use stdClass;
use Traversable;

/**
 * Implementation of a transformer that can transform and kind of container using an internal map of transformers.
 *
 * This implementation requires configuration in the form of a list of maps. The keys accepted for these maps are as
 * follows:
 *
 * * 'source' - the key of the input value in input maps
 * * 'target' - the key to use for the transformed value
 * * 'transformer' - the transformer instance or a callback.
 *
 * The 'source' config may be omitted, in which case the value to transform will not be fetched from the map, `null`
 * will be used instead and the 'target' key must be present.
 *
 * The 'target' config may be omitted, in which case the value of the 'source' config will be used.
 *
 * If the 'transformer' config is a callback, it will receive the value retrieved from the source map as the first
 * argument (or null if the 'source' config was not given or the value was not found) and the whole source map as the
 * second argument. The callback is expected to return the transformed value to assign to the transformed map at the
 * 'target' key.
 *
 * @since [*next-version*]
 */
class MapTransformer implements TransformerInterface
{
    /* @since [*next-version*] */
    use InvokeCallableCapableTrait;

    /* @since [*next-version*] */
    use ValidateParamsCapableTrait;

    /* @since [*next-version*] */
    use GetArgsListErrorsCapableTrait;

    /* @since [*next-version*] */
    use GetValueTypeErrorCapableTrait;

    /* @since [*next-version*] */
    use CreateReflectionForCallableCapableTrait;

    /* @since [*next-version*] */
    use CountIterableCapableTrait;

    /* @since [*next-version*] */
    use ResolveIteratorCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use NormalizeCallableCapableTrait;

    /* @since [*next-version*] */
    use NormalizeMethodCallableCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInternalExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvocationExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateValidationFailedExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The config key from where to retrieve the target results key.
     *
     * @since [*next-version*]
     */
    const K_TARGET = 'target';

    /**
     * The config key from where to retrieve the source value from the map.
     *
     * @since [*next-version*]
     */
    const K_SOURCE = 'source';

    /**
     * The config key from where to retrieve the transformer.
     *
     * @since [*next-version*]
     */
    const K_TRANSFORMER = 'transformer';

    /**
     * The map of transformers.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $mapConfig;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $mapConfig The map config. See {@see MapTransformer}.
     */
    public function __construct($mapConfig)
    {
        $this->_setMapConfig($mapConfig);
    }

    /**
     * Retrieves the map config.
     *
     * @since [*next-version*]
     *
     * @return array|stdClass|Traversable The map config. See {@see MapTransformer}.
     */
    protected function _getMapConfig()
    {
        return $this->mapConfig;
    }

    /**
     * Sets the map config.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $mapConfig The map config. See {@see MapTransformer}.
     */
    protected function _setMapConfig($mapConfig)
    {
        $this->mapConfig = $this->_normalizeIterable($mapConfig);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        $result = [];

        foreach ($this->_getMapConfig() as $config) {
            // Get the source and target keys from config
            $sourceKey = $this->_containerGetDefault($config, static::K_SOURCE, null);
            $targetKey = $this->_containerGetDefault($config, static::K_TARGET, $sourceKey);
            // At least one must be given - throw if both are omitted
            if ($sourceKey === null && $targetKey === null) {
                throw $this->_createOutOfRangeException(
                    $this->__(
                        'Config must have at least either a "%1$s" or a %2$s key', [static::K_SOURCE, static::K_TARGET]
                    ),
                    null, null, $config
                );
            }

            // Get the value from the source map
            $value = $sourceKey !== null
                ? $this->_containerGetDefault($source, $sourceKey)
                : null;
            // Get the transformer
            $transformer = $this->_containerGetDefault($config, static::K_TRANSFORMER, null);

            try {
                // Normalize transformation to a callable
                $_callback = $this->_normalizeTransformationCallable($transformer, $value, $source);
            } catch (InvalidArgumentException $invalidArgumentException) {
                throw $this->_createOutOfRangeException(
                    $this->__('Config contains an invalid transformer for target key "%s"', [$targetKey]),
                    null, $invalidArgumentException, $config
                );
            }

            // Invoke transformation callback
            $result[$targetKey] = $this->_invokeCallable($_callback, [$value, $source]);
        }

        return $result;
    }

    /**
     * Retrieves a value from a container or data set, defaulting to a specific value on failure.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable              $key       The key of the value to retrieve.
     * @param mixed|null                                    $default   The value to default to.
     *
     * @throws InvalidArgumentException    If container is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     *
     * @return mixed The value mapped to the given key, or the $default if the valid was not found.
     */
    protected function _containerGetDefault($container, $key, $default = null)
    {
        try {
            return $this->_containerGet($container, $key);
        } catch (NotFoundExceptionInterface $notFoundException) {
            return $default;
        }
    }

    /**
     * Normalizes a transformer into a callback.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface $transformer The transformer instance to wrap.
     * @param mixed|null           $source      The source data.
     * @param mixed|null           $value       The original value.
     *
     * @return callable A callback that performs the transformer.
     */
    protected function _normalizeTransformationCallable($transformer, $source, $value)
    {
        if ($transformer !== null && !($transformer instanceof TransformerInterface) && !is_callable($transformer)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a transformer instance or callable'), null, null, $transformer
            );
        }

        if ($transformer instanceof TransformerInterface) {
            return function ($value, $source) use ($transformer) {
                return $transformer->transform($value);
            };
        }

        if ($transformer === null) {
            return function ($value, $source) {
                return $value;
            };
        }

        return $transformer;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createReflectionMethod($className, $methodName)
    {
        return new ReflectionMethod($className, $methodName);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createReflectionFunction($functionName)
    {
        return new ReflectionFunction($functionName);
    }
}
