<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\CreateIterationCapableTrait;
use Dhii\Iterator\CreateIteratorExceptionCapableTrait;
use Dhii\Iterator\IterationAwareTrait;
use Dhii\Iterator\IterationInterface;
use Dhii\Iterator\IteratorInterface;
use Dhii\Iterator\IteratorTrait;
use Exception as RootException;
use Iterator;

/**
 * An iterator implementation that wraps and iterates over another iterator and applies transformations to the keys
 * and values in the iteration before yielding them.
 *
 * @since [*next-version*]
 */
class TransformerIterator implements IteratorInterface
{
    /* @since [*next-version*] */
    use IteratorTrait;

    /* @since [*next-version*] */
    use IterationAwareTrait;

    /* @since [*next-version*] */
    use CreateIterationCapableTrait;

    /* @since [*next-version*] */
    use CreateIteratorExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The wrapped iterator.
     *
     * @since [*next-version*]
     *
     * @var Iterator
     */
    protected $iterator;

    /**
     * The transformer to use for values.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface|null
     */
    protected $valTransformer;

    /**
     * The transformer to use for keys.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface|null
     */
    protected $keyTransformer;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Iterator                  $iterator       The iterator to wrap.
     * @param TransformerInterface|null $valTransformer The transformer to use for transforming values, if any.
     * @param TransformerInterface|null $keyTransformer The transformer to use for transforming keys, if any.
     */
    public function __construct(
        $iterator,
        TransformerInterface $valTransformer,
        TransformerInterface $keyTransformer = null
    ) {
        $this->iterator       = $iterator;
        $this->valTransformer = $valTransformer;
        $this->keyTransformer = $keyTransformer;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function rewind()
    {
        $this->_rewind();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function key()
    {
        return $this->_key();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function current()
    {
        return $this->_value();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function next()
    {
        $this->_next();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function valid()
    {
        return $this->_valid();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getIteration()
    {
        return $this->_getIteration();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _reset()
    {
        $this->iterator->rewind();

        return $this->_getTransformedIteration();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _loop()
    {
        $this->iterator->next();

        return $this->_getTransformedIteration();
    }

    /**
     * Gets the current iteration, with transformations applied to the key and value if applicable.
     *
     * @since [*next-version*]
     *
     * @return IterationInterface The iteration instance.
     */
    protected function _getTransformedIteration()
    {
        $key = $this->iterator->key();
        $key = ($this->keyTransformer !== null)
            ? $this->keyTransformer->transform($key)
            : $key;

        $val = $this->iterator->current();
        $val = ($this->valTransformer !== null)
            ? $this->valTransformer->transform($val)
            : $val;

        return $this->_createIteration($key, $val);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _throwIteratorException(
        $message = null,
        $code = null,
        RootException $previous = null
    ) {
        return $this->_createIteratorException($message, $code, $previous, $this);
    }
}
