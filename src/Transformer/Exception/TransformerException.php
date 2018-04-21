<?php

namespace RebelCode\EddBookings\RestApi\Transformer\Exception;

use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Exception\AbstractBaseException;
use RebelCode\EddBookings\RestApi\Transformer\TransformerAwareTrait;
use RebelCode\EddBookings\RestApi\Transformer\TransformerInterface;
use Throwable;

/**
 * Concrete implementation of an exception thrown in relation to a transformer.
 *
 * @since [*next-version*]
 */
class TransformerException extends AbstractBaseException implements TransformerExceptionInterface
{
    /* @since [*next-version*] */
    use TransformerAwareTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null    $message     The error message, if any.
     * @param int|null                  $code        The error code, if any.
     * @param Throwable|null            $previous    The previous exception, if any.
     * @param TransformerInterface|null $transformer The transformer that erred, if any.
     */
    public function __construct(
        $message = "",
        $code = 0,
        Throwable $previous = null,
        TransformerInterface $transformer = null
    ) {
        $this->_initBaseException($message, $code, $previous);
        $this->_setTransformer($transformer);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getTransformer()
    {
        return $this->_getTransformer();
    }
}
