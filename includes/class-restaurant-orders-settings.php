<?php
class Restaurant_Orders_Settings {
    const OPTION_GROUP = 'restaurant_orders_options';
    const OPTION_NAME  = Restaurant_Orders_Logger::OPTION_KEY;

    public static function init() {
        add_action( 'admin_menu', [ __CLASS__, 'register_settings_page' ] );
        add_action( 'admin_init', [ __CLASS__, 'register_settings' ] );
    }

    public static function register_settings_page() {
        add_options_page(
            __( 'Restaurant Orders', 'restaurant-orders' ),
            __( 'Restaurant Orders', 'restaurant-orders' ),
            'manage_options',
            'restaurant-orders',
            [ __CLASS__, 'render_settings_page' ]
        );
    }

    public static function register_settings() {
        register_setting( self::OPTION_GROUP, self::OPTION_NAME, [ 'type' => 'boolean' ] );

        add_settings_section(
            'restaurant_orders_logging',
            __( 'Debug Logging', 'restaurant-orders' ),
            '__return_false',
            'restaurant-orders'
        );

        add_settings_field(
            'restaurant_orders_logging_enabled',
            __( 'Enable debug log', 'restaurant-orders' ),
            [ __CLASS__, 'render_logging_toggle' ],
            'restaurant-orders',
            'restaurant_orders_logging'
        );
    }

    public static function render_logging_toggle() {
        $value = (bool) get_option( self::OPTION_NAME, false );
        ?>
        <label for="restaurant-orders-debug-log">
            <input type="checkbox" id="restaurant-orders-debug-log" name="<?php echo esc_attr( self::OPTION_NAME ); ?>" value="1" <?php checked( $value ); ?> />
            <?php esc_html_e( 'Write order events to the debug log', 'restaurant-orders' ); ?>
        </label>
        <?php
    }

    public static function render_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Restaurant Orders Settings', 'restaurant-orders' ); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields( self::OPTION_GROUP );
                do_settings_sections( 'restaurant-orders' );
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
