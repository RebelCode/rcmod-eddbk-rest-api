<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\CreateIterationCapableTrait;
use Dhii\Iterator\IterationInterface;
use RebelCode\EddBookings\RestApi\Transformer\Exception\CreateCouldNotTransformExceptionCapableTrait;

/**
 * A transformer implementation that can transform an {@see IterationInterface} instance.
 *
 * @since [*next-version*]
 */
class IterationTransformer implements TransformerInterface
{
    /* @since [*next-version*] */
    use CreateIterationCapableTrait;

    /* @since [*next-version*] */
    use CreateCouldNotTransformExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The transformer to use for iteration keys, if any.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface|null
     */
    protected $keyT9r;

    /**
     * The transformer to use for iteration values, if any.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface|null
     */
    protected $valT9r;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface|null $keyT9r The transformer to use for iteration keys, if any.
     * @param TransformerInterface|null $valT9r The transformer to use for iteration values, if any.
     */
    public function __construct(TransformerInterface $keyT9r = null, TransformerInterface $valT9r = null)
    {
        $this->keyT9r = $keyT9r;
        $this->valT9r = $valT9r;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        if (!($source instanceof IterationInterface)) {
            throw $this->_createCouldNotTransformException(
                $this->__('Source data is not an iteration instance'), null, null, $this, $source
            );
        }

        $key = ($this->keyT9r !== null) ? $this->keyT9r->transform($source->getKey()) : $source->getKey();
        $val = ($this->valT9r !== null) ? $this->valT9r->transform($source->getValue()) : $source->getValue();

        return $this->_createIteration($key, $val);
    }
}
