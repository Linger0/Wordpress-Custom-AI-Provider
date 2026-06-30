<?php
/**
 * Provider implementation.
 */

declare( strict_types=1 );

namespace LingerAiBridgeForOpenAiCompatibleApis\Provider;

use LingerAiBridgeForOpenAiCompatibleApis\Metadata\ResponsesModelMetadataDirectory;
use LingerAiBridgeForOpenAiCompatibleApis\Models\ResponsesTextGenerationModel;
use LingerAiBridgeForOpenAiCompatibleApis\Settings\ResponsesSettings;
use WordPress\AiClient\AiClient;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiProvider;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Contracts\ProviderAvailabilityInterface;
use WordPress\AiClient\Providers\DTO\ProviderMetadata;
use WordPress\AiClient\Providers\Enums\ProviderTypeEnum;
use WordPress\AiClient\Providers\Http\Enums\RequestAuthenticationMethod;
use WordPress\AiClient\Providers\Models\Contracts\ModelInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;

class ResponsesProvider extends AbstractApiProvider {
	protected static function baseUrl(): string {
		$env_url = getenv( 'LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_BASE_URL' );
		if ( false !== $env_url && '' !== trim( $env_url ) ) {
			return rtrim( (string) $env_url, '/' );
		}

		$settings_url = ResponsesSettings::get_endpoint_url();
		if ( '' !== $settings_url ) {
			return rtrim( $settings_url, '/' );
		}

		return '';
	}

	protected static function createModel( ModelMetadata $model_metadata, ProviderMetadata $provider_metadata ): ModelInterface {
		return new ResponsesTextGenerationModel( $model_metadata, $provider_metadata );
	}

	protected static function createProviderMetadata(): ProviderMetadata {
		$provider_metadata_args = [
			'linger_ai_bridge_for_openai_compatible_apis',
			__( 'Linger AI Bridge for OpenAI-Compatible APIs', 'linger-ai-bridge-for-openai-compatible-apis' ),
			ProviderTypeEnum::cloud(),
			'',
			RequestAuthenticationMethod::apiKey(),
			__( 'Configurable Responses and Chat Completions compatible text generation provider.', 'linger-ai-bridge-for-openai-compatible-apis' ),
		];

		if ( class_exists( AiClient::class ) && version_compare( AiClient::VERSION, '1.3.0', '>=' ) ) {
			$provider_metadata_args[] = LINGER_AI_BRIDGE_FOR_OPENAI_COMPATIBLE_APIS_PLUGIN_DIR . 'assets/images/linger-ai-bridge.svg';
		}

		return new ProviderMetadata( ...$provider_metadata_args );
	}

	protected static function createProviderAvailability(): ProviderAvailabilityInterface {
		return new class implements ProviderAvailabilityInterface {
			public function isConfigured(): bool {
				return '' !== ResponsesSettings::get_api_key() && '' !== ResponsesSettings::get_endpoint_url();
			}
		};
	}

	protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface {
		return new ResponsesModelMetadataDirectory();
	}
}
