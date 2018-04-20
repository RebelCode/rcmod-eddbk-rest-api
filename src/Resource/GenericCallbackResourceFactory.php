<?php

namespace RebelCode\EddBookings\RestApi\Resource;

use Dhii\Factory\AbstractBaseCallbackFactory;
use Dhii\Invocation\CallbackAwareTrait;

/**
 * A generic, callback-based resource factory implementation.
 *
 * @since [*next-version*]
 */
class GenericCallbackResourceFactory extends AbstractBaseCallbackFactory implements ResourceFactoryInterface
{
    /*
     * Provides callback awareness.
     *
     * @since [*next-version*]
     */
    use CallbackAwareTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param callable $callback The callback.
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
    protected function _getFactoryCallback($config = null)
    {
        return $this->_getCallback();
    }
}
