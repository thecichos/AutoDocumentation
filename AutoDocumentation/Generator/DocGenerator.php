<?php

namespace AutoDocumentation\Generator;

use AutoDocumentation\Attributes\ApiDoc;
use AutoDocumentation\Attributes\Endpoint;
use AutoDocumentation\Attributes\Param;
use AutoDocumentation\Attributes\Response;
use AutoDocumentation\Attributes\Returns;
use ReflectionClass;
use ReflectionMethod;

class DocGenerator
{
	public function __construct(
		private TypeRegistry $registry
	) {}

	/**
	 * @param class-string[] $classes
	 * @return array
	 */
	public function generate(array $classes): array
	{
		$docs = [];

		foreach ($classes as $className) {
			$reflection = new ReflectionClass($className);
			$classDoc = $this->extractClassDoc($reflection);

			if ($classDoc === null) {
				continue;
			}

			foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
				$endpoint = $this->extractEndpoint($method);

				if ($endpoint !== null) {
					$classDoc['endpoints'][] = $endpoint;
				}
			}

			$docs[] = $classDoc;
		}

		return $docs;
	}

	private function extractClassDoc(ReflectionClass $class): ?array
	{
		$attr = $class->getAttributes(ApiDoc::class)[0] ?? null;

		if ($attr === null) {
			return null;
		}

		$apiDoc = $attr->newInstance();

		return [
			'name' => $class->getShortName(),
			'fqcn' => $class->getName(),
			'description' => $apiDoc->description,
			'version' => $apiDoc->version,
			'tags' => $apiDoc->tags,
			'deprecated' => $apiDoc->deprecated,
			'endpoints' => []
		];
	}

	private function extractEndpoint(ReflectionMethod $method): ?array
	{
		$attr = $method->getAttributes(Endpoint::class)[0] ?? null;

		if ($attr === null) {
			return null;
		}

		$endpoint = $attr->newInstance();

		return [
			'name' => $method->getName(),
			'slug' => $this->slugify($method->getName()),
			'http_method' => strtoupper($endpoint->method),
			'path' => $endpoint->path,
			'summary' => $endpoint->summary,
			'description' => $endpoint->description,
			'deprecated' => $endpoint->deprecated,
			'parameters' => $this->extractParameters($method),
			'returns' => $this->extractReturns($method),
			'responses' => $this->extractResponses($method)
		];
	}

	private function extractParameters(ReflectionMethod $method): array
	{
		$params = [];

		foreach ($method->getParameters() as $param) {
			$attr = $param->getAttributes(Param::class)[0] ?? null;
			$paramDoc = $attr?->newInstance();

			$type = $param->getType();
			$typeName = $type?->getName() ?? 'mixed';

			// Try to get type from docblock for arrays
			$docType = $this->parseParamDocBlock($method, $param->getName());

			$params[] = [
				'name' => $param->getName(),
				'type' => $docType ?? $typeName,
				'nullable' => $type?->allowsNull() ?? true,
				'required' => $paramDoc?->required ?? !$param->isOptional(),
				'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
				'has_default' => $param->isDefaultValueAvailable(),
				'description' => $paramDoc?->description ?? '',
				'example' => $paramDoc?->example,
				'in' => $paramDoc?->in ?? $this->guessParamLocation($param->getName(), $method)
			];
		}

		return $params;
	}

	private function extractReturns(ReflectionMethod $method): ?array
	{
		$attr = $method->getAttributes(Returns::class)[0] ?? null;

		if ($attr !== null) {
			$returns = $attr->newInstance();
			return [
				'type' => $returns->type,
				'description' => $returns->description,
				'status_code' => $returns->statusCode
			];
		}

		// Fall back to return type
		$returnType = $method->getReturnType();

		if ($returnType === null) {
			return null;
		}

		// Try docblock for array types
		$docReturn = $this->parseReturnDocBlock($method);

		return [
			'type' => $docReturn ?? $returnType->getName(),
			'description' => '',
			'status_code' => 200
		];
	}

	private function extractResponses(ReflectionMethod $method): array
	{
		$responses = [];
		$attrs = $method->getAttributes(Response::class);

		foreach ($attrs as $attr) {
			$response = $attr->newInstance();
			$responses[] = [
				'status_code' => $response->statusCode,
				'description' => $response->description,
				'type' => $response->type
			];
		}

		return $responses;
	}

	private function parseParamDocBlock(ReflectionMethod $method, string $paramName): ?string
	{
		$docComment = $method->getDocComment();

		if (!$docComment) {
			return null;
		}

		// Match @param Type $name
		$pattern = '/@param\s+([^\s]+)\s+\$' . preg_quote($paramName, '/') . '/';

		if (preg_match($pattern, $docComment, $matches)) {
			return $matches[1];
		}

		return null;
	}

	private function parseReturnDocBlock(ReflectionMethod $method): ?string
	{
		$docComment = $method->getDocComment();

		if (!$docComment) {
			return null;
		}

		if (preg_match('/@return\s+([^\s]+)/', $docComment, $matches)) {
			return $matches[1];
		}

		return null;
	}

	private function guessParamLocation(string $paramName, ReflectionMethod $method): string
	{
		// Check if param appears in route path
		$endpoint = $method->getAttributes(Endpoint::class)[0] ?? null;

		if ($endpoint !== null) {
			$endpointInstance = $endpoint->newInstance();

			if (str_contains($endpointInstance->path, '{' . $paramName . '}')) {
				return 'path';
			}
		}

		// POST/PUT/PATCH usually have body params
		if ($endpoint !== null) {
			$httpMethod = strtoupper($endpoint->newInstance()->method);

			if (in_array($httpMethod, ['POST', 'PUT', 'PATCH'], true)) {
				return 'body';
			}
		}

		return 'query';
	}

	private function slugify(string $name): string
	{
		return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
	}
}