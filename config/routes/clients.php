<?php

/**
 * This file contains the configuration for the routes related to clients in the EDD Bookings.
 *
 * @since [*next-version*]
 */

/*
 * The route for querying clients.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['routes'][] = [
    'pattern' => '/clients',
    'methods' => ['GET'],
    'handler' => 'eddbk_rest_api_query_clients_handler'
];

/*
 * The route for retrieving a client by ID.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['routes'][] = [
    'pattern' => '/clients/(?P<id>[\d]+)',
    'methods' => ['GET'],
    'handler' => 'eddbk_rest_api_get_client_info_handler'
];
