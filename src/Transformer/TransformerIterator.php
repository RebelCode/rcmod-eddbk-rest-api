<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
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
use OutOfRangeException;
use RebelCode\EddBookings\RestApi\Transformer\Exception\TransformerExceptionInterface;

/**
 * An iterator implementation that wraps and iterates over another iterator and applies transformations to the
 * iterations before yielding them.
 *
 * The given transformer is given an {@see IterationInterface} instance to transform, and is expected to return the
 * transformed {@see IterationInterface} instance to yield.
 *
 * @since [*next-version*]
 */
class TransformerIterator implements TransformerInterface, Iterator
{
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
        return $this->transform($this->iterator->current());
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

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        return $this->transformer->transform($source);
    }
}
