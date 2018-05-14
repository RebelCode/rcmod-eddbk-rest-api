<?php

/**
 * This file contains the configuration for the EDD Bookings WordPress REST API module.
 *
 * @since [*next-version*]
 */

/**
 * The REST API version number.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['version'] = '1';

/**
 * The identifying name of the REST API.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['name'] = 'eddbk';

/**
 * The REST API namespace.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['namespace'] = '${eddbk_rest_api/name}/v${eddbk_rest_api/version}';

/**
 * The date time format to use in REST API responses.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['datetime_format'] = DATE_ISO8601;

/*
 * The REST API routes.
 *
 * @since [*next-version*]
 */
require __DIR__ . '/routes.php';

return $cfg;
