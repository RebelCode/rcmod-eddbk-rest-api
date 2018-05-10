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

/*
 * The REST API routes related to sessions.
 *
 * @since [*next-version*]
 */
require __DIR__ . '/routes/sessions.php';

/*
 * The REST API routes related to services.
 *
 * @since [*next-version*]
 */
require __DIR__ . '/routes/services.php';

return $cfg;
