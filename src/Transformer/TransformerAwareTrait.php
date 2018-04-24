<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;

/**
 * Common functionality for awareness of a transformer instance.
 *
 * @since [*next-version*]
 */
trait TransformerAwareTrait
{
    /**
     * The transformer instance.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface|null
     */
    protected $transformer;

    /**
     * Retrieves the transformer associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return TransformerInterface|null
     */
    protected function _getTransformer()
    {
        return $this->transformer;
    }

    /**
     * Sets the transformer for this instance.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface|null $transformer The transformer instance, if any.
     *
     * @throws InvalidArgumentException If the argument is not a transformer instance.
     */
    protected function _setTransformer($transformer)
    {
        if ($transformer !== null && !($transformer instanceof TransformerInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a transformer instance'),
                null,
                null,
                $transformer
            );
        }

        $this->transformer = $transformer;
    }

    /**
     * Creates a new Dhii invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|int|float|bool|null $message  The message, if any.
     * @param int|float|string|Stringable|null      $code     The numeric error code, if any.
     * @param RootException|null                    $previous The inner exception, if any.
     * @param mixed|null                            $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
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
