<?php

/**
 * This file contains the configuration for the routes related to services in the EDD Bookings.
 *
 * @since [*next-version*]
 */

/*
 * The route for querying services.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['routes']['get_services'] = [
    'pattern' => '/services',
    'methods' => ['GET'],
    'handler' => 'eddbk_rest_api_query_services_handler'
];

/*
 * The route for retrieving a service by ID.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['routes']['get_service'] = [
    'pattern' => '/services/(?P<id>[\d]+)',
    'methods' => ['GET'],
    'handler' => 'eddbk_rest_api_get_service_info_handler'
];
