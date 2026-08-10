<?php
/**
 * Settings for hybrid provider.
 */

declare( strict_types=1 );

namespace LingerAiBridgeForOpenAiCompatibleApis\Settings;

use WordPress\AiClient\AiClient;
use WordPress\AiClient\Providers\Http\DTO\ApiKeyRequestAuthentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ResponsesSettings {
	private const OPTION_GROUP     = 'linger-ai-bridge-for-openai-compatible-apis-settings';
	private const OPTION_NAME      = 'linger_ai_bridge_for_openai_compatible_apis_settings';
	private const PAGE_SLUG        = 'linger-ai-bridge-for-openai-compatible-apis';
	private const KEY_ENDPOINT_URL = 'endpoint_url';
	private const KEY_MODELS       = 'models';
	private const DEFAULT_ENDPOINT = '';

	public function init(): void {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'register_settings_screen' ] );
	}

	public function register_settings(): void {
		register_setting(
			self::OPTION_GROUP,
			self::OPTION_NAME,
			[
				'type'              => 'array',
				'default'           => self::get_default_settings(),
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
			]
		);

		add_settings_section( 'linger_ai_bridge_for_openai_compatible_apis_main', '', '__return_empty_string', self::PAGE_SLUG );

		add_settings_field(
			self::OPTION_NAME . '_endpoint_url',
			__( 'API Base URL', 'linger-ai-bridge-for-openai-compatible-apis' ),
			[ $this, 'render_endpoint_field' ],
			self::PAGE_SLUG,
			'linger_ai_bridge_for_openai_compatible_apis_main'
		);

		add_settings_field(
			self::OPTION_NAME . '_models',
			__( 'Models', 'linger-ai-bridge-for-openai-compatible-apis' ),
			[ $this, 'render_models_field' ],
			self::PAGE_SLUG,
			'linger_ai_bridge_for_openai_compatible_apis_main'
		);
	}

	public function register_settings_screen(): void {
		add_options_page(
			__( 'Linger AI Bridge for OpenAI-Compatible APIs', 'linger-ai-bridge-for-openai-compatible-apis' ),
			__( 'Linger AI Bridge for OpenAI-Compatible APIs', 'linger-ai-bridge-for-openai-compatible-apis' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_screen' ]
		);
	}

	public function sanitize_settings( $value ): array {
		if ( ! is_array( $value ) ) {
			return self::get_default_settings();
		}

		$endpoint_url = isset( $value[ self::KEY_ENDPOINT_URL ] ) ? $this->sanitize_endpoint_url( (string) $value[ self::KEY_ENDPOINT_URL ] ) : self::DEFAULT_ENDPOINT;

		$models = [];
		if ( isset( $value[ self::KEY_MODELS ] ) && is_array( $value[ self::KEY_MODELS ] ) ) {
			foreach ( $value[ self::KEY_MODELS ] as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}

				$id       = isset( $row['id'] ) ? sanitize_text_field( trim( (string) $row['id'] ) ) : '';
				$label    = isset( $row['label'] ) ? sanitize_text_field( trim( (string) $row['label'] ) ) : '';
				$endpoint = isset( $row['endpoint_type'] ) ? sanitize_text_field( trim( (string) $row['endpoint_type'] ) ) : 'responses';
				$enabled  = ! empty( $row['enabled'] );
				$stream   = ! empty( $row['stream'] );

				if ( '' === $id ) {
					continue;
				}

				if ( 'chat_completions' !== $endpoint ) {
					$endpoint = 'responses';
				}

				$models[] = [
					'id'            => $id,
					'label'         => '' !== $label ? $label : $id,
					'endpoint_type' => $endpoint,
					'enabled'       => $enabled,
					'stream'        => $stream,
				];
			}
		}

		if ( [] === $models ) {
			$models = self::get_default_models();
		}

		return [
			self::KEY_ENDPOINT_URL => rtrim( $endpoint_url, '/' ),
			self::KEY_MODELS       => array_values( $models ),
		];
	}

	private function sanitize_endpoint_url( string $url ): string {
		$url = trim( $url );
		if ( '' === $url ) {
			return '';
		}

		if ( strpos( $url, 'localhost' ) !== false || strpos( $url, '127.0.0.1' ) !== false ) {
			if ( ! preg_match( '/^https?:\/\/.+/', $url ) ) {
				return '';
			}
			return $url;
		}

		$escaped = esc_url_raw( $url );
		if ( '' === $escaped && preg_match( '/^https?:\/\/.+/', $url ) ) {
			return $url;
		}

		return $escaped;
	}

	public function render_screen(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'Configure a shared API base URL and multiple models. Each model can choose either /v1/responses or /v1/chat/completions.', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></p>
			<p>
				<?php
				printf(
					esc_html__( 'Set your API key under %1$sSettings > Connectors%2$s for the Linger AI Bridge for OpenAI-Compatible APIs connector.', 'linger-ai-bridge-for-openai-compatible-apis' ),
					'<a href="' . esc_url( admin_url( 'options-connectors.php' ) ) . '">',
					'</a>'
				);
				?>
			</p>
			<form action="options.php" method="post">
				<?php
				settings_fields( self::OPTION_GROUP );
				do_settings_sections( self::PAGE_SLUG );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	public function render_endpoint_field(): void {
		$settings = self::get_settings();
		?>
		<input type="url" class="regular-text code" name="<?php echo esc_attr( self::OPTION_NAME . '[' . self::KEY_ENDPOINT_URL . ']' ); ?>" value="<?php echo esc_attr( (string) $settings[ self::KEY_ENDPOINT_URL ] ); ?>" />
		<p class="description"><?php esc_html_e( 'Enter the base URL for your OpenAI-compatible service, including its API version path when required.', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></p>
		<?php
	}

	public function render_models_field(): void {
		$settings = self::get_settings();
		$models   = isset( $settings[ self::KEY_MODELS ] ) && is_array( $settings[ self::KEY_MODELS ] ) ? $settings[ self::KEY_MODELS ] : [];
		?>
		<p class="description"><?php esc_html_e( 'Edit the models below. Leave enabled checked for models that should be registered into the WordPress AI Client.', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></p>
		<table class="widefat striped" style="max-width: 1000px; margin-top: 12px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Enabled', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></th>
					<th><?php esc_html_e( 'Model ID', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></th>
					<th><?php esc_html_e( 'Label', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></th>
					<th><?php esc_html_e( 'Endpoint Type', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></th>
					<th><?php esc_html_e( 'Streaming', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></th>
				</tr>
				</thead>
			<tbody>
				<?php foreach ( $models as $index => $model ) : ?>
					<tr>
						<td><input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME . '[' . self::KEY_MODELS . '][' . $index . '][enabled]' ); ?>" value="1" <?php checked( ! empty( $model['enabled'] ) ); ?> /></td>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME . '[' . self::KEY_MODELS . '][' . $index . '][id]' ); ?>" value="<?php echo esc_attr( (string) $model['id'] ); ?>" /></td>
						<td><input type="text" class="regular-text" name="<?php echo esc_attr( self::OPTION_NAME . '[' . self::KEY_MODELS . '][' . $index . '][label]' ); ?>" value="<?php echo esc_attr( (string) $model['label'] ); ?>" /></td>
						<td>
							<select name="<?php echo esc_attr( self::OPTION_NAME . '[' . self::KEY_MODELS . '][' . $index . '][endpoint_type]' ); ?>">
								<option value="responses" <?php selected( (string) $model['endpoint_type'], 'responses' ); ?>>/v1/responses</option>
								<option value="chat_completions" <?php selected( (string) $model['endpoint_type'], 'chat_completions' ); ?>>/v1/chat/completions</option>
							</select>
						</td>
						<td>
							<input type="checkbox" name="<?php echo esc_attr( self::OPTION_NAME . '[' . self::KEY_MODELS . '][' . $index . '][stream]' ); ?>" value="1" <?php checked( ! empty( $model['stream'] ) ); ?> />
							<span class="screen-reader-text"><?php esc_html_e( 'Use a streaming response', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></span>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	public static function get_settings(): array {
		$settings = (array) get_option( self::OPTION_NAME, [] );
		return array_merge( self::get_default_settings(), $settings );
	}

	private static function get_default_settings(): array {
		return [
			self::KEY_ENDPOINT_URL => self::DEFAULT_ENDPOINT,
			self::KEY_MODELS       => self::get_default_models(),
		];
	}

	private static function get_default_models(): array {
		return [
			[
				'id'            => 'responses-model-id',
				'label'         => 'Responses model',
				'endpoint_type' => 'responses',
				'enabled'       => false,
				'stream'        => false,
			],
			[
				'id'            => 'chat-completions-model-id',
				'label'         => 'Chat Completions model',
				'endpoint_type' => 'chat_completions',
				'enabled'       => false,
				'stream'        => false,
			],
		];
	}

	public static function get_models(): array {
		$settings = self::get_settings();
		$models   = isset( $settings[ self::KEY_MODELS ] ) && is_array( $settings[ self::KEY_MODELS ] ) ? $settings[ self::KEY_MODELS ] : [];
		return array_values(
			array_filter(
				$models,
				static function ( $model ) {
					return is_array( $model ) && ! empty( $model['enabled'] ) && ! empty( $model['id'] );
				}
			)
		);
	}

	public static function get_model_config( string $model_id ): ?array {
		foreach ( self::get_models() as $model ) {
			if ( isset( $model['id'] ) && (string) $model['id'] === $model_id ) {
				return $model;
			}
		}
		return null;
	}

	public static function get_endpoint_type_for_model( string $model_id ): string {
		$model = self::get_model_config( $model_id );
		if ( ! is_array( $model ) ) {
			return 'responses';
		}
		return isset( $model['endpoint_type'] ) && 'chat_completions' === $model['endpoint_type'] ? 'chat_completions' : 'responses';
	}

	public static function uses_streaming_for_model( string $model_id ): bool {
		$model = self::get_model_config( $model_id );
		return is_array( $model ) && self::get_endpoint_type_for_model( $model_id ) === 'responses' && ! empty( $model['stream'] );
	}

	public static function get_endpoint_url(): string {
		$settings = self::get_settings();
		$url      = (string) $settings[ self::KEY_ENDPOINT_URL ];
		if ( '' === trim( $url ) ) {
			return '';
		}
		return rtrim( $url, '/' );
	}

	public static function get_api_key(): string {
		if ( class_exists( AiClient::class ) ) {
			$registry = AiClient::defaultRegistry();
			if ( $registry->hasProvider( 'linger_ai_bridge_for_openai_compatible_apis' ) ) {
				$auth = $registry->getProviderRequestAuthentication( 'linger_ai_bridge_for_openai_compatible_apis' );
				if ( $auth instanceof ApiKeyRequestAuthentication ) {
					return (string) $auth->getApiKey();
				}
			}
		}

		$env_key = getenv( 'LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_API_KEY' );
		if ( false === $env_key ) {
			return '';
		}

		return trim( (string) $env_key );
	}
}
