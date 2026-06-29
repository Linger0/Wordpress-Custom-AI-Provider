<?php
/**
 * Model metadata directory.
 */

declare( strict_types=1 );

namespace AiProviderForAnyOpenAiCompatibleProvider\Metadata;

use AiProviderForAnyOpenAiCompatibleProvider\Settings\ResponsesSettings;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\ApiBasedImplementation\AbstractApiBasedModelMetadataDirectory;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;

class ResponsesModelMetadataDirectory extends AbstractApiBasedModelMetadataDirectory {
	protected function sendListModelsRequest(): array {
		$models_map = [];
		foreach ( ResponsesSettings::get_models() as $model ) {
			$model_id    = (string) $model['id'];
			$model_label = isset( $model['label'] ) && '' !== trim( (string) $model['label'] ) ? (string) $model['label'] : $model_id;
			$models_map[ $model_id ] = $this->createTextModelMetadata( $model_id, $model_label );
		}

		ksort( $models_map );
		return $models_map;
	}

	private function createTextModelMetadata( string $model_id, string $model_label ): ModelMetadata {
		return new ModelMetadata(
			$model_id,
			$model_label,
			[
				CapabilityEnum::textGeneration(),
				CapabilityEnum::chatHistory(),
			],
			[
				new SupportedOption( OptionEnum::systemInstruction() ),
				new SupportedOption( OptionEnum::maxTokens() ),
				new SupportedOption( OptionEnum::temperature() ),
				new SupportedOption( OptionEnum::topP() ),
				new SupportedOption( OptionEnum::stopSequences() ),
				new SupportedOption( OptionEnum::outputMimeType(), [ 'text/plain', 'application/json' ] ),
				new SupportedOption( OptionEnum::outputSchema() ),
				new SupportedOption( OptionEnum::customOptions() ),
				new SupportedOption( OptionEnum::outputModalities(), [ [ ModalityEnum::text() ] ] ),
				new SupportedOption( OptionEnum::inputModalities(), [ [ ModalityEnum::text() ], [ ModalityEnum::text(), ModalityEnum::image() ] ] ),
			]
		);
	}
}
