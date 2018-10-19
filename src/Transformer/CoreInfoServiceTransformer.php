<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Transformer\TransformerInterface;
use RebelCode\Transformers\MapTransformer;

/**
 * A services transformer that transformers services with only their core information.
 *
 * @since [*next-version*]
 */
class CoreInfoServiceTransformer extends MapTransformer
{
    /**
     * The transformer for session length lists.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $sessionLengthListT9r;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface $sessionLengthListT9r The transformer for session length lists.
     */
    public function __construct(TransformerInterface $sessionLengthListT9r)
    {
        parent::__construct($this->_getServiceMapConfig());

        $this->sessionLengthListT9r = $sessionLengthListT9r;
    }

    /**
     * Retrieves the map config for this service transformer.
     *
     * @since [*next-version*]
     *
     * @return array
     */
    protected function _getServiceMapConfig()
    {
        return [
            [
                MapTransformer::K_SOURCE => 'id',
                MapTransformer::K_TARGET => 'id',
            ],
            [
                MapTransformer::K_SOURCE => 'name',
                MapTransformer::K_TARGET => 'name',
            ],
            [
                MapTransformer::K_SOURCE => 'description',
                MapTransformer::K_TARGET => 'description',
            ],
            [
                MapTransformer::K_SOURCE => 'image_url',
                MapTransformer::K_TARGET => 'imageSrc',
            ],
            [
                MapTransformer::K_SOURCE => 'session_lengths',
                MapTransformer::K_TARGET => 'sessionLengths',
                MapTransformer::K_TRANSFORMER => $this->sessionLengthListT9r,
            ],
            [
                MapTransformer::K_SOURCE => 'timezone',
            ],
            [
                MapTransformer::K_SOURCE => 'display_options',
                MapTransformer::K_TARGET => 'displayOptions',
            ],
        ];
    }
}
