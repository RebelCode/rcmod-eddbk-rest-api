# Change log
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [[*next-version*]] - YYYY-MM-DD
### Added
- Routes, handlers and controllers for resources.

### Changed
- The service transformer now transforms session types instead of session lengths.

## [0.1-alpha10] - 2018-10-30
### Added
- Routes for service creation, updating and deletion.
- Hidden services (draft, protected, etc.) are now hidden from unauthorized clients.
- Certain sensitive service data is hidden when the received request is from an unauthenticated client.
- Service responses now include the service `status`.
- Service responses now include the service `color`.
- Sessions are generated on POST, PUT and PATCH via WordPress Cron.

### Changed
- The `availabilities` data key in service responses is now `availability`.

## [0.1-alpha9] - 2018-09-12
### Fixed
- The `POST /bookings` route was not authorizing logged in users.

## [0.1-alpha8] - 2018-09-11
### Added
- New mechanism for custom authorization using events.
- Added handler to authorize certain WP apps by nonce.

### Changed
- Booking creation endpoint may be authorized by nonce.

## [0.1-alpha7] - 2018-08-13
### Added
- Route config may specify a validator service to be used for authorization.
- An authorization validator `UserIsAdminAuthValidator` for WordPress administrator users.
- Bookings and clients routes now only authorize admin users.

### Fixed
- Sessions that only partially coincide with the queried `start` and `end` range were not included in the response.

## [0.1-alpha6] - 2018-07-12
### Fixed
- Added missing dev-dependency on a module package (#25).

## [0.1-alpha5] - 2018-06-12
### Changed
- Now using the new unbooked sessions RM in controller, instead of just the sessions one.

## [0.1-alpha4] - 2018-06-11
### Changed
- Increased the sessions response default and hard limits by 10 times.

### Fixed
- The bookings search query was only working when the query string was more than a certain length.

## [0.1-alpha3] - 2018-06-04
### Changed
- Now re-using an existing services transformer, for consistency and to avoid duplication across modules.

### Fixed
- Bookings now get created before initial transition. This fixes related errors.

## [0.1-alpha2] - 2018-05-24
### Changed
- Optimized responses from session endpoint: now excluding a lot of extra service-related data.

## [0.1-alpha1] - 2018-05-21
Initial version.
