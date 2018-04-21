<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\CreateIterationCapableTrait;
use Dhii\Iterator\CreateIteratorExceptionCapableTrait;
use Dhii\Iterator\Exception\IteratorExceptionInterface;
use Dhii\Iterator\IterationAwareTrait;
use Dhii\Iterator\IterationInterface;
use Dhii\Iterator\IteratorInterface;
use Dhii\Iterator\IteratorTrait;
use Exception as RootException;
use Iterator;

/**
 * An iterator implementation that wraps and iterates over another iterator and applies transformations to the
 * iterations before yielding them.
 *
 * The given transformer is given an {@see IterationInterface} instance to transform, and is expected to return the
 * transformed {@see IterationInterface} instance to yield.
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
    use CreateOutOfRangeExceptionCapableTrait;

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
     * The transformer to use for transforming iterations.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $transformer;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param Iterator             $iterator    The iterator to wrap.
     * @param TransformerInterface $transformer The transformer to use for transforming iterations.
     */
    public function __construct($iterator, TransformerInterface $transformer)
    {
        $this->iterator    = $iterator;
        $this->transformer = $transformer;
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
     *
     * @throws IteratorExceptionInterface If the transformed iteration is not a valid iteration.
     */
    protected function _getTransformedIteration()
    {
        $iteration = $this->_createIteration(
            $this->iterator->key(),
            $this->iterator->current()
        );

        $transformed = $this->transformer->transform($iteration);

        if (!$transformed instanceof IterationInterface) {
            throw $this->_createOutOfRangeException(
                $this->__('The transformed iteration is not a valid iteration instance'),
                null,
                null,
                $iteration
            );
        }

        return $transformed;
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
