<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Exception\CreateInternalExceptionCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\CallbackAwareTrait;
use Dhii\Invocation\CreateInvocationExceptionCapableTrait;
use Dhii\Invocation\CreateReflectionForCallableCapableTrait;
use Dhii\Invocation\Exception\InvocationExceptionInterface;
use Dhii\Invocation\InvokeCallableCapableTrait;
use Dhii\Invocation\InvokeCallbackCapableTrait;
use Dhii\Invocation\NormalizeCallableCapableTrait;
use Dhii\Invocation\NormalizeMethodCallableCapableTrait;
use Dhii\Invocation\ValidateParamsCapableTrait;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Validation\CreateValidationFailedExceptionCapableTrait;
use Dhii\Validation\GetArgsListErrorsCapableTrait;
use Dhii\Validation\GetValueTypeErrorCapableTrait;
use Exception as RootException;
use RebelCode\EddBookings\RestApi\Transformer\Exception\CreateCouldNotTransformExceptionCapableTrait;
use RebelCode\EddBookings\RestApi\Transformer\Exception\CreateTransformerExceptionCapableTrait;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Implementation of a transformer that simply invokes a callback to perform the transformation.
 *
 * @since [*next-version*]
 */
class CallbackTransformer implements TransformerInterface
{
    /* @since [*next-version*] */
    use CallbackAwareTrait;

    /* @since [*next-version*] */
    use InvokeCallbackCapableTrait;

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
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

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
    use CreateInvocationExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateValidationFailedExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInternalExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateTransformerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateCouldNotTransformExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param callable $callback The transformation callback.
     */
    public function __construct($callback)
    {
        $this->_setCallback($callback);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        try {
            return $this->_invokeCallback([$source]);
        } catch (InvocationExceptionInterface $invocationException) {
            throw $this->_createCouldNotTransformException(
                $this->__('The callback failed to transform the source data'),
                null,
                $invocationException,
                $this,
                $source
            );
        } catch (RootException $exception) {
            throw $this->_createTransformerException(
                $this->__('The callback could not be invoked to transform the source data'),
                null,
                $exception,
                $this
            );
        }
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
