<?php
/**
 * Main plugin class.
 */

declare( strict_types=1 );

namespace LingerAiBridgeForOpenAiCompatibleApis;

use LingerAiBridgeForOpenAiCompatibleApis\Provider\ResponsesProvider;
use LingerAiBridgeForOpenAiCompatibleApis\Settings\ResponsesSettings;
use WordPress\AiClient\AiClient;
use WordPress\AiClient\Providers\Http\DTO\ApiKeyRequestAuthentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {
	public function init(): void {
		add_action( 'init', [ $this, 'register_provider' ], 5 );
		add_action( 'init', [ $this, 'register_fallback_auth' ], 15 );
		add_action( 'init', [ $this, 'initialize_settings' ] );
		add_action( 'init', [ $this, 'allow_localhost_requests' ], 20 );
		add_action( 'wp_loaded', [ $this, 'setup_http_request_filters' ] );
		add_filter( 'plugin_action_links_' . plugin_basename( LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_PLUGIN_FILE ), [ $this, 'plugin_action_links' ] );
	}

	public function register_provider(): void {
		if ( ! class_exists( AiClient::class ) ) {
			return;
		}

		$registry = AiClient::defaultRegistry();
		if ( $registry->hasProvider( ResponsesProvider::class ) ) {
			return;
		}

		$registry->registerProvider( ResponsesProvider::class );
	}

	public function register_fallback_auth(): void {
		if ( ! class_exists( AiClient::class ) ) {
			return;
		}

		$registry = AiClient::defaultRegistry();
		if ( ! $registry->hasProvider( 'linger_ai_bridge_for_openai_compatible_apis' ) ) {
			return;
		}

		$auth = $registry->getProviderRequestAuthentication( 'linger_ai_bridge_for_openai_compatible_apis' );
		if ( null !== $auth ) {
			return;
		}

		$env_key = (string) getenv( 'LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_API_KEY' );
		$registry->setProviderRequestAuthentication(
			'linger_ai_bridge_for_openai_compatible_apis',
			new ApiKeyRequestAuthentication( $env_key )
		);
	}

	public function initialize_settings(): void {
		$settings = new ResponsesSettings();
		$settings->init();
	}

	public function allow_localhost_for_linger_ai_bridge_for_openai_compatible_apis( bool $external, string $host, string $url ): bool {
		if ( $external ) {
			return true;
		}

		if ( 'localhost' !== $host && '127.0.0.1' !== $host && '::1' !== $host ) {
			return $external;
		}

		$endpoint = ResponsesSettings::get_endpoint_url();
		if ( empty( $endpoint ) ) {
			return $external;
		}

		if ( 0 === strpos( $url, rtrim( $endpoint, '/' ) ) ) {
			return true;
		}

		return $external;
	}

	public function setup_http_request_filters(): void {
		add_filter( 'http_request_host_is_external', [ $this, 'allow_localhost_for_linger_ai_bridge_for_openai_compatible_apis' ], 10, 3 );
		add_filter( 'http_request_args', [ $this, 'modify_http_request_args' ], 10, 2 );
	}

	public function modify_http_request_args( array $args, string $url ): array {
		$endpoint = ResponsesSettings::get_endpoint_url();
		if ( empty( $endpoint ) ) {
			return $args;
		}

		if ( 0 !== strpos( $url, rtrim( $endpoint, '/' ) ) ) {
			return $args;
		}

		$args['reject_unsafe_urls'] = false;
		return $args;
	}

	public function allow_localhost_requests(): void {
		add_filter( 'http_request_host_is_external', [ $this, 'allow_localhost_for_linger_ai_bridge_for_openai_compatible_apis' ], 10, 3 );
	}

	public function plugin_action_links( array $links ): array {
		$settings_link = sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'options-general.php?page=linger-ai-bridge-for-openai-compatible-apis' ),
			esc_html__( 'Settings', 'linger-ai-bridge-for-openai-compatible-apis' )
		);

		array_unshift( $links, $settings_link );
		return $links;
	}
}
