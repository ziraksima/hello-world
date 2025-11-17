<?php
/**
 * Core plugin class for Restaurant Menu Suite.
 */

defined( 'ABSPATH' ) || exit;

class Restaurant_Menu_Plugin {
    /**
     * Singleton instance.
     *
     * @var Restaurant_Menu_Plugin|null
     */
    protected static $instance = null;

    /**
     * Retrieve the singleton instance.
     *
     * @return Restaurant_Menu_Plugin
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Constructor.
     */
    private function __construct() {
        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Run initialization tasks and hook additional components.
     *
     * @return void
     */
    public function init() {
        // Placeholder for loading admin/public components and other runtime hooks.
    }

    /**
     * Load translation files.
     *
     * @return void
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'restaurant-menu-suite',
            false,
            dirname( plugin_basename( RESTAURANT_MENU_SUITE_PLUGIN_FILE ) ) . '/languages'
        );
    }

    /**
     * Tasks to run on plugin activation.
     *
     * @return void
     */
    public static function activate() {
        // Placeholder for activation tasks such as database setup.
    }

    /**
     * Tasks to run on plugin deactivation.
     *
     * @return void
     */
    public static function deactivate() {
        // Placeholder for deactivation tasks such as cleanup or cache flushing.
    }
}
