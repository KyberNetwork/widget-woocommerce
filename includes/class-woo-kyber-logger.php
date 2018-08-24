<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Log all things!
 *
 */
class Woo_Kyber_Logger{

	public static $logger;
	const WC_LOG_FILENAME = 'woo-gateway-kyber';

	/**
	 * Utilize WC logger class
	 *
	 */
	public static function log( $message ) {
		if ( ! class_exists( 'WC_Logger' ) ) {
			return;
		}

		if ( apply_filters( 'wc_kyber_logging', true, $message ) ) {
			if ( empty( self::$logger ) ) {
				if ( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
					self::$logger = wc_get_logger();
				} else {
					self::$logger = new WC_Logger();
				}
			}

			$log_entry .= '====Start Log====' . "\n" . $message . "\n" . '====End Log====' . "\n\n";

			if ( version_compare( WC_VERSION, '3.0.0', '>=' ) ) {
				self::$logger->debug( $log_entry, array( 'source' => self::WC_LOG_FILENAME ) );
			} else {
				self::$logger->add( self::WC_LOG_FILENAME, $log_entry );
			}
		}
	}
}
