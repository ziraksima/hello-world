=== Restaurant Orders ===
Contributors: example
Tags: restaurant, orders, rest-api
Requires at least: 6.0
Tested up to: 6.5
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

== Description ==
Provides REST endpoints for managing restaurant menu items and orders, along with optional debug logging of order events.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/restaurant-orders` directory or clone the repository into your plugins folder.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. (Optional) Run `bin/install-wp-tests.sh <db-name> <db-user> <db-pass> <db-host>` to provision the WordPress test suite, then execute `phpunit`.
4. Visit **Settings → Restaurant Orders** to toggle debug logging for order events.

== Roles and Capabilities ==
* All REST endpoints require authentication.
* CRUD operations for items and orders require a user with the `manage_options` capability (Administrators by default).
* Reading item or order data requires the `read` capability.

== REST API Endpoints ==
All routes live under the `restaurant/v1` namespace.

=== Items ===
* `POST /restaurant/v1/items` — Create an item. Body: `name` (string), `price` (number), `restaurant_id` (int).
* `GET /restaurant/v1/items?restaurant_id=<id>` — List items, optionally filtered by `restaurant_id`.
* `GET /restaurant/v1/items/<id>` — Retrieve a single item.
* `PUT /restaurant/v1/items/<id>` — Update `name`, `price`, or `restaurant_id`.
* `DELETE /restaurant/v1/items/<id>` — Delete an item.

=== Orders ===
* `POST /restaurant/v1/orders` — Create an order. Body: `name` (string, optional), `status` (string, optional), `items` (array), `restaurant_id` (int).
* `GET /restaurant/v1/orders?restaurant_id=<id>` — List orders, optionally filtered by `restaurant_id`.
* `GET /restaurant/v1/orders/<id>` — Retrieve a single order.
* `PUT /restaurant/v1/orders/<id>` — Update `status`, `name`, `items`, or `restaurant_id`.
* `DELETE /restaurant/v1/orders/<id>` — Delete an order.
* `POST /restaurant/v1/orders/<id>/notify` — Record notification result (`result` must be `success` or `failure`).

== Debug Logging ==
Enable the **Debug log** checkbox in the settings page to start writing order lifecycle events (creation, status changes, notification success/failure) to `wp-content/uploads/restaurant-orders-debug.log`.
