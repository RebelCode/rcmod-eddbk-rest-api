<?php

namespace RebelCode\EddBookings\RestApi\Transformer\Exception;

use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Exception\AbstractBaseException;
use RebelCode\EddBookings\RestApi\Transformer\TransformerAwareTrait;
use RebelCode\EddBookings\RestApi\Transformer\TransformerInterface;
use Throwable;

/**
 * Concrete implementation of an exception thrown in relation to a transformer that failed to transform.
 *
 * @since [*next-version*]
 */
class CouldNotTransformException extends AbstractBaseException implements CouldNotTransformExceptionInterface
{
    /* @since [*next-version*] */
    use TransformerAwareTrait;

    /**
     * The source data that could not be transformed.
     *
     * @since [*next-version*]
     *
     * @var mixed|null
     */
    protected $source;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null    $message     The error message, if any.
     * @param int|null                  $code        The error code, if any.
     * @param Throwable|null            $previous    The previous exception, if any.
     * @param TransformerInterface|null $transformer The transformer that erred, if any.
     * @param mixed|null                $source      The source data that could not be transformed, if any.
     */
    public function __construct(
        $message = '',
        $code = 0,
        Throwable $previous = null,
        TransformerInterface $transformer = null,
        $source = null
    ) {
        $this->_initBaseException($message, $code, $previous);
        $this->_setTransformer($transformer);
        $this->source = $source;
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

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getSourceData()
    {
        return $this->source;
    }
}
