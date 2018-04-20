<?php

namespace RebelCode\EddBookings\RestApi\Resource;

/**
 * Resource for clients.
 *
 * @since [*next-version*]
 */
class ClientResource extends AbstractBaseDataStoreResource
{
    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function toArray()
    {
        return [
            'id'    => $this->_get('id'),
            'name'  => $this->_get('name'),
            'email' => $this->_get('email'),
        ];
    }
}
