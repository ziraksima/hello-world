<?php
/**
 * Admin-specific functionality for Restaurant Menu Suite.
 */

defined( 'ABSPATH' ) || exit;

class Restaurant_Menu_Admin {
    /**
     * Register admin-side hooks.
     *
     * @return void
     */
    public function register_hooks() {
        add_action( 'admin_menu', array( $this, 'register_menu_page' ) );
        add_filter(
            'plugin_action_links_' . plugin_basename( RESTAURANT_MENU_SUITE_PLUGIN_FILE ),
            array( $this, 'add_plugin_action_links' )
        );
    }

    /**
     * Add the main admin menu page for the plugin.
     *
     * @return void
     */
    public function register_menu_page() {
        add_menu_page(
            __( 'Restaurant Menu Suite', 'restaurant-menu-suite' ),
            __( 'Restaurant Menu Suite', 'restaurant-menu-suite' ),
            'manage_options',
            'restaurant-menu-suite',
            array( $this, 'render_page' ),
            'dashicons-carrot',
            26
        );
    }

    /**
     * Add quick access links on the Plugins screen.
     *
     * @param array $links Existing plugin action links.
     *
     * @return array
     */
    public function add_plugin_action_links( $links ) {
        $menu_url   = admin_url( 'admin.php?page=restaurant-menu-suite' );
        $settings   = sprintf(
            '<a href="%1$s">%2$s</a>',
            esc_url( $menu_url ),
            esc_html__( 'Settings', 'restaurant-menu-suite' )
        );
        $docs       = sprintf(
            '<a href="%1$s" target="_blank" rel="noopener noreferrer">%2$s</a>',
            esc_url( 'https://example.com/restaurant-menu-suite' ),
            esc_html__( 'Docs', 'restaurant-menu-suite' )
        );

        array_unshift( $links, $settings, $docs );

        return $links;
    }

    /**
     * Render the admin dashboard page content.
     *
     * @return void
     */
    public function render_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Restaurant Menu Suite', 'restaurant-menu-suite' ); ?></h1>
            <p><?php esc_html_e( 'Manage your restaurant menus and settings from this dashboard.', 'restaurant-menu-suite' ); ?></p>
        </div>
        <?php
    }
}
