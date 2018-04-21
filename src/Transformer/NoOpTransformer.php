<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

/**
 * A no-operation transformer implementation.
 *
 * @since [*next-version*]
 */
class NoOpTransformer implements TransformerInterface
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        return $source;
    }
}
