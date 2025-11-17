<?php
/**
 * Plugin Name: Restaurant Orders
 * Description: Provides RESTful CRUD for restaurant menu items and orders with debug logging.
 * Version: 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/includes/class-restaurant-orders-logger.php';
require_once __DIR__ . '/includes/class-restaurant-orders-rest.php';
require_once __DIR__ . '/includes/class-restaurant-orders-settings.php';

class Restaurant_Orders {
    const ITEM_CPT  = 'restaurant_item';
    const ORDER_CPT = 'restaurant_order';

    /** @var Restaurant_Orders_Logger */
    public $logger;

    /** @var Restaurant_Orders_REST */
    public $rest_controller;

    public function __construct() {
        $this->logger          = new Restaurant_Orders_Logger();
        $this->rest_controller = new Restaurant_Orders_REST( $this, $this->logger );

        add_action( 'init', [ $this, 'register_post_types' ] );
        add_action( 'rest_api_init', [ $this->rest_controller, 'register_routes' ] );

        add_action( 'transition_post_status', [ $this, 'handle_order_status_change' ], 10, 3 );

        add_action( 'plugins_loaded', [ 'Restaurant_Orders_Settings', 'init' ] );
    }

    public function register_post_types() {
        register_post_type(
            self::ITEM_CPT,
            [
                'label'        => __( 'Restaurant Item', 'restaurant-orders' ),
                'public'       => false,
                'show_ui'      => true,
                'show_in_menu' => true,
                'supports'     => [ 'title', 'custom-fields' ],
            ]
        );

        register_post_type(
            self::ORDER_CPT,
            [
                'label'        => __( 'Restaurant Order', 'restaurant-orders' ),
                'public'       => false,
                'show_ui'      => true,
                'show_in_menu' => true,
                'supports'     => [ 'title', 'custom-fields' ],
            ]
        );
    }

    public function handle_order_status_change( $new_status, $old_status, $post ) {
        if ( self::ORDER_CPT !== $post->post_type || $new_status === $old_status ) {
            return;
        }

        $this->logger->log( sprintf( 'Order %d status changed: %s → %s', $post->ID, $old_status, $new_status ) );
    }
}

function restaurant_orders() {
    static $instance;
    if ( ! isset( $instance ) ) {
        $instance = new Restaurant_Orders();
    }

    return $instance;
}

restaurant_orders();
