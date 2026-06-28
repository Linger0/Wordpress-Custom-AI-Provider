<?php
/**
 * Settings for hybrid provider.
 */

declare( strict_types=1 );

namespace MyOpenAiResponsesProvider\Settings;

use WordPress\AiClient\AiClient;
use WordPress\AiClient\Providers\Http\DTO\ApiKeyRequestAuthentication;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ResponsesSettings {
	private const OPTION_GROUP     = 'my-openai-responses-provider-settings';
	private const OPTION_NAME      = 'my_openai_responses_provider_settings';
	private const PAGE_SLUG        = 'my-openai-responses-provider';
	private const KEY_ENDPOINT_URL = 'endpoint_url';
	private const KEY_MODELS       = 'models';
	private const DEFAULT_ENDPOINT = 'https://api.openai.com/v1';

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

		add_settings_section( 'my_openai_responses_provider_main', '', '__return_empty_string', self::PAGE_SLUG );

		add_settings_field(
			self::OPTION_NAME . '_endpoint_url',
			__( 'API Base URL', 'my-openai-responses-provider' ),
			[ $this, 'render_endpoint_field' ],
			self::PAGE_SLUG,
			'my_openai_responses_provider_main'
		);

		add_settings_field(
			self::OPTION_NAME . '_models',
			__( 'Models', 'my-openai-responses-provider' ),
			[ $this, 'render_models_field' ],
			self::PAGE_SLUG,
			'my_openai_responses_provider_main'
		);
	}

	public function register_settings_screen(): void {
		add_options_page(
			__( 'Custom AI Provider', 'my-openai-responses-provider' ),
			__( 'Custom AI Provider', 'my-openai-responses-provider' ),
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
		if ( '' === $endpoint_url ) {
			$endpoint_url = self::DEFAULT_ENDPOINT;
		}

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
			<p><?php esc_html_e( 'Configure a shared API base URL and multiple models. Each model can choose either /v1/responses or /v1/chat/completions.', 'my-openai-responses-provider' ); ?></p>
			<p>
				<?php
				printf(
					esc_html__( 'Set your API key under %1$sSettings > Connectors%2$s for the Custom AI Provider connector.', 'my-openai-responses-provider' ),
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
		<p class="description"><?php esc_html_e( 'Example: https://api.openai.com/v1 or your own compatible gateway base URL.', 'my-openai-responses-provider' ); ?></p>
		<?php
	}

	public function render_models_field(): void {
		$settings = self::get_settings();
		$models   = isset( $settings[ self::KEY_MODELS ] ) && is_array( $settings[ self::KEY_MODELS ] ) ? $settings[ self::KEY_MODELS ] : [];
		?>
		<p class="description"><?php esc_html_e( 'Edit the models below. Leave enabled checked for models that should be registered into the WordPress AI Client.', 'my-openai-responses-provider' ); ?></p>
		<table class="widefat striped" style="max-width: 1000px; margin-top: 12px;">
			<thead>
				<tr>
					<th><?php esc_html_e( 'Enabled', 'my-openai-responses-provider' ); ?></th>
					<th><?php esc_html_e( 'Model ID', 'my-openai-responses-provider' ); ?></th>
					<th><?php esc_html_e( 'Label', 'my-openai-responses-provider' ); ?></th>
					<th><?php esc_html_e( 'Endpoint Type', 'my-openai-responses-provider' ); ?></th>
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
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<p class="description" style="margin-top: 8px;"><?php esc_html_e( 'Need more models? Duplicate a row in the saved option or tell me and I will add a dynamic repeater UI in the next revision.', 'my-openai-responses-provider' ); ?></p>
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
				'id'            => 'gpt-4.1',
				'label'         => 'gpt-4.1',
				'endpoint_type' => 'responses',
				'enabled'       => true,
			],
			[
				'id'            => 'gpt-4o-mini',
				'label'         => 'gpt-4o-mini',
				'endpoint_type' => 'chat_completions',
				'enabled'       => true,
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

	public static function get_endpoint_url(): string {
		$settings = self::get_settings();
		$url      = (string) $settings[ self::KEY_ENDPOINT_URL ];
		if ( '' === trim( $url ) ) {
			return self::DEFAULT_ENDPOINT;
		}
		return rtrim( $url, '/' );
	}

	public static function get_api_key(): string {
		if ( class_exists( AiClient::class ) ) {
			$registry = AiClient::defaultRegistry();
			if ( $registry->hasProvider( 'openai_responses' ) ) {
				$auth = $registry->getProviderRequestAuthentication( 'openai_responses' );
				if ( $auth instanceof ApiKeyRequestAuthentication ) {
					return (string) $auth->getApiKey();
				}
			}
		}

		$env_key = getenv( 'OPENAI_RESPONSES_API_KEY' );
		if ( false === $env_key ) {
			return '';
		}

		return trim( (string) $env_key );
	}
}
