<?php

namespace AutoDocumentation\Renderer;

use AutoDocumentation\Generator\TypeInfo;
use AutoDocumentation\Generator\TypeRegistry;

class JsonRenderer
{
	public function __construct(
		private TypeRegistry $registry
	) {}

	/**
	 * Renders documentation as OpenAPI 3.0 JSON
	 *
	 * @param array $docs
	 * @param TypeInfo[] $types
	 */
	public function render(array $docs, array $types, array $info = []): string
	{
		$openApi = [
			'openapi' => '3.0.3',
			'info' => [
				'title' => $info['title'] ?? 'API Documentation',
				'version' => $info['version'] ?? '1.0.0',
				'description' => $info['description'] ?? ''
			],
			'paths' => $this->buildPaths($docs),
			'components' => [
				'schemas' => $this->buildSchemas($types)
			]
		];

		return json_encode($openApi, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	}

	private function buildPaths(array $docs): array
	{
		$paths = [];

		foreach ($docs as $controller) {
			foreach ($controller['endpoints'] as $endpoint) {
				$path = $endpoint['path'];
				$method = strtolower($endpoint['http_method']);

				$operation = [
					'summary' => $endpoint['summary'],
					'operationId' => $endpoint['name'],
					'tags' => $controller['tags'] ?: [$controller['name']],
					'deprecated' => $endpoint['deprecated']
				];

				if (!empty($endpoint['description'])) {
					$operation['description'] = $endpoint['description'];
				}

				// Parameters
				$parameters = [];
				$requestBody = null;

				foreach ($endpoint['parameters'] as $param) {
					if ($param['in'] === 'body') {
						$requestBody = $this->buildRequestBody($param);
					} else {
						$parameters[] = $this->buildParameter($param);
					}
				}

				if (!empty($parameters)) {
					$operation['parameters'] = $parameters;
				}

				if ($requestBody !== null) {
					$operation['requestBody'] = $requestBody;
				}

				// Responses
				$operation['responses'] = $this->buildResponses($endpoint);

				$paths[$path][$method] = $operation;
			}
		}

		return $paths;
	}

	private function buildParameter(array $param): array
	{
		return [
			'name' => $param['name'],
			'in' => $param['in'],
			'required' => $param['required'],
			'description' => $param['description'],
			'schema' => $this->buildSchema($param['type']),
			'example' => $param['example']
		];
	}

	private function buildRequestBody(array $param): array
	{
		return [
			'required' => $param['required'],
			'content' => [
				'application/json' => [
					'schema' => $this->buildSchema($param['type'])
				]
			]
		];
	}

	private function buildResponses(array $endpoint): array
	{
		$responses = [];

		// Main return type
		if ($endpoint['returns'] !== null) {
			$code = (string) $endpoint['returns']['status_code'];
			$responses[$code] = [
				'description' => $endpoint['returns']['description'] ?: 'Success',
				'content' => [
					'application/json' => [
						'schema' => $this->buildSchema($endpoint['returns']['type'])
					]
				]
			];
		}

		// Additional responses
		foreach ($endpoint['responses'] as $response) {
			$code = (string) $response['status_code'];
			$responses[$code] = [
				'description' => $response['description']
			];

			if ($response['type'] !== null) {
				$responses[$code]['content'] = [
					'application/json' => [
						'schema' => $this->buildSchema($response['type'])
					]
				];
			}
		}

		// Default response if none specified
		if (empty($responses)) {
			$responses['200'] = ['description' => 'Success'];
		}

		return $responses;
	}

	/**
	 * @param TypeInfo[] $types
	 */
	private function buildSchemas(array $types): array
	{
		$schemas = [];

		foreach ($types as $type) {
			$properties = [];
			$required = [];

			foreach ($type->properties as $prop) {
				$properties[$prop->name] = $this->buildSchema($prop->type);
				$properties[$prop->name]['description'] = $prop->description;

				if ($prop->example !== null) {
					$properties[$prop->name]['example'] = $prop->example;
				}

				if (!$prop->nullable) {
					$required[] = $prop->name;
				}
			}

			$schemas[$type->shortName] = [
				'type' => 'object',
				'description' => $type->description,
				'properties' => $properties
			];

			if (!empty($required)) {
				$schemas[$type->shortName]['required'] = $required;
			}
		}

		return $schemas;
	}

	private function buildSchema(string $typeName): array
	{
		// Handle nullable
		if (str_starts_with($typeName, '?')) {
			$inner = $this->buildSchema(substr($typeName, 1));
			$inner['nullable'] = true;
			return $inner;
		}

		// Handle arrays
		if (preg_match('/^(.+)\[\]$/', $typeName, $matches)) {
			return [
				'type' => 'array',
				'items' => $this->buildSchema($matches[1])
			];
		}

		// Check if it's a documented type
		$typeInfo = $this->registry->resolve($typeName);

		if ($typeInfo !== null) {
			return ['$ref' => '#/components/schemas/' . $typeInfo->shortName];
		}

		// Built-in types
		return match ($typeName) {
			'int', 'integer' => ['type' => 'integer'],
			'float', 'double' => ['type' => 'number'],
			'bool', 'boolean' => ['type' => 'boolean'],
			'string' => ['type' => 'string'],
			'array' => ['type' => 'array', 'items' => []],
			default => ['type' => 'object']
		};
	}
}