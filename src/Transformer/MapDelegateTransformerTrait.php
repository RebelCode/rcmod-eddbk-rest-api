<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use ArrayAccess;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use OutOfRangeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as BaseContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\EddBookings\RestApi\Transformer\Exception\TransformerExceptionInterface;
use stdClass;
use Throwable;

/**
 * Common functionality for retrieving a delegate transformer from a map.
 *
 * @since [*next-version*]
 */
trait MapDelegateTransformerTrait
{
    /**
     * Retrieves the transformer to use for the given source data.
     *
     * @since [*next-version*]
     *
     * @param mixed $source The source data for which to retrieve a transformer for.
     *
     * @return TransformerInterface|null The transformer to use for transforming, or null if no suitable transformer
     *                                   can be returned.
     */
    protected function _getTransformer($source)
    {
        $map = $this->_getTransformerMap($source);
        $key = $this->_getTransformerKey($source);

        if (!$this->_containerHas($map, $key)) {
            return null;
        }

        $transformer = $this->_containerGet($map, $key);

        if (!($transformer instanceof TransformerInterface)) {
            throw $this->_createTransformerException(
                $this->__('Delegate transformer for key "%s" is not a transformer instance', [$key]),
                null,
                null,
                $this
            );
        }

        return $transformer;
    }

    /**
     * Retrieves the transformer map.
     *
     * @since [*next-version*]
     *
     * @param mixed $source The source data that is being transformed.
     *
     * @return array|stdClass|ArrayAccess|BaseContainerInterface
     */
    abstract protected function _getTransformerMap($source);

    /**
     * Retrieves the map key of the transformer to use for the given source data.
     *
     * @since [*next-version*]
     *
     * @param mixed $source The source data that is being transformed.
     *
     * @return string|Stringable
     */
    abstract protected function _getTransformerKey($source);

    /**
     * Retrieves a value from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|BaseContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable                  $key       The key of the value to retrieve.
     *
     * @throws InvalidArgumentException    If container is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     *
     * @return mixed The value mapped to the given key.
     */
    abstract protected function _containerGet($container, $key);

    /**
     * Checks for a key on a container.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|BaseContainerInterface $container The container to check.
     * @param string|int|float|bool|Stringable                  $key       The key to check for.
     *
     * @throws ContainerExceptionInterface If an error occurred while checking the container.
     * @throws OutOfRangeException         If the container or the key is invalid.
     *
     * @return bool True if the container has an entry for the given key, false if not.
     */
    abstract protected function _containerHas($container, $key);

    /**
     * Creates a transformer exception instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null       $message     The error message, if any.
     * @param int|null                     $code        The error code, if any.
     * @param RootException|Throwable|null $previous    The previous exception, if any.
     * @param TransformerInterface|null    $transformer The transformer that erred, if any.
     *
     * @return TransformerExceptionInterface
     */
    abstract protected function _createTransformerException(
        $message = null,
        $code = null,
        $previous = null,
        $transformer = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     * @see   _translate()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
