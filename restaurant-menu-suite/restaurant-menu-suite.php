<?php
/**
 * Plugin Name: Restaurant Menu Suite
 * Plugin URI:  https://example.com/restaurant-menu-suite
 * Description: A foundational plugin structure for managing restaurant menus with dedicated admin and public interfaces.
 * Version:     1.0.0
 * Author:      Your Name
 * Author URI:  https://example.com
 * Text Domain: restaurant-menu-suite
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

defined( 'ABSPATH' ) || exit;

define( 'RESTAURANT_MENU_SUITE_VERSION', '1.0.0' );
define( 'RESTAURANT_MENU_SUITE_PLUGIN_FILE', __FILE__ );
define( 'RESTAURANT_MENU_SUITE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

restaurant_menu_suite_bootstrap();

/**
 * Load the autoloader, required functions, and boot the core plugin class.
 *
 * @return void
 */
function restaurant_menu_suite_bootstrap() {
    $includes_dir = RESTAURANT_MENU_SUITE_PLUGIN_DIR . 'includes/';

    require_once $includes_dir . 'class-restaurant-menu-autoloader.php';
    \Restaurant_Menu_Autoloader::register( $includes_dir );

    require_once $includes_dir . 'plugin-functions.php';
    require_once $includes_dir . 'class-restaurant-menu-plugin.php';

    \Restaurant_Menu_Plugin::instance();
}

register_activation_hook( __FILE__, array( 'Restaurant_Menu_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Restaurant_Menu_Plugin', 'deactivate' ) );
