<?php
/**
 * Plugin Name:       Custom AI Responses Provider
 * Plugin URI:        https://github.com/Linger0/Wordpress-Custom-AI-Provider
 * Description:       AI provider for the WordPress AI Client with configurable Responses and Chat Completions endpoints.
 * Requires at least: 7.0
 * Requires PHP:      7.4
 * Version:           0.1.0
 * Author:            Linger0
 * Author URI:        https://github.com/Linger0
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       my-openai-responses-provider
 */

declare( strict_types=1 );

namespace MyOpenAiResponsesProvider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MY_OPENAI_RESPONSES_PROVIDER_VERSION', '0.1.0' );
define( 'MY_OPENAI_RESPONSES_PROVIDER_MIN_PHP_VERSION', '7.4' );
define( 'MY_OPENAI_RESPONSES_PROVIDER_MIN_WP_VERSION', '7.0' );
define( 'MY_OPENAI_RESPONSES_PROVIDER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MY_OPENAI_RESPONSES_PROVIDER_PLUGIN_FILE', __FILE__ );

require_once MY_OPENAI_RESPONSES_PROVIDER_PLUGIN_DIR . 'src/autoload.php';

function requirement_notice( string $message ): void {
	if ( ! is_admin() ) {
		return;
	}
	?>
	<div class="notice notice-error">
		<p><?php echo wp_kses_post( $message ); ?></p>
	</div>
	<?php
}

function check_php_version(): bool {
	if ( version_compare( phpversion(), MY_OPENAI_RESPONSES_PROVIDER_MIN_PHP_VERSION, '<' ) ) {
		add_action(
			'admin_notices',
			static function () {
				requirement_notice(
					sprintf(
						__( 'The Custom AI Responses Provider plugin requires PHP version %1$s or higher. You are running PHP version %2$s.', 'my-openai-responses-provider' ),
						MY_OPENAI_RESPONSES_PROVIDER_MIN_PHP_VERSION,
						PHP_VERSION
					)
				);
			}
		);

		return false;
	}

	return true;
}

function check_wp_version(): bool {
	if ( ! is_wp_version_compatible( MY_OPENAI_RESPONSES_PROVIDER_MIN_WP_VERSION ) ) {
		add_action(
			'admin_notices',
			static function () {
				global $wp_version;
				requirement_notice(
					sprintf(
						__( 'The Custom AI Responses Provider plugin requires WordPress version %1$s or higher. You are running WordPress version %2$s.', 'my-openai-responses-provider' ),
						MY_OPENAI_RESPONSES_PROVIDER_MIN_WP_VERSION,
						$wp_version
					)
				);
			}
		);

		return false;
	}

	return true;
}

function check_ai_client(): bool {
	if ( ! class_exists( \WordPress\AiClient\AiClient::class ) ) {
		add_action(
			'admin_notices',
			static function () {
				requirement_notice(
					__( 'The Custom AI Responses Provider plugin requires the WordPress AI Client to be available in WordPress 7.0+.', 'my-openai-responses-provider' )
				);
			}
		);

		return false;
	}

	return true;
}

function load(): void {
	if ( ! check_php_version() || ! check_wp_version() || ! check_ai_client() ) {
		return;
	}

	$plugin = new Plugin();
	$plugin->init();
}

load();
