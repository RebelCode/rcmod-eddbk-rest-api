<?php

/*
 * The REST API routes.
 *
 * @since [*next-version*]
 */
$cfg['eddbk_rest_api']['routes'] = [];

/*
 * The REST API routes related to bookings.
 *
 * @since [*next-version*]
 */
require __DIR__ . '/routes/bookings.php';

/*
 * The REST API routes related to clients.
 *
 * @since [*next-version*]
 */
require __DIR__ . '/routes/clients.php';

return $cfg;
