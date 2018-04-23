<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use RebelCode\EddBookings\RestApi\Transformer\Exception\CouldNotTransformExceptionInterface;
use RebelCode\EddBookings\RestApi\Transformer\Exception\TransformerExceptionInterface;

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
     * @throws TransformerExceptionInterface       If an error occurred during transformation.
     * @throws CouldNotTransformExceptionInterface If the given source data could not be transformed.
     *
     * @return mixed The output data.
     */
    public function transform($source);
}
