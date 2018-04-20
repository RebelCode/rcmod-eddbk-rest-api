<?php

namespace RebelCode\EddBookings\RestApi\Resource;

/**
 * Resource for services.
 *
 * @since [*next-version*]
 */
class ServiceResource extends AbstractBaseDataStoreResource
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function toArray()
    {
        return [
            'id'    => $this->_get('ID'),
            'title' => $this->_get('post_title'),
            // @todo get from database
            'color' => '#00ccff',
        ];
    }
}
