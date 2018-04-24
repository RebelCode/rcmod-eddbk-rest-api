<?php

/**
 * This file contains the configuration for the routes related to bookings in the EDD Bookings.
 *
 * @since [*next-version*]
 */

/*
 * The route for querying bookings.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['routes']['get_bookings'] = [
    'pattern' => '/bookings',
    'methods' => ['GET'],
    'handler' => 'eddbk_rest_api_query_bookings_handler'
];

/*
 * The route for retrieving a booking by ID.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['routes']['get_booking_info'] = [
    'pattern' => '/bookings/(?P<id>[\d]+)',
    'methods' => ['GET'],
    'handler' => 'eddbk_rest_api_get_booking_info_handler'
];
