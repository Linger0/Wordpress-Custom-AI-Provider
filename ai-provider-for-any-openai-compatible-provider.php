<?php
/**
 * Plugin Name:       AI Provider for Any OpenAI-Compatible Provider
 * Description:       AI provider for the WordPress AI Client with configurable Responses and Chat Completions endpoints.
 * Requires at least: 7.0
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:            Linger0
 * Author URI:        https://github.com/Linger0
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ai-provider-for-any-openai-compatible-provider
 */

declare( strict_types=1 );

namespace AiProviderForAnyOpenAiCompatibleProvider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_VERSION', '0.1.0' );
define( 'AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_MIN_PHP_VERSION', '7.4' );
define( 'AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_MIN_WP_VERSION', '7.0' );
define( 'AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_PLUGIN_FILE', __FILE__ );

require_once AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_PLUGIN_DIR . 'src/autoload.php';

function ai_provider_for_any_openai_compatible_provider_requirement_notice( string $message ): void {
	if ( ! is_admin() ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p><?php echo wp_kses_post( $message ); ?></p>
	</div>
	<?php
}

function ai_provider_for_any_openai_compatible_provider_check_php_version(): bool {
	if ( version_compare( phpversion(), AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_MIN_PHP_VERSION, '<' ) ) {
		add_action(
			'admin_notices',
			static function () {
				ai_provider_for_any_openai_compatible_provider_requirement_notice(
					sprintf(
						__( 'The AI Provider for Any OpenAI-Compatible Provider plugin requires PHP version %1$s or higher. You are running PHP version %2$s.', 'ai-provider-for-any-openai-compatible-provider' ),
						AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_MIN_PHP_VERSION,
						PHP_VERSION
					)
				);
			}
		);

		return false;
	}

	return true;
}

function ai_provider_for_any_openai_compatible_provider_check_wp_version(): bool {
	if ( ! is_wp_version_compatible( AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_MIN_WP_VERSION ) ) {
		add_action(
			'admin_notices',
			static function () {
				global $wp_version;
				ai_provider_for_any_openai_compatible_provider_requirement_notice(
					sprintf(
						__( 'The AI Provider for Any OpenAI-Compatible Provider plugin requires WordPress version %1$s or higher. You are running WordPress version %2$s.', 'ai-provider-for-any-openai-compatible-provider' ),
						AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_MIN_WP_VERSION,
						$wp_version
					)
				);
			}
		);

		return false;
	}

	return true;
}

function ai_provider_for_any_openai_compatible_provider_check_ai_client(): bool {
	if ( ! class_exists( \WordPress\AiClient\AiClient::class ) ) {
		add_action(
			'admin_notices',
			static function () {
				ai_provider_for_any_openai_compatible_provider_requirement_notice(
					__( 'The AI Provider for Any OpenAI-Compatible Provider plugin requires the WordPress AI Client to be available in WordPress 7.0+.', 'ai-provider-for-any-openai-compatible-provider' )
				);
			}
		);

		return false;
	}

	return true;
}

function ai_provider_for_any_openai_compatible_provider_load(): void {
	if ( ! ai_provider_for_any_openai_compatible_provider_check_php_version() || ! ai_provider_for_any_openai_compatible_provider_check_wp_version() || ! ai_provider_for_any_openai_compatible_provider_check_ai_client() ) {
		return;
	}

	$plugin = new Plugin();
	$plugin->init();
}

ai_provider_for_any_openai_compatible_provider_load();
