<?php
/**
 * Text generation model for hybrid Responses / Chat Completions provider.
 */

declare( strict_types=1 );

namespace AiProviderForAnyOpenAiCompatibleProvider\Models;

use AiProviderForAnyOpenAiCompatibleProvider\Settings\ResponsesSettings;
use WordPress\AiClient\Providers\Http\DTO\Request;
use WordPress\AiClient\Providers\Http\DTO\RequestOptions;
use WordPress\AiClient\Providers\Http\DTO\Response;
use WordPress\AiClient\Providers\Http\Enums\HttpMethodEnum;
use WordPress\AiClient\Providers\OpenAiCompatibleImplementation\AbstractOpenAiCompatibleTextGenerationModel;
use WordPress\AiClient\Results\DTO\GenerativeAiResult;

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

		return apply_filters( 'ai_provider_for_any_openai_compatible_provider_text_generation_params', $params, $this->metadata()->getId(), $this->get_endpoint_type() );
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

	protected function parseResponseToGenerativeAiResult( Response $response ): GenerativeAiResult {
		if ( ! $this->uses_responses_api() ) {
			return parent::parseResponseToGenerativeAiResult( $response );
		}

		$response_data = $response->getData();
		if ( is_array( $response_data ) && isset( $response_data['choices'] ) ) {
			return parent::parseResponseToGenerativeAiResult( $response );
		}

		return parent::parseResponseToGenerativeAiResult(
			$this->convert_responses_response_to_chat_completions_response( $response )
		);
	}

	private function convert_responses_response_to_chat_completions_response( Response $response ): Response {
		$response_data = $response->getData();
		if ( ! is_array( $response_data ) ) {
			return $response;
		}

		$converted = [
			'id'      => isset( $response_data['id'] ) && is_string( $response_data['id'] ) ? $response_data['id'] : '',
			'object'  => 'chat.completion',
			'created' => isset( $response_data['created_at'] ) && is_numeric( $response_data['created_at'] ) ? (int) $response_data['created_at'] : time(),
			'model'   => isset( $response_data['model'] ) && is_string( $response_data['model'] ) ? $response_data['model'] : $this->metadata()->getId(),
			'choices' => [
				[
					'index'         => 0,
					'message'       => [
						'role'    => 'assistant',
						'content' => $this->extract_text_from_responses_data( $response_data ),
					],
					'finish_reason' => $this->map_responses_finish_reason( $response_data ),
				],
			],
			'usage'   => $this->convert_responses_usage_to_chat_usage( $response_data['usage'] ?? null ),
		];

		$converted['responses_api_response'] = $response_data;

		$converted_body = json_encode( $converted );
		if ( false === $converted_body ) {
			return $response;
		}

		return new Response(
			$response->getStatusCode(),
			$response->getHeaders(),
			$converted_body
		);
	}

	private function extract_text_from_responses_data( array $response_data ): string {
		if ( isset( $response_data['output_text'] ) && is_string( $response_data['output_text'] ) ) {
			return $response_data['output_text'];
		}

		$text_parts = [];
		if ( isset( $response_data['output'] ) && is_array( $response_data['output'] ) ) {
			foreach ( $response_data['output'] as $output_item ) {
				if ( ! is_array( $output_item ) || ! isset( $output_item['content'] ) || ! is_array( $output_item['content'] ) ) {
					continue;
				}

				foreach ( $output_item['content'] as $content_item ) {
					if ( ! is_array( $content_item ) || ! isset( $content_item['text'] ) || ! is_string( $content_item['text'] ) ) {
						continue;
					}

					$type = isset( $content_item['type'] ) && is_string( $content_item['type'] ) ? $content_item['type'] : '';
					if ( '' === $type || 'output_text' === $type || 'text' === $type || 'refusal' === $type ) {
						$text_parts[] = $content_item['text'];
					}
				}
			}
		}

		return implode( '', $text_parts );
	}

	private function map_responses_finish_reason( array $response_data ): string {
		$status = isset( $response_data['status'] ) && is_string( $response_data['status'] ) ? $response_data['status'] : '';
		if ( 'incomplete' === $status ) {
			$details = isset( $response_data['incomplete_details'] ) && is_array( $response_data['incomplete_details'] ) ? $response_data['incomplete_details'] : [];
			$reason  = isset( $details['reason'] ) && is_string( $details['reason'] ) ? $details['reason'] : '';
			if ( 'content_filter' === $reason ) {
				return 'content_filter';
			}
			return 'length';
		}

		if ( 'failed' === $status || 'cancelled' === $status ) {
			return 'content_filter';
		}

		return 'stop';
	}

	private function convert_responses_usage_to_chat_usage( $usage ): array {
		if ( ! is_array( $usage ) ) {
			return [
				'prompt_tokens'     => 0,
				'completion_tokens' => 0,
				'total_tokens'      => 0,
			];
		}

		$prompt_tokens     = isset( $usage['input_tokens'] ) && is_numeric( $usage['input_tokens'] ) ? (int) $usage['input_tokens'] : 0;
		$completion_tokens = isset( $usage['output_tokens'] ) && is_numeric( $usage['output_tokens'] ) ? (int) $usage['output_tokens'] : 0;
		$total_tokens      = isset( $usage['total_tokens'] ) && is_numeric( $usage['total_tokens'] ) ? (int) $usage['total_tokens'] : $prompt_tokens + $completion_tokens;

		return [
			'prompt_tokens'     => $prompt_tokens,
			'completion_tokens' => $completion_tokens,
			'total_tokens'      => $total_tokens,
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
