<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

/**
 * Something that can transform some source data into some output data.
 *
 * @since [*next-version*]
 */
interface TransformerInterface
{
    /**
     * Transforms some source data into some output data.
     *
     * @since [*next-version*]
     *
     * @param mixed $source The source data to transform.
     *
     * @return mixed The output data.
     */
    public function transform($source);
}
