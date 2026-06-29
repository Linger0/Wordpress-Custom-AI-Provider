<?php
/**
 * Provider implementation.
 */

declare( strict_types=1 );

namespace AiProviderForAnyOpenAiCompatibleProvider\Provider;

use AiProviderForAnyOpenAiCompatibleProvider\Metadata\ResponsesModelMetadataDirectory;
use AiProviderForAnyOpenAiCompatibleProvider\Models\ResponsesTextGenerationModel;
use AiProviderForAnyOpenAiCompatibleProvider\Settings\ResponsesSettings;
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
		$env_url = getenv( 'AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_BASE_URL' );
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
			'ai_provider_for_any_openai_compatible_provider',
			__( 'AI Provider for Any OpenAI-Compatible Provider', 'ai-provider-for-any-openai-compatible-provider' ),
			ProviderTypeEnum::cloud(),
			'',
			RequestAuthenticationMethod::apiKey(),
			__( 'Configurable Responses and Chat Completions compatible text generation provider.', 'ai-provider-for-any-openai-compatible-provider' ),
		];

		if ( class_exists( AiClient::class ) && version_compare( AiClient::VERSION, '1.3.0', '>=' ) ) {
			$provider_metadata_args[] = AI_PROVIDER_FOR_ANY_OPENAI_COMPATIBLE_PROVIDER_PLUGIN_DIR . 'assets/images/ai-provider.svg';
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
