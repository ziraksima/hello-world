<?php
class Restaurant_Orders_Logger {
    const OPTION_KEY = 'restaurant_orders_debug_logging';

    public function is_enabled() {
        return (bool) get_option( self::OPTION_KEY, false );
    }

    public function log( $message ) {
        if ( ! $this->is_enabled() ) {
            return;
        }

        $upload_dir = wp_upload_dir();
        $log_path   = trailingslashit( $upload_dir['basedir'] ) . 'restaurant-orders-debug.log';

        if ( ! file_exists( $upload_dir['basedir'] ) ) {
            wp_mkdir_p( $upload_dir['basedir'] );
        }

        $line = sprintf( "[%s] %s\n", current_time( 'mysql' ), $message );
        file_put_contents( $log_path, $line, FILE_APPEND );
    }
}
