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
