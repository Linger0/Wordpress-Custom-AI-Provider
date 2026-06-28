<?php
/**
 * Provider implementation.
 */

declare( strict_types=1 );

namespace MyOpenAiResponsesProvider\Provider;

use MyOpenAiResponsesProvider\Metadata\ResponsesModelMetadataDirectory;
use MyOpenAiResponsesProvider\Models\ResponsesTextGenerationModel;
use MyOpenAiResponsesProvider\Settings\ResponsesSettings;
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
	private const DEFAULT_BASE_URL = 'https://api.openai.com/v1';

	protected static function baseUrl(): string {
		$env_url = getenv( 'OPENAI_RESPONSES_BASE_URL' );
		if ( false !== $env_url && '' !== trim( $env_url ) ) {
			return rtrim( (string) $env_url, '/' );
		}

		$settings_url = ResponsesSettings::get_endpoint_url();
		if ( '' !== $settings_url ) {
			return rtrim( $settings_url, '/' );
		}

		return self::DEFAULT_BASE_URL;
	}

	protected static function createModel( ModelMetadata $model_metadata, ProviderMetadata $provider_metadata ): ModelInterface {
		return new ResponsesTextGenerationModel( $model_metadata, $provider_metadata );
	}

	protected static function createProviderMetadata(): ProviderMetadata {
		$provider_metadata_args = [
			'openai_responses',
			__( 'Custom AI Responses Provider', 'my-openai-responses-provider' ),
			ProviderTypeEnum::cloud(),
			'',
			RequestAuthenticationMethod::apiKey(),
			__( 'Configurable Responses and Chat Completions compatible text generation provider.', 'my-openai-responses-provider' ),
		];

		if ( class_exists( AiClient::class ) && version_compare( AiClient::VERSION, '1.3.0', '>=' ) ) {
			$provider_metadata_args[] = MY_OPENAI_RESPONSES_PROVIDER_PLUGIN_DIR . 'assets/images/responses-api.svg';
		}

		return new ProviderMetadata( ...$provider_metadata_args );
	}

	protected static function createProviderAvailability(): ProviderAvailabilityInterface {
		return new class implements ProviderAvailabilityInterface {
			public function isConfigured(): bool {
				return '' !== ResponsesSettings::get_api_key();
			}
		};
	}

	protected static function createModelMetadataDirectory(): ModelMetadataDirectoryInterface {
		return new ResponsesModelMetadataDirectory();
	}
}
