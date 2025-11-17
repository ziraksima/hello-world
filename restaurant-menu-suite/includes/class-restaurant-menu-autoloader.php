<?php
/**
 * Simple autoloader for the Restaurant Menu Suite plugin.
 */

defined( 'ABSPATH' ) || exit;

class Restaurant_Menu_Autoloader {
    /**
     * Base path for includes.
     *
     * @var string
     */
    protected static $includes_dir;

    /**
     * Register the autoloader callback.
     *
     * @param string $includes_dir Base directory for include files.
     *
     * @return void
     */
    public static function register( $includes_dir ) {
        self::$includes_dir = trailingslashit( $includes_dir );
        spl_autoload_register( array( __CLASS__, 'autoload' ) );
    }

    /**
     * Load class files matching the Restaurant_Menu_ prefix.
     *
     * @param string $class Class name being requested.
     *
     * @return void
     */
    public static function autoload( $class ) {
        if ( 0 !== strpos( $class, 'Restaurant_Menu_' ) ) {
            return;
        }

        $filename = 'class-' . strtolower( str_replace( '_', '-', $class ) ) . '.php';
        $filepath = self::$includes_dir . $filename;

        if ( file_exists( $filepath ) ) {
            require_once $filepath;
        }
    }
}
