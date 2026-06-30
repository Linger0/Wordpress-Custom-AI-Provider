<?php
/**
 * PSR-4 autoloader for Linger AI Bridge for OpenAI-Compatible APIs.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix   = 'LingerAiBridgeForOpenAiCompatibleApis\\';
		$base_dir = __DIR__ . '/';

		$len = strlen( $prefix );
		if ( strncmp( $class_name, $prefix, $len ) !== 0 ) {
			return;
		}

		$relative_class = substr( $class_name, $len );
		$file           = $base_dir . str_replace( '\\', '/', $relative_class ) . '.php';

		if ( ! file_exists( $file ) ) {
			return;
		}

		require $file;
	}
);
