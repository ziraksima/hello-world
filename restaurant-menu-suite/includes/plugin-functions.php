<?php
/**
 * General helper functions for Restaurant Menu Suite.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve the plugin directory URL.
 *
 * @return string
 */
function restaurant_menu_suite_url() {
    return plugin_dir_url( RESTAURANT_MENU_SUITE_PLUGIN_FILE );
}

/**
 * Retrieve the plugin directory path.
 *
 * @return string
 */
function restaurant_menu_suite_path() {
    return RESTAURANT_MENU_SUITE_PLUGIN_DIR;
}
