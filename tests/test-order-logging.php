<?php
class Restaurant_Orders_Logging_Test extends WP_UnitTestCase {
    private $log_path;

    public function setUp(): void {
        parent::setUp();
        wp_set_current_user( $this->factory->user->create( [ 'role' => 'administrator' ] ) );
        update_option( Restaurant_Orders_Logger::OPTION_KEY, true );

        $upload_dir     = wp_upload_dir();
        $this->log_path = trailingslashit( $upload_dir['basedir'] ) . 'restaurant-orders-debug.log';
        if ( file_exists( $this->log_path ) ) {
            unlink( $this->log_path );
        }
    }

    public function test_logging_flow() {
        $create = new WP_REST_Request( 'POST', '/restaurant/v1/orders' );
        $create->set_param( 'restaurant_id', 99 );
        $create->set_param( 'items', [ 'pizza' ] );
        $order = rest_get_server()->dispatch( $create )->get_data();

        $update = new WP_REST_Request( 'PUT', '/restaurant/v1/orders/' . $order['id'] );
        $update->set_param( 'status', 'completed' );
        rest_get_server()->dispatch( $update );

        $notify = new WP_REST_Request( 'POST', '/restaurant/v1/orders/' . $order['id'] . '/notify' );
        $notify->set_param( 'result', 'failure' );
        rest_get_server()->dispatch( $notify );

        $this->assertFileExists( $this->log_path );
        $contents = file_get_contents( $this->log_path );
        $this->assertStringContainsString( 'Order ' . $order['id'] . ' created', $contents );
        $this->assertStringContainsString( 'status changed', $contents );
        $this->assertStringContainsString( 'Notification failed', $contents );
    }
}
