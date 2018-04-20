<?php

/**
 * This file contains the configuration for the routes related to clients in the EDD Bookings.
 *
 * @since [*next-version*]
 */

/*
 * The base route for retrieving clients.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['routes']['/clients/(?P<id>[\d]+)'][] = [
    'methods' => ['GET'],
    'handler' => 'eddbk_rest_api_get_client_info_handler'
];
