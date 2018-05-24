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
        'datetime_format' => DATE_ATOM,

        /*
         * The REST API routes.
         *
         * @since [*next-version*]
         */
        'routes' => array_merge(
            require(__DIR__ . '/routes/bookings.php'),
            require(__DIR__ . '/routes/sessions.php'),
            require(__DIR__ . '/routes/services.php'),
            require(__DIR__ . '/routes/clients.php')
        ),

        /*
         * Configuration for the REST API controllers.
         *
         * @since [*next-version*]
         */
        'controllers' => [
            /*
             * Configuration for the REST API sessions controller.
             *
             * @since [*next-version*]
             */
            'sessions' => [
                /**
                 * The default number of items to return per page.
                 *
                 * @since [*next-version*]
                 */
                'default_num_sessions_per_page' => 500,

                /**
                 * The maximum (hard cap) number of items to return per page.
                 *
                 * @since [*next-version*]
                 */
                'max_num_sessions_per_page' => 1000,
            ]
        ],
    ],
];
