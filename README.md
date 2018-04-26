# RebelCode - RebelCode - EDD Bookings REST API - Module

[![Build Status](https://travis-ci.org/rebelcode/rcmod-eddbk-rest-api.svg?branch=master)](https://travis-ci.org/rebelcode/rcmod-eddbk-rest-api)
[![Code Climate](https://codeclimate.com/github/RebelCode/rcmod-eddbk-rest-api/badges/gpa.svg)](https://codeclimate.com/github/RebelCode/rcmod-eddbk-rest-api)
[![Test Coverage](https://codeclimate.com/github/RebelCode/rcmod-eddbk-rest-api/badges/coverage.svg)](https://codeclimate.com/github/RebelCode/rcmod-eddbk-rest-api/coverage)
[![Latest Stable Version](https://poser.pugx.org/rebelcode/rcmod-eddbk-rest-api/version)](https://packagist.org/packages/rebelcode/rcmod-eddbk-rest-api)

A RebelCode module that provides the REST API used in EDD Bookings.

## REST API Details

### 1. Bookings

#### Retrieving Booking Info

```
GET /bookings/<id>
```

Retrieves a booking by ID.

The response will contain an object with the following properties:

| Property | Type | Description | Values |
|----------|------|-------------|--------------|
| `id` | integer | The ID of the booking | positive non-zero integer |
| `start` | integer| The start time of the booking in the format `Y-M-D H:i:s` | integer |
| `end` | integer| The end time of the booking in the format `Y-M-D H:i:s` | integer |
| `status` | string | The current status of the booking | `""`, `"draft"`, `"in_cart"`, `"pending"`, `"approved"`, `"scheduled"`, `"cancelled"`, `"completed"` |
| `service` | service | The service for which the booking was made | Service object |
| `resource` | integer | The ID of the resource for which the booking was made | positive non-zero integer |
| `client` | client | The client for the booking | Client object or `null` |
| `clientTzName` | string | The client's timezone name | any timezone name or `null` |
| `clientTzOffset` | integer | The client's UTC timezone offset in seconds, at the `start` time of the booking | integer or `null` |
| `payment` | integer | The payment number | integer or `null` |
| `notes` | string | Admin booking notes | string | 

#### Query Bookings

```
GET /bookings?field=value&...
```

Multiple field and value pairs may be provided to narrow the query.
The following table lists the available query fields and how a provided value is compared:

| Field | Description | Value Type |
|-------|-------------|------------|
| `id`  | The booking with the given ID | integer |
| `start` | The bookings that start at or after the given UTC timestamp | integer |
| `end` | The bookings that end at or before the given UTC timestamp | integer |
| `service` | The bookings made for the service with the given ID | positive non-zero integer |
| `resource` | The bookings made for the resource with the given ID | positive non-zero integer |
| `client` | The bookings made for the client with the given ID | positive non-zero integer |
| `payment` | The bookings associated with the payment with the given ID | positive non-zero integer |

The response is an object with 3 keys: `items`, `count` and `statuses`. Example:

```
{
    items: [
        {
            "id": "21",
            "start": "2018-05-01 12:00:00",
            "end": "2018-05-01 13:30:00",
            "status": "draft",
            "service": {
                "id": 8,
                "name": "Test",
                "color": "#00ccff"
            },
            "resource": "0",
            "client": {
                "id": "6",
                "name": "Test User",
                "email": "test@eddbk.com"
            },
            "clientTzName": "Europe/Rome",
            "clientTzOffset": 1,
            "paymentNumber": 52,
            "notes": ""
        }
    ],
    "count": 1,
    "statuses": {
        "draft": 1,
        "in_cart": 0,
        "none": 0,
        "pending": 0,
        "approved": 0,
        "rejected": 0,
        "scheduled": 0,
        "completed": 0,
        "cancelled": 0
    }
}
```

#### Creating Bookings

```
POST /bookings
```

Creates a new booking and responds with the booking object. The following table lists the available fields:

| Property | Type | Description | Required? |
|----------|------|-------------|-----------|
| `start` | integer| The start time of the booking as a UTC timestamp | ✅ |
| `end` | integer| The end time of the booking as a UTC timestamp | ✅ |
| `service` | positive non-zero integer | The ID of the service for which the booking will be made | ✅ |
| `resource` | positive non-zero integer | The ID of the resource for which the booking will be made | ✅ |
| `client` | positive non-zero integer | The ID of the client for which the booking will be made | |
| `clientTz` | string | The name of the client's timezone | |
| `payment` | integer or `null` | The payment number | |
| `notes` | string | Admin booking notes | |

#### Updating Bookings

```
PATCH /bookings/<id>
```

Updates a booking. The following table lists the available fields for updating:

| Property | Type | Description |
|----------|------|-------------|
| `start` | integer| The start time of the booking as a UTC timestamp |
| `end` | integer| The end time of the booking as a UTC timestamp |
| `service` | positive non-zero integer | The ID of the service for which the booking will be made |
| `resource` | positive non-zero integer | The ID of the resource for which the booking will be made |
| `client` | positive non-zero integer | The ID of the client for which the booking will be made |
| `clientTz` | string | The name of the client's timezone |
| `payment` | integer | The payment number | integer or `null` |
| `notes` | string | Admin booking notes | string |

#### Deleting Bookings

```
DELETE /bookings/<id>
```

Deletes the booking with the given ID.
