# hello-world

Custom WordPress plugin scaffold providing restaurant-specific routing, access control, and data scoping.

## Restaurant Manager plugin
- Registers a `restaurant` custom post type with branding settings and activation flag.
- Adds rewrite rules for front-end menu pages at `/restaurant/{slug}/menu` and scoped dashboards at `/restaurant/{slug}/dashboard`.
- Enforces `restaurant_id` scoping on queries, saves, and REST requests to keep data isolated per restaurant.
- Includes guard helpers that leverage user meta or request parameters to ensure users only interact with their assigned restaurant.
