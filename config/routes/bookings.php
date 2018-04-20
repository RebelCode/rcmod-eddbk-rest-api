<?php

/**
 * This file contains the configuration for the routes related to bookings in the EDD Bookings.
 *
 * @since [*next-version*]
 */

/*
 * The base route for retrieving bookings.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['routes']['/bookings'][] = [
    'methods' => ['GET'],
    'handler' => 'eddbk_rest_api_get_bookings_handler'
];

/*
 * The base route for retrieving a booking by ID.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['routes']['/bookings/(?P<id>[\d]+)'][] = [
    'methods' => ['GET'],
    'handler' => 'eddbk_rest_api_get_booking_info_handler'
];
