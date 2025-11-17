<?php
/**
 * Plugin Name: Restaurant Manager
 * Description: Custom restaurant type with menu and dashboard endpoints plus multi-restaurant guards.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Restaurant_Manager {
    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init', [$this, 'register_restaurant_type']);
        add_action('init', [$this, 'add_rewrite_rules']);
        add_filter('query_vars', [$this, 'register_query_vars']);
        add_action('template_redirect', [$this, 'handle_custom_routes']);
        add_action('pre_get_posts', [$this, 'restrict_queries_by_restaurant']);
        add_filter('wp_insert_post_data', [$this, 'enforce_restaurant_scope_on_save'], 10, 2);
        add_action('save_post', [$this, 'assign_restaurant_meta_on_save'], 10, 3);
        add_action('rest_request_before_callbacks', [$this, 'guard_restaurant_rest_requests'], 10, 3);
        register_activation_hook(__FILE__, ['Restaurant_Manager', 'activate']);
    }

    public static function activate() {
        self::get_instance()->register_restaurant_type();
        self::get_instance()->add_rewrite_rules();
        flush_rewrite_rules();
    }

    public function register_restaurant_type() {
        register_post_type('restaurant', [
            'label' => __('Restaurants', 'restaurant-manager'),
            'public' => true,
            'show_in_rest' => true,
            'supports' => ['title', 'thumbnail'],
            'rewrite' => ['slug' => 'restaurant'],
        ]);

        register_post_meta('restaurant', 'branding_settings', [
            'type' => 'object',
            'description' => __('Branding settings for the restaurant', 'restaurant-manager'),
            'single' => true,
            'show_in_rest' => true,
            'sanitize_callback' => [$this, 'sanitize_branding_settings'],
            'auth_callback' => [$this, 'can_edit_restaurant'],
        ]);

        register_post_meta('restaurant', 'is_active', [
            'type' => 'boolean',
            'description' => __('Whether the restaurant is active', 'restaurant-manager'),
            'single' => true,
            'show_in_rest' => true,
            'default' => true,
            'auth_callback' => [$this, 'can_edit_restaurant'],
        ]);
    }

    public function sanitize_branding_settings($value) {
        if (!is_array($value)) {
            return [];
        }

        return array_map(function ($item) {
            return is_string($item) ? sanitize_text_field($item) : $item;
        }, $value);
    }

    public function can_edit_restaurant($allowed, $meta_key, $post_id) {
        return current_user_can('edit_post', $post_id);
    }

    public function add_rewrite_rules() {
        add_rewrite_rule('^restaurant/([^/]+)/menu/?$', 'index.php?post_type=restaurant&name=$matches[1]&restaurant_view=menu', 'top');
        add_rewrite_rule('^restaurant/([^/]+)/dashboard/?$', 'index.php?post_type=restaurant&name=$matches[1]&restaurant_view=dashboard', 'top');
    }

    public function register_query_vars($vars) {
        $vars[] = 'restaurant_view';
        $vars[] = 'restaurant_slug';
        return $vars;
    }

    public function handle_custom_routes() {
        $view = get_query_var('restaurant_view');
        if (!$view) {
            return;
        }

        $restaurant = get_queried_object();
        if (!$restaurant || $restaurant->post_type !== 'restaurant') {
            return;
        }

        if (!$this->user_has_access_to_restaurant($restaurant->ID)) {
            wp_die(__('You do not have access to this restaurant.', 'restaurant-manager'), __('Unauthorized', 'restaurant-manager'), 403);
        }

        if ($view === 'menu') {
            $this->render_menu_view($restaurant);
            exit;
        }

        if ($view === 'dashboard') {
            $this->render_dashboard_view($restaurant);
            exit;
        }
    }

    protected function render_menu_view($restaurant) {
        status_header(200);
        echo '<h1>' . esc_html(get_the_title($restaurant)) . '</h1>';
        echo '<p>' . esc_html__('Menu goes here.', 'restaurant-manager') . '</p>';
    }

    protected function render_dashboard_view($restaurant) {
        if (!current_user_can('edit_post', $restaurant->ID)) {
            wp_die(__('You do not have permission to view this dashboard.', 'restaurant-manager'), __('Unauthorized', 'restaurant-manager'), 403);
        }

        status_header(200);
        echo '<h1>' . esc_html(get_the_title($restaurant)) . ' ' . esc_html__('Dashboard', 'restaurant-manager') . '</h1>';
        echo '<p>' . esc_html__('Restricted dashboard view for staff.', 'restaurant-manager') . '</p>';
    }

    public function restrict_queries_by_restaurant($query) {
        if (is_admin() && !$query->is_main_query()) {
            return;
        }

        $current_restaurant_id = $this->get_current_restaurant_id();
        if (!$current_restaurant_id) {
            return;
        }

        $meta_query = (array) $query->get('meta_query', []);

        $meta_query[] = [
            'key' => 'restaurant_id',
            'value' => $current_restaurant_id,
            'compare' => '=',
        ];

        $query->set('meta_query', $meta_query);
    }

    public function enforce_restaurant_scope_on_save($data, $postarr) {
        $current_restaurant_id = $this->get_current_restaurant_id();

        if ($current_restaurant_id && (!isset($postarr['ID']) || $postarr['post_type'] !== 'restaurant')) {
            $data['meta_input']['restaurant_id'] = $current_restaurant_id;
        }

        if ($data['post_type'] === 'restaurant' && $current_restaurant_id && $postarr['ID'] && !$this->user_has_access_to_restaurant($postarr['ID'])) {
            wp_die(__('You cannot modify another restaurant.', 'restaurant-manager'));
        }

        return $data;
    }

    public function assign_restaurant_meta_on_save($post_id, $post, $update) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        $current_restaurant_id = $this->get_current_restaurant_id();
        if ($current_restaurant_id && $post->post_type !== 'restaurant') {
            update_post_meta($post_id, 'restaurant_id', $current_restaurant_id);
        }
    }

    public function guard_restaurant_rest_requests($response, $handler, $request) {
        $current_restaurant_id = $this->get_current_restaurant_id();
        if (!$current_restaurant_id) {
            return $response;
        }

        $request_restaurant_id = (int) $request->get_param('restaurant_id');
        if ($request_restaurant_id && $request_restaurant_id !== $current_restaurant_id) {
            return new WP_Error('restaurant_scope_mismatch', __('Restaurant access denied.', 'restaurant-manager'), ['status' => 403]);
        }

        return $response;
    }

    protected function user_has_access_to_restaurant($restaurant_id) {
        $current_restaurant_id = $this->get_current_restaurant_id();
        if (!$current_restaurant_id) {
            return current_user_can('edit_post', $restaurant_id);
        }

        return (int) $current_restaurant_id === (int) $restaurant_id;
    }

    protected function get_current_restaurant_id() {
        if (isset($_GET['restaurant_id'])) {
            return absint($_GET['restaurant_id']);
        }

        $user = wp_get_current_user();
        $assigned = get_user_meta($user->ID, 'restaurant_id', true);
        return $assigned ? absint($assigned) : 0;
    }
}

Restaurant_Manager::get_instance();
