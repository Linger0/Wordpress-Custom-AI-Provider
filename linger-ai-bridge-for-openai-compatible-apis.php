<?php
/**
 * Plugin Name:       Linger AI Bridge for OpenAI-Compatible APIs
 * Description:       AI provider for the WordPress AI Client with configurable Responses and Chat Completions endpoints.
 * Requires at least: 7.0
 * Requires PHP:      7.4
 * Version:           0.1.1
 * Author:            Linger0
 * Author URI:        https://github.com/Linger0
 * License:           GPL-2.0
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       linger-ai-bridge-for-openai-compatible-apis
 */

declare( strict_types=1 );

namespace LingerAiBridgeForOpenAiCompatibleApis;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_VERSION', '0.1.1' );
define( 'LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_MIN_PHP_VERSION', '7.4' );
define( 'LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_MIN_WP_VERSION', '7.0' );
define( 'LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_PLUGIN_FILE', __FILE__ );

require_once LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_PLUGIN_DIR . 'src/autoload.php';

function linger_ai_bridge_for_openai_compatible_apis_requirement_notice( string $message ): void {
	if ( ! is_admin() ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p><?php echo wp_kses_post( $message ); ?></p>
	</div>
	<?php
}

function linger_ai_bridge_for_openai_compatible_apis_check_php_version(): bool {
	if ( version_compare( phpversion(), LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_MIN_PHP_VERSION, '<' ) ) {
		add_action(
			'admin_notices',
			static function () {
				linger_ai_bridge_for_openai_compatible_apis_requirement_notice(
					sprintf(
						__( 'The Linger AI Bridge for OpenAI-Compatible APIs plugin requires PHP version %1$s or higher. You are running PHP version %2$s.', 'linger-ai-bridge-for-openai-compatible-apis' ),
						LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_MIN_PHP_VERSION,
						PHP_VERSION
					)
				);
			}
		);

		return false;
	}

	return true;
}

function linger_ai_bridge_for_openai_compatible_apis_check_wp_version(): bool {
	if ( ! is_wp_version_compatible( LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_MIN_WP_VERSION ) ) {
		add_action(
			'admin_notices',
			static function () {
				global $wp_version;
				linger_ai_bridge_for_openai_compatible_apis_requirement_notice(
					sprintf(
						__( 'The Linger AI Bridge for OpenAI-Compatible APIs plugin requires WordPress version %1$s or higher. You are running WordPress version %2$s.', 'linger-ai-bridge-for-openai-compatible-apis' ),
						LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_MIN_WP_VERSION,
						$wp_version
					)
				);
			}
		);

		return false;
	}

	return true;
}

function linger_ai_bridge_for_openai_compatible_apis_check_ai_client(): bool {
	if ( ! class_exists( \WordPress\AiClient\AiClient::class ) ) {
		add_action(
			'admin_notices',
			static function () {
				linger_ai_bridge_for_openai_compatible_apis_requirement_notice(
					__( 'The Linger AI Bridge for OpenAI-Compatible APIs plugin requires the WordPress AI Client to be available in WordPress 7.0+.', 'linger-ai-bridge-for-openai-compatible-apis' )
				);
			}
		);

		return false;
	}

	return true;
}

function linger_ai_bridge_for_openai_compatible_apis_load(): void {
	if ( ! linger_ai_bridge_for_openai_compatible_apis_check_php_version() || ! linger_ai_bridge_for_openai_compatible_apis_check_wp_version() || ! linger_ai_bridge_for_openai_compatible_apis_check_ai_client() ) {
		return;
	}

	$plugin = new Plugin();
	$plugin->init();
}

linger_ai_bridge_for_openai_compatible_apis_load();
