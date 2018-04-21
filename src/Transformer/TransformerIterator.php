<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Iterator;

/**
 * An iterator implementation that wraps and iterates over another iterator and applies a transformation on each
 * element in the iteration before yielding.
 *
 * @since [*next-version*]
 */
class TransformerIterator implements Iterator
{
    /**
     * The transformer to use.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $transformer;

    /**
     * The wrapped iterator.
     *
     * @since [*next-version*]
     *
     * @var Iterator
     */
    protected $iterator;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface $transformer The transformer to use.
     * @param Iterator             $iterator    The iterator to wrap.
     */
    public function __construct(TransformerInterface $transformer, $iterator)
    {
        $this->transformer = $transformer;
        $this->iterator    = $iterator;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function rewind()
    {
        $this->iterator->rewind();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function key()
    {
        return $this->iterator->key();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function current()
    {
        return $this->transformer->transform($this->iterator->current());
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function next()
    {
        $this->iterator->next();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function valid()
    {
        return $this->iterator->valid();
    }
}
