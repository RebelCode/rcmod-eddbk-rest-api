<?php

/**
 * This file contains the configuration for the EDD Bookings WordPress REST API module.
 *
 * @since [*next-version*]
 */

return [
    'eddbk_rest_api' => [
        /**
         * The REST API version number.
         *
         * @since [*next-version*]
         */
        'version' => '1',

        /**
         * The identifying name of the REST API.
         *
         * @since [*next-version*]
         */
        'name' => 'eddbk',

        /**
         * The REST API namespace.
         *
         * @since [*next-version*]
         */
        'namespace' => '${eddbk_rest_api/name}/v${eddbk_rest_api/version}',

        /**
         * The date time format to use in REST API responses.
         *
         * @since [*next-version*]
         */
        'datetime_format' => DATE_ISO8601,

        /*
         * The REST API routes.
         *
         * @since [*next-version*]
         */
        'routes' => [
            /*
             * The REST API routes related to bookings.
             *
             * @since [*next-version*]
             */
            'bookings' => require(__DIR__ . '/routes/bookings.php'),

            /*
             * The REST API routes related to sessions.
             *
             * @since [*next-version*]
             */
            'sessions' => require(__DIR__ . '/routes/sessions.php'),

            /*
             * The REST API routes related to services.
             *
             * @since [*next-version*]
             */
            'services' => require(__DIR__ . '/routes/services.php'),

            /*
             * The REST API routes related to clients.
             *
             * @since [*next-version*]
             */
            'clients' => require(__DIR__ . '/routes/clients.php'),
        ]
    ],
];
