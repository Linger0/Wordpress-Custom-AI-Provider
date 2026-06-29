<?php
/**
 * PSR-4 autoloader for AI Provider for Any OpenAI-Compatible Provider.
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

spl_autoload_register(
	static function ( string $class_name ): void {
		$prefix   = 'AiProviderForAnyOpenAiCompatibleProvider\\';
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
