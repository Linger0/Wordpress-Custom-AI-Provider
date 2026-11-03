<?php
/**
 * Model metadata directory.
 */

declare( strict_types=1 );

namespace LingerAiBridgeForOpenAiCompatibleApis\Metadata;

use LingerAiBridgeForOpenAiCompatibleApis\Settings\ResponsesSettings;
use WordPress\AiClient\Common\Exception\InvalidArgumentException;
use WordPress\AiClient\Messages\Enums\ModalityEnum;
use WordPress\AiClient\Providers\Contracts\ModelMetadataDirectoryInterface;
use WordPress\AiClient\Providers\Models\DTO\ModelMetadata;
use WordPress\AiClient\Providers\Models\DTO\SupportedOption;
use WordPress\AiClient\Providers\Models\Enums\CapabilityEnum;
use WordPress\AiClient\Providers\Models\Enums\OptionEnum;

class ResponsesModelMetadataDirectory implements ModelMetadataDirectoryInterface {
	// These models come from local settings, so an API discovery cache would become stale after edits.
	public function listModelMetadata(): array {
		return array_values( $this->get_model_metadata_map() );
	}

	public function hasModelMetadata( string $modelId ): bool {
		return isset( $this->get_model_metadata_map()[ $modelId ] );
	}

	public function getModelMetadata( string $modelId ): ModelMetadata {
		$models = $this->get_model_metadata_map();
		if ( ! isset( $models[ $modelId ] ) ) {
			throw new InvalidArgumentException( sprintf( 'No model with ID %s was found in the provider', $modelId ) );
		}
		return $models[ $modelId ];
	}

	private function get_model_metadata_map(): array {
		$models_map = [];
		foreach ( ResponsesSettings::get_models() as $model ) {
			$model_id    = (string) $model['id'];
			$model_label = isset( $model['label'] ) && '' !== trim( (string) $model['label'] ) ? (string) $model['label'] : $model_id;
			$models_map[ $model_id ] = $this->createTextModelMetadata( $model_id, $model_label );
		}

		$default_id = ResponsesSettings::get_default_model_id();
		if ( isset( $models_map[ $default_id ] ) ) {
			$models_map = [ $default_id => $models_map[ $default_id ] ] + $models_map;
		}
		return $models_map;
	}

	private function createTextModelMetadata( string $model_id, string $model_label ): ModelMetadata {
		$options = [
			new SupportedOption( OptionEnum::systemInstruction() ),
			new SupportedOption( OptionEnum::maxTokens() ),
			new SupportedOption( OptionEnum::topP() ),
			new SupportedOption( OptionEnum::outputMimeType(), [ 'text/plain', 'application/json' ] ),
			new SupportedOption( OptionEnum::outputSchema() ),
			new SupportedOption( OptionEnum::customOptions() ),
			new SupportedOption( OptionEnum::outputModalities(), [ [ ModalityEnum::text() ] ] ),
			new SupportedOption( OptionEnum::inputModalities(), [ [ ModalityEnum::text() ], [ ModalityEnum::text(), ModalityEnum::image() ] ] ),
		];
		if ( 'chat_completions' === ResponsesSettings::get_endpoint_type_for_model( $model_id ) ) {
			$options[] = new SupportedOption( OptionEnum::temperature() );
			$options[] = new SupportedOption( OptionEnum::stopSequences() );
		}
		return new ModelMetadata(
			$model_id,
			$model_label,
			[
				CapabilityEnum::textGeneration(),
				CapabilityEnum::chatHistory(),
			],
			$options
		);
	}
}
