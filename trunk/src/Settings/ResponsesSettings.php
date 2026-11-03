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
	public const MAX_MODELS         = 10;
	private const OPTION_GROUP      = 'linger-ai-bridge-for-openai-compatible-apis-settings';
	private const OPTION_NAME       = 'linger_ai_bridge_for_openai_compatible_apis_settings';
	private const PAGE_SLUG         = 'linger-ai-bridge-for-openai-compatible-apis';
	private const KEY_ENDPOINT_URL  = 'endpoint_url';
	private const KEY_MODELS        = 'models';
	private const KEY_DEFAULT_MODEL = 'default_model';
	private const DEFAULT_ENDPOINT  = '';

	public function init(): void {
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'admin_menu', [ $this, 'register_settings_screen' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	public function enqueue_assets( string $hook_suffix ): void {
		if ( 'settings_page_' . self::PAGE_SLUG !== $hook_suffix ) {
			return;
		}
		wp_enqueue_script( 'linger-ai-bridge-settings', plugins_url( 'assets/js/settings.js', LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_PLUGIN_FILE ), [], (string) filemtime( LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_PLUGIN_DIR . 'assets/js/settings.js' ), true );
		wp_enqueue_style( 'linger-ai-bridge-settings', plugins_url( 'assets/css/settings.css', LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_PLUGIN_FILE ), [], (string) filemtime( LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_PLUGIN_DIR . 'assets/css/settings.css' ) );
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
			__( 'AI Bridge for OpenAI-Compatible APIs', 'linger-ai-bridge-for-openai-compatible-apis' ),
			__( 'AI Bridge for OpenAI-Compatible APIs', 'linger-ai-bridge-for-openai-compatible-apis' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_screen' ]
		);
	}

	public function sanitize_settings( $value ): array {
		if ( ! is_array( $value ) ) {
			return self::get_default_settings();
		}

		$endpoint_value = $value[ self::KEY_ENDPOINT_URL ] ?? '';
		$endpoint_url   = $this->sanitize_endpoint_url( is_string( $endpoint_value ) ? $endpoint_value : '' );

		$models = [];
		if ( isset( $value[ self::KEY_MODELS ] ) && is_array( $value[ self::KEY_MODELS ] ) ) {
			foreach ( $value[ self::KEY_MODELS ] as $row ) {
				if ( ! is_array( $row ) ) {
					continue;
				}

				$id       = self::text_value( $row['id'] ?? '' );
				$label    = self::text_value( $row['label'] ?? '' );
				$endpoint = self::text_value( $row['endpoint_type'] ?? 'responses' );
				$enabled  = ! empty( $row['enabled'] );
				$stream   = ! empty( $row['stream'] );

				if ( '' === $id ) {
					continue;
				}
				if ( in_array( $id, array_column( $models, 'id' ), true ) ) {
					add_settings_error( self::OPTION_NAME, 'duplicate_model', __( 'Duplicate model IDs were ignored. Use a unique ID for each model.', 'linger-ai-bridge-for-openai-compatible-apis' ) );
					continue;
				}
				if ( count( $models ) >= self::MAX_MODELS ) {
					add_settings_error( self::OPTION_NAME, 'too_many_models', __( 'Only the first 10 models were saved.', 'linger-ai-bridge-for-openai-compatible-apis' ) );
					break;
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

		$default_model = self::text_value( $value[ self::KEY_DEFAULT_MODEL ] ?? '' );
		$enabled_ids   = array_column(
			array_filter(
				$models,
				static function ( $model ) {
					return $model['enabled'];
				}
			),
			'id'
		);
		if ( ! in_array( $default_model, $enabled_ids, true ) ) {
			$default_model = $enabled_ids[0] ?? '';
		}

		return [
			self::KEY_ENDPOINT_URL  => rtrim( $endpoint_url, '/' ),
			self::KEY_MODELS        => array_values( $models ),
			self::KEY_DEFAULT_MODEL => $default_model,
		];
	}

	private static function text_value( $value ): string {
		return is_scalar( $value ) ? sanitize_text_field( trim( (string) $value ) ) : '';
	}

	private function sanitize_endpoint_url( string $url ): string {
		$url = trim( $url );
		if ( '' === $url ) {
			return '';
		}

		$url   = esc_url_raw( $url, [ 'http', 'https' ] );
		$parts = wp_parse_url( $url );
		if ( ! is_array( $parts ) || empty( $parts['host'] ) || ! in_array( strtolower( $parts['scheme'] ?? '' ), [ 'http', 'https' ], true ) || isset( $parts['user'] ) || isset( $parts['pass'] ) || isset( $parts['query'] ) || isset( $parts['fragment'] ) ) {
			add_settings_error( self::OPTION_NAME, 'invalid_endpoint', __( 'Enter an HTTP or HTTPS base URL without credentials, a query string, or a fragment.', 'linger-ai-bridge-for-openai-compatible-apis' ) );
			return '';
		}
		return $url;
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
					esc_html__( 'Set your API key under %1$sSettings > Connectors%2$s for the AI Bridge for OpenAI-Compatible APIs connector.', 'linger-ai-bridge-for-openai-compatible-apis' ),
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
		$settings      = self::get_settings();
		$models        = array_slice( array_values( $settings[ self::KEY_MODELS ] ), 0, self::MAX_MODELS );
		$default_model = self::get_default_model_id();
		if ( [] === $models ) {
			$models = self::get_default_models();
		}
		$headings = [
			__( 'Default', 'linger-ai-bridge-for-openai-compatible-apis' ),
			__( 'Enabled', 'linger-ai-bridge-for-openai-compatible-apis' ),
			__( 'Model ID', 'linger-ai-bridge-for-openai-compatible-apis' ),
			__( 'Label', 'linger-ai-bridge-for-openai-compatible-apis' ),
			__( 'Endpoint Type', 'linger-ai-bridge-for-openai-compatible-apis' ),
			__( 'Streaming', 'linger-ai-bridge-for-openai-compatible-apis' ),
			__( 'Actions', 'linger-ai-bridge-for-openai-compatible-apis' ),
		];
		?>
		<div id="linger-ai-bridge-models" data-max-models="<?php echo esc_attr( (string) self::MAX_MODELS ); ?>">
			<p class="description"><?php esc_html_e( 'Add up to 10 models. Enable the models you want to use and select a default. The default is preferred for automatic text generation in the WordPress AI plugin; explicit model selections take precedence.', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></p>
			<div class="linger-ai-bridge-table-wrap">
				<table class="widefat striped">
					<thead><tr>
						<?php foreach ( $headings as $heading ) : ?>
							<th scope="col"><?php echo esc_html( $heading ); ?></th>
						<?php endforeach; ?>
					</tr></thead>
					<tbody>
						<?php foreach ( $models as $index => $model ) : ?>
							<?php $this->render_model_row( (string) $index, $model, $default_model ); ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<p><button type="button" class="button" data-add-model><span aria-hidden="true">＋</span> <?php esc_html_e( 'Add model', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></button></p>
			<p class="description" data-model-limit role="status" hidden><?php esc_html_e( 'The maximum of 10 models has been reached.', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></p>
			<template id="linger-ai-bridge-model-template"><?php $this->render_model_row( '__INDEX__', self::get_default_models()[0], '' ); ?></template>
			<noscript><p><?php esc_html_e( 'Enable JavaScript to add or remove models.', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></p></noscript>
		</div>
		<?php
	}

	private function render_model_row( string $index, array $model, string $default_model ): void {
		$prefix = self::OPTION_NAME . '[' . self::KEY_MODELS . '][' . $index . ']';
		$id     = (string) ( $model['id'] ?? '' );
		?>
		<tr data-model-row>
			<td><input type="radio" data-model-default name="<?php echo esc_attr( self::OPTION_NAME . '[' . self::KEY_DEFAULT_MODEL . ']' ); ?>" value="<?php echo esc_attr( $id ); ?>" aria-label="<?php esc_attr_e( 'Use as default model', 'linger-ai-bridge-for-openai-compatible-apis' ); ?>" <?php checked( '' !== $id && $default_model === $id ); ?> /></td>
			<td><input type="checkbox" data-model-enabled name="<?php echo esc_attr( $prefix . '[enabled]' ); ?>" value="1" aria-label="<?php esc_attr_e( 'Enable model', 'linger-ai-bridge-for-openai-compatible-apis' ); ?>" <?php checked( ! empty( $model['enabled'] ) ); ?> /></td>
			<td><input type="text" class="code" data-model-id name="<?php echo esc_attr( $prefix . '[id]' ); ?>" value="<?php echo esc_attr( $id ); ?>" aria-label="<?php esc_attr_e( 'Model ID', 'linger-ai-bridge-for-openai-compatible-apis' ); ?>" /></td>
			<td><input type="text" name="<?php echo esc_attr( $prefix . '[label]' ); ?>" value="<?php echo esc_attr( (string) ( $model['label'] ?? '' ) ); ?>" aria-label="<?php esc_attr_e( 'Model label', 'linger-ai-bridge-for-openai-compatible-apis' ); ?>" /></td>
			<td><select data-model-endpoint name="<?php echo esc_attr( $prefix . '[endpoint_type]' ); ?>" aria-label="<?php esc_attr_e( 'Endpoint type', 'linger-ai-bridge-for-openai-compatible-apis' ); ?>">
				<option value="responses" <?php selected( $model['endpoint_type'] ?? 'responses', 'responses' ); ?>>/responses</option>
				<option value="chat_completions" <?php selected( $model['endpoint_type'] ?? '', 'chat_completions' ); ?>>/chat/completions</option>
			</select></td>
			<td><input type="checkbox" data-model-stream name="<?php echo esc_attr( $prefix . '[stream]' ); ?>" value="1" aria-label="<?php esc_attr_e( 'Use a streaming response (Responses only)', 'linger-ai-bridge-for-openai-compatible-apis' ); ?>" <?php checked( ! empty( $model['stream'] ) ); ?> /></td>
			<td><button type="button" class="button-link-delete" data-remove-model><?php esc_html_e( 'Remove', 'linger-ai-bridge-for-openai-compatible-apis' ); ?></button></td>
		</tr>
		<?php
	}

	public static function get_settings(): array {
		$settings = (array) get_option( self::OPTION_NAME, [] );
		$settings = array_merge( self::get_default_settings(), $settings );
		if ( ! is_array( $settings[ self::KEY_MODELS ] ) ) {
			$settings[ self::KEY_MODELS ] = self::get_default_models();
		}
		$settings[ self::KEY_MODELS ] = array_slice( array_values( array_filter( $settings[ self::KEY_MODELS ], 'is_array' ) ), 0, self::MAX_MODELS );
		return $settings;
	}

	private static function get_default_settings(): array {
		return [
			self::KEY_ENDPOINT_URL  => self::DEFAULT_ENDPOINT,
			self::KEY_MODELS        => self::get_default_models(),
			self::KEY_DEFAULT_MODEL => '',
		];
	}

	private static function get_default_models(): array {
		return [
			[
				'id'            => '',
				'label'         => '',
				'endpoint_type' => 'responses',
				'enabled'       => false,
				'stream'        => false,
			],
		];
	}

	public static function get_default_model_id(): string {
		$settings = self::get_settings();
		$models   = self::get_models();
		$selected = self::text_value( $settings[ self::KEY_DEFAULT_MODEL ] ?? '' );
		foreach ( $models as $model ) {
			if ( $selected === $model['id'] ) {
				return $selected;
			}
		}
		return (string) ( $models[0]['id'] ?? '' );
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
		$env_url = getenv( 'LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_BASE_URL' );
		if ( false !== $env_url && '' !== trim( $env_url ) ) {
			return rtrim( trim( $env_url ), '/' );
		}
		$settings = self::get_settings();
		$url      = is_string( $settings[ self::KEY_ENDPOINT_URL ] ) ? trim( $settings[ self::KEY_ENDPOINT_URL ] ) : '';
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
