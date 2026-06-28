<?php
/**
 * Text generation model for hybrid Responses / Chat Completions provider.
 */

declare( strict_types=1 );

namespace MyOpenAiResponsesProvider\Models;

use MyOpenAiResponsesProvider\Settings\ResponsesSettings;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\RequestOptions;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;

class ResponsesTextGenerationModel extends AbstractOpenAiCompatibleTextGenerationModel {
	protected function prepareGenerateTextParams( array $prompt ): array {
		$params = parent::prepareGenerateTextParams( $prompt );
		$params['model'] = $this->metadata()->getId();

		unset( $params['n'] );
		unset( $params['frequency_penalty'] );
		unset( $params['presence_penalty'] );
		unset( $params['logit_bias'] );

		if ( $this->uses_responses_api() ) {
			if ( isset( $params['messages'] ) && ! isset( $params['input'] ) && is_array( $params['messages'] ) ) {
				$params['input'] = $this->convert_messages_to_responses_input( $params['messages'] );
			}
			unset( $params['messages'] );

			if ( isset( $params['max_tokens'] ) && ! isset( $params['max_output_tokens'] ) ) {
				$params['max_output_tokens'] = $params['max_tokens'];
			}
			unset( $params['max_tokens'] );

			if ( isset( $params['response_format'] ) && is_array( $params['response_format'] ) && ! isset( $params['text'] ) ) {
				$params['text'] = $this->convert_response_format_to_responses_text_param( $params['response_format'] );
			}
			unset( $params['response_format'] );
		}

		return apply_filters( 'my_openai_responses_text_generation_params', $params, $this->metadata()->getId(), $this->get_endpoint_type() );
	}

	protected function prepareResponseFormatParam( ?array $output_schema ): array {
		if ( is_array( $output_schema ) ) {
			return [
				'type'        => 'json_schema',
				'json_schema' => [
					'name'   => 'result',
					'schema' => $output_schema,
					'strict' => true,
				],
			];
		}

		return [ 'type' => 'json_object' ];
	}

	protected function prepareMessagesParam( array $messages, ?string $system_instruction = null ): array {
		$messages = parent::prepareMessagesParam( $messages, $system_instruction );

		foreach ( $messages as &$message ) {
			if ( ! isset( $message['content'] ) || ! is_array( $message['content'] ) ) {
				continue;
			}

			$content  = $message['content'];
			$all_text = true;
			foreach ( $content as $key => $part ) {
				if ( ! is_int( $key ) || ! is_array( $part ) || ( $part['type'] ?? '' ) !== 'text' ) {
					$all_text = false;
					break;
				}
			}

			if ( ! $all_text || count( $content ) <= 0 ) {
				continue;
			}

			$message['content'] = implode(
				'',
				array_map(
					static function ( $part ) {
						return $part['text'] ?? '';
					},
					$content
				)
			);
		}
		unset( $message );

		return $messages;
	}

	private function convert_messages_to_responses_input( array $messages ): array {
		$input = [];

		foreach ( $messages as $message ) {
			if ( ! is_array( $message ) ) {
				continue;
			}

			$role = isset( $message['role'] ) ? (string) $message['role'] : 'user';
			if ( 'system' === $role ) {
				$role = 'developer';
			}

			$input[] = [
				'role'    => $role,
				'content' => $this->convert_message_content_to_responses_content( $message['content'] ?? '' ),
			];
		}

		return $input;
	}

	/**
	 * Convert OpenAI chat content parts to Responses input content parts.
	 */
	private function convert_message_content_to_responses_content( $content ): array {
		if ( is_string( $content ) ) {
			return [
				[
					'type' => 'input_text',
					'text' => $content,
				],
			];
		}

		if ( ! is_array( $content ) ) {
			return [
				[
					'type' => 'input_text',
					'text' => '',
				],
			];
		}

		$parts = [];
		foreach ( $content as $part ) {
			if ( ! is_array( $part ) ) {
				continue;
			}

			$type = $part['type'] ?? '';
			if ( 'text' === $type ) {
				$parts[] = [
					'type' => 'input_text',
					'text' => (string) ( $part['text'] ?? '' ),
				];
				continue;
			}

			if ( 'image_url' === $type && isset( $part['image_url']['url'] ) ) {
				$parts[] = [
					'type'      => 'input_image',
					'image_url' => (string) $part['image_url']['url'],
				];
			}
		}

		if ( [] === $parts ) {
			$parts[] = [
				'type' => 'input_text',
				'text' => '',
			];
		}

		return $parts;
	}

	private function convert_response_format_to_responses_text_param( array $response_format ): array {
		if ( isset( $response_format['type'] ) && 'json_schema' === $response_format['type'] && isset( $response_format['json_schema'] ) && is_array( $response_format['json_schema'] ) ) {
			return [
				'format' => array_merge(
					[ 'type' => 'json_schema' ],
					$response_format['json_schema']
				),
			];
		}

		return [
			'format' => $response_format,
		];
	}

	protected function createRequest( HttpMethodEnum $method, string $path, array $headers = [], $data = null ): Request {
		$existing = $this->getRequestOptions();
		$options  = null !== $existing ? RequestOptions::fromArray( $existing->toArray() ) : new RequestOptions();

		$effective_base_url = rtrim( ResponsesSettings::get_endpoint_url(), '/' );
		$is_local           = preg_match( '#^https?://(localhost|127\\.0\\.0\\.1|\\[::1\\])(:\\d+)?(/|$)#i', $effective_base_url ) === 1;

		if ( $is_local || $options->getTimeout() === null ) {
			$options->setTimeout( $is_local ? 120.0 : 60.0 );
		}

		if ( $is_local || $options->getConnectTimeout() === null ) {
			$options->setConnectTimeout( $is_local ? 5.0 : 60.0 );
		}

		$request_path = $this->uses_responses_api() ? '/responses' : '/chat/completions';

		return new Request(
			$method,
			$effective_base_url . $request_path,
			$headers,
			$data,
			$options
		);
	}

	private function get_endpoint_type(): string {
		return ResponsesSettings::get_endpoint_type_for_model( $this->metadata()->getId() );
	}

	private function uses_responses_api(): bool {
		return 'responses' === $this->get_endpoint_type();
	}
}
