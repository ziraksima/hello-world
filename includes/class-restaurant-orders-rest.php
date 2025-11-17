<?php
class Restaurant_Orders_REST {
    private $plugin;
    private $logger;
    private $namespace = 'restaurant/v1';

    public function __construct( Restaurant_Orders $plugin, Restaurant_Orders_Logger $logger ) {
        $this->plugin = $plugin;
        $this->logger = $logger;
    }

    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/items',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'can_manage' ],
                    'args'                => $this->get_item_schema(),
                ],
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'list_items' ],
                    'permission_callback' => [ $this, 'can_read' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/items/(?P<id>\d+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'can_read' ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_item' ],
                    'permission_callback' => [ $this, 'can_manage' ],
                    'args'                => $this->get_item_schema(),
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'can_manage' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/orders',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_order' ],
                    'permission_callback' => [ $this, 'can_manage' ],
                    'args'                => $this->get_order_schema(),
                ],
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'list_orders' ],
                    'permission_callback' => [ $this, 'can_read' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/orders/(?P<id>\d+)',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_order' ],
                    'permission_callback' => [ $this, 'can_read' ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_order' ],
                    'permission_callback' => [ $this, 'can_manage' ],
                    'args'                => [
                        'status' => [
                            'required' => false,
                            'type'     => 'string',
                        ],
                        'restaurant_id' => [
                            'required' => false,
                            'type'     => 'integer',
                        ],
                        'items' => [
                            'required' => false,
                            'type'     => 'array',
                        ],
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_order' ],
                    'permission_callback' => [ $this, 'can_manage' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace,
            '/orders/(?P<id>\d+)/notify',
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [ $this, 'notify_order' ],
                'permission_callback' => [ $this, 'can_manage' ],
                'args'                => [
                    'result' => [
                        'required' => true,
                        'type'     => 'string',
                        'enum'     => [ 'success', 'failure' ],
                    ],
                ],
            ]
        );
    }

    public function can_manage() {
        return current_user_can( 'manage_options' );
    }

    public function can_read() {
        return current_user_can( 'read' );
    }

    public function create_item( WP_REST_Request $request ) {
        $post_id = wp_insert_post(
            [
                'post_type'   => Restaurant_Orders::ITEM_CPT,
                'post_title'  => sanitize_text_field( $request['name'] ),
                'post_status' => 'publish',
                'meta_input'  => [
                    'price'         => floatval( $request['price'] ),
                    'restaurant_id' => intval( $request['restaurant_id'] ),
                ],
            ]
        );

        return $this->prepare_item_for_response( get_post( $post_id ), $request );
    }

    public function get_item( WP_REST_Request $request ) {
        $post = get_post( (int) $request['id'] );
        return $this->prepare_item_for_response( $post, $request );
    }

    public function update_item( WP_REST_Request $request ) {
        $post_id = (int) $request['id'];

        wp_update_post(
            [
                'ID'         => $post_id,
                'post_title' => sanitize_text_field( $request['name'] ),
            ]
        );

        if ( null !== $request['price'] ) {
            update_post_meta( $post_id, 'price', floatval( $request['price'] ) );
        }

        if ( null !== $request['restaurant_id'] ) {
            update_post_meta( $post_id, 'restaurant_id', intval( $request['restaurant_id'] ) );
        }

        return $this->prepare_item_for_response( get_post( $post_id ), $request );
    }

    public function delete_item( WP_REST_Request $request ) {
        $post = get_post( (int) $request['id'] );
        wp_trash_post( $post->ID );
        return rest_ensure_response( [ 'deleted' => true ] );
    }

    public function list_items( WP_REST_Request $request ) {
        $args = [
            'post_type'      => Restaurant_Orders::ITEM_CPT,
            'posts_per_page' => -1,
            'meta_query'     => [],
        ];

        if ( $request['restaurant_id'] ) {
            $args['meta_query'][] = [
                'key'   => 'restaurant_id',
                'value' => intval( $request['restaurant_id'] ),
            ];
        }

        $items = get_posts( $args );
        return array_map( function ( $post ) use ( $request ) {
            return $this->prepare_item_for_response( $post, $request );
        }, $items );
    }

    public function create_order( WP_REST_Request $request ) {
        $post_id = wp_insert_post(
            [
                'post_type'   => Restaurant_Orders::ORDER_CPT,
                'post_title'  => sanitize_text_field( $request['name'] ?? 'Order' ),
                'post_status' => sanitize_key( $request['status'] ?? 'pending' ),
                'meta_input'  => [
                    'items'         => wp_json_encode( $request['items'] ?? [] ),
                    'restaurant_id' => intval( $request['restaurant_id'] ),
                ],
            ]
        );

        $this->logger->log( sprintf( 'Order %d created for restaurant %d', $post_id, intval( $request['restaurant_id'] ) ) );

        return $this->prepare_order_for_response( get_post( $post_id ), $request );
    }

    public function get_order( WP_REST_Request $request ) {
        $post = get_post( (int) $request['id'] );
        return $this->prepare_order_for_response( $post, $request );
    }

    public function update_order( WP_REST_Request $request ) {
        $post_id = (int) $request['id'];
        $post    = get_post( $post_id );

        $updated_post = [ 'ID' => $post_id ];

        if ( null !== $request['status'] ) {
            $updated_post['post_status'] = sanitize_key( $request['status'] );
        }

        if ( isset( $request['name'] ) ) {
            $updated_post['post_title'] = sanitize_text_field( $request['name'] );
        }

        if ( count( $updated_post ) > 1 ) {
            wp_update_post( $updated_post );
        }

        if ( null !== $request['restaurant_id'] ) {
            update_post_meta( $post_id, 'restaurant_id', intval( $request['restaurant_id'] ) );
        }

        if ( null !== $request['items'] ) {
            update_post_meta( $post_id, 'items', wp_json_encode( $request['items'] ) );
        }

        return $this->prepare_order_for_response( get_post( $post_id ), $request );
    }

    public function delete_order( WP_REST_Request $request ) {
        wp_trash_post( (int) $request['id'] );
        return rest_ensure_response( [ 'deleted' => true ] );
    }

    public function list_orders( WP_REST_Request $request ) {
        $args = [
            'post_type'      => Restaurant_Orders::ORDER_CPT,
            'posts_per_page' => -1,
            'meta_query'     => [],
        ];

        if ( $request['restaurant_id'] ) {
            $args['meta_query'][] = [
                'key'   => 'restaurant_id',
                'value' => intval( $request['restaurant_id'] ),
            ];
        }

        $orders = get_posts( $args );
        return array_map( function ( $post ) use ( $request ) {
            return $this->prepare_order_for_response( $post, $request );
        }, $orders );
    }

    public function notify_order( WP_REST_Request $request ) {
        $order_id = (int) $request['id'];
        $result   = $request['result'];

        $message = 'success' === $result
            ? sprintf( 'Notification sent for order %d', $order_id )
            : sprintf( 'Notification failed for order %d', $order_id );

        $this->logger->log( $message );
        return rest_ensure_response( [ 'notified' => $result ] );
    }

    private function prepare_item_for_response( $post, WP_REST_Request $request ) {
        if ( ! $post ) {
            return new WP_Error( 'not_found', __( 'Item not found', 'restaurant-orders' ), [ 'status' => 404 ] );
        }

        return rest_ensure_response(
            [
                'id'            => $post->ID,
                'name'          => $post->post_title,
                'price'         => (float) get_post_meta( $post->ID, 'price', true ),
                'restaurant_id' => (int) get_post_meta( $post->ID, 'restaurant_id', true ),
            ]
        );
    }

    private function prepare_order_for_response( $post, WP_REST_Request $request ) {
        if ( ! $post ) {
            return new WP_Error( 'not_found', __( 'Order not found', 'restaurant-orders' ), [ 'status' => 404 ] );
        }

        return rest_ensure_response(
            [
                'id'            => $post->ID,
                'name'          => $post->post_title,
                'status'        => $post->post_status,
                'restaurant_id' => (int) get_post_meta( $post->ID, 'restaurant_id', true ),
                'items'         => json_decode( (string) get_post_meta( $post->ID, 'items', true ), true ),
            ]
        );
    }

    private function get_item_schema() {
        return [
            'name' => [
                'required' => true,
                'type'     => 'string',
            ],
            'price' => [
                'required' => true,
                'type'     => 'number',
            ],
            'restaurant_id' => [
                'required' => true,
                'type'     => 'integer',
            ],
        ];
    }

    private function get_order_schema() {
        return [
            'name' => [
                'required' => false,
                'type'     => 'string',
            ],
            'status' => [
                'required' => false,
                'type'     => 'string',
            ],
            'items' => [
                'required' => false,
                'type'     => 'array',
            ],
            'restaurant_id' => [
                'required' => true,
                'type'     => 'integer',
            ],
        ];
    }
}
