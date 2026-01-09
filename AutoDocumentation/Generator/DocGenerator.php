<?php

namespace AutoDocumentation\Generator;

use ReflectionClass;
use ReflectionMethod;

/**
 * Handles extraction of types, methods and endpoints
 *
 * @group Generator
 */
class DocGenerator
{
	/**
	 * @param TypeRegistry $registry
	 */
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

	/**
	 *
	 * Handles extraction of class-level documentation
	 *
	 * @param ReflectionClass $class
	 * @return array|null
	 */
	private function extractClassDoc(ReflectionClass $class): ?array
	{
		$docComment = $class->getDocComment();

		if (!$docComment) {
			return null;
		}

		$apiDoc = $this->parseClassDocBlock($docComment);

		if ($apiDoc === null) {
			return null;
		}

		return [
			'name' => $class->getShortName(),
			'fqcn' => $class->getName(),
			'description' => $apiDoc['description'],
			'version' => $apiDoc['version'],
			'tags' => $apiDoc['tags'],
			'deprecated' => $apiDoc['deprecated'],
			'endpoints' => []
		];
	}

	/**
	 *
	 * Handles extraction of method-level documentation
	 *
	 * @param string $docComment
	 * @return array|null
	 */
	private function parseClassDocBlock(string $docComment): ?array
	{
		// Check for @api-doc tag to indicate this is an API controller
		if (!preg_match('/@api-doc\b/', $docComment)) {
			return null;
		}

		return [
			'description' => $this->parseDescription($docComment),
			'version' => $this->parseTag($docComment, 'version') ?? '1.0.0',
			'tags' => $this->parseTags($docComment),
			'deprecated' => $this->hasTag($docComment, 'deprecated'),
		];
	}

	/**
	 *
	 * Handles extraction of endpoint-level documentation
	 *
	 * @param ReflectionMethod $method
	 * @return array|null
	 */
	private function extractEndpoint(ReflectionMethod $method): ?array
	{
		$docComment = $method->getDocComment();

		if (!$docComment) {
			return null;
		}

		$endpoint = $this->parseEndpointDocBlock($docComment);

		if ($endpoint === null) {
			return null;
		}

		return [
			'name' => $method->getName(),
			'slug' => $this->slugify($method->getName()),
			'http_method' => strtoupper($endpoint['method']),
			'path' => $endpoint['path'],
			'summary' => $endpoint['summary'],
			'description' => $endpoint['description'],
			'deprecated' => $endpoint['deprecated'],
			'parameters' => $this->extractParameters($method),
			'returns' => $this->extractReturns($method),
			'responses' => $this->extractResponses($docComment)
		];
	}

	/**
	 *
	 * Handles extraction of endpoint-level documentation
	 *
	 * @param string $docComment
	 * @return array|null
	 */
	private function parseEndpointDocBlock(string $docComment): ?array
	{
		// Match @endpoint GET /path
		if (!preg_match('/@endpoint\s+(\w+)\s+(\S+)/', $docComment, $matches)) {
			return null;
		}

		return [
			'method' => $matches[1],
			'path' => $matches[2],
			'summary' => $this->parseTag($docComment, 'summary') ?? '',
			'description' => $this->parseDescription($docComment),
			'deprecated' => $this->hasTag($docComment, 'deprecated'),
		];
	}

	/**
	 *
	 * Extracts parameters from a method's docblock
	 *
	 * @param ReflectionMethod $method
	 * @return array
	 */
	private function extractParameters(ReflectionMethod $method): array
	{
		$params = [];
		$docComment = $method->getDocComment() ?: '';

		foreach ($method->getParameters() as $param) {
			$paramDoc = $this->parseParamDocBlock($docComment, $param->getName());

			$type = $param->getType();
			$typeName = $type?->getName() ?? 'mixed';

			$params[] = [
				'name' => $param->getName(),
				'type' => $paramDoc['type'] ?? $typeName,
				'nullable' => $type?->allowsNull() ?? true,
				'required' => $paramDoc['required'] ?? !$param->isOptional(),
				'default' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
				'has_default' => $param->isDefaultValueAvailable(),
				'description' => $paramDoc['description'] ?? '',
				'example' => $paramDoc['example'] ?? null,
				'in' => $paramDoc['in'] ?? $this->guessParamLocation($param->getName(), $method)
			];
		}

		return $params;
	}

	/**
	 *
	 * Extracts returns from a method's docblock
	 *
	 * @param ReflectionMethod $method
	 * @return array|null
	 */
	private function extractReturns(ReflectionMethod $method): ?array
	{
		$docComment = $method->getDocComment();

		if (!$docComment) {
			return null;
		}

		$returns = $this->parseReturnsDocBlock($docComment);

		if ($returns !== null) {
			return $returns;
		}

		// Fall back to return type
		$returnType = $method->getReturnType();

		if ($returnType === null) {
			return null;
		}

		// Try docblock for array types
		$docReturn = $this->parseReturnType($docComment);

		return [
			'type' => $docReturn ?? $returnType->getName(),
			'description' => '',
			'status_code' => 200
		];
	}

	private function parseReturnsDocBlock(string $docComment): ?array
	{
		// Match @returns Type Description with optional status code
		// e.g., @returns User The user object
		// e.g., @returns User The user object @status 201
		if (!preg_match('/@returns\s+(\S+)(?:\s+(.+?))?(?:\s+@status\s+(\d+))?$/m', $docComment, $matches)) {
			return null;
		}

		return [
			'type' => $matches[1],
			'description' => trim($matches[2] ?? ''),
			'status_code' => isset($matches[3]) ? (int)$matches[3] : 200
		];
	}

	/**
	 *
	 * Extracts responses from a docblock
	 *
	 * @param string $docComment
	 * @return array
	 */
	private function extractResponses(string $docComment): array
	{
		$responses = [];

		// Match @response 200 Description [Type]
		preg_match_all('/@response\s+(\d+)\s+([^@\n]+?)(?:\s+\[(\S+)\])?$/m', $docComment, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {
			$responses[] = [
				'status_code' => (int)$match[1],
				'description' => trim($match[2]),
				'type' => $match[3] ?? null
			];
		}

		return $responses;
	}

	/**
	 *
	 * Parses a parameter's docblock
	 *
	 * @param string $docComment
	 * @param string $paramName
	 * @return array|null
	 */
	private function parseParamDocBlock(string $docComment, string $paramName): ?array
	{
		// Extended format: @param Type $name Description [in:query] [required] [example:value]
		$pattern = '/@param\s+(\S+)\s+\$' . preg_quote($paramName, '/') . '(?:\s+(.*))?$/m';

		if (!preg_match($pattern, $docComment, $matches)) {
			return null;
		}

		$type = $matches[1];
		$rest = $matches[2] ?? '';

		// Parse optional modifiers from the description
		$in = 'query';
		$required = null;
		$example = null;
		$description = $rest;

		if (preg_match('/\[in:(\w+)\]/', $rest, $inMatch)) {
			$in = $inMatch[1];
			$description = str_replace($inMatch[0], '', $description);
		}

		if (preg_match('/\[required\]/', $rest)) {
			$required = true;
			$description = str_replace('[required]', '', $description);
		}

		if (preg_match('/\[optional\]/', $rest)) {
			$required = false;
			$description = str_replace('[optional]', '', $description);
		}

		if (preg_match('/\[example:([^\]]+)\]/', $rest, $exampleMatch)) {
			$example = $exampleMatch[1];
			$description = str_replace($exampleMatch[0], '', $description);
		}

		return [
			'type' => $type,
			'description' => trim($description),
			'in' => $in,
			'required' => $required,
			'example' => $example,
		];
	}

	/**
	 *
	 * Parses the return type from a docblock
	 *
	 * @param string $docComment
	 * @return string|null
	 */
	private function parseReturnType(string $docComment): ?string
	{
		if (preg_match('/@return\s+(\S+)/', $docComment, $matches)) {
			return $matches[1];
		}

		return null;
	}

	/**
	 *
	 * Parses the description from a docblock
	 *
	 * @param string $docComment
	 * @return string
	 */
	private function parseDescription(string $docComment): string
	{
		// Remove the opening /** and closing */
		$content = preg_replace('/^\/\*\*|\*\/$/', '', $docComment);

		// Split into lines and process
		$lines = explode("\n", $content);
		$description = [];

		foreach ($lines as $line) {
			// Clean the line (remove leading * and whitespace)
			$line = preg_replace('/^\s*\*\s?/', '', $line);

			// Stop at first tag
			if (str_starts_with(trim($line), '@')) {
				break;
			}

			$description[] = $line;
		}

		return trim(implode("\n", $description));
	}

	/**
	 *
	 * Parses a tag from a docblock
	 *
	 * @param string $docComment
	 * @param string $tag
	 * @return string|null
	 */
	private function parseTag(string $docComment, string $tag): ?string
	{
		if (preg_match('/@' . preg_quote($tag, '/') . '\s+(.+)$/m', $docComment, $matches)) {
			return trim($matches[1]);
		}

		return null;
	}

	/**
	 *
	 * Parses tags from a docblock
	 *
	 * @param string $docComment
	 * @return string[]
	 */
	private function parseTags(string $docComment): array
	{
		if (preg_match('/@tags\s+(.+)$/m', $docComment, $matches)) {
			return array_map('trim', explode(',', $matches[1]));
		}

		return [];
	}

	/**
	 *
	 * Checks if a docblock contains a tag
	 *
	 * @param string $docComment
	 * @param string $tag
	 * @return bool
	 */
	private function hasTag(string $docComment, string $tag): bool
	{
		return (bool)preg_match('/@' . preg_quote($tag, '/') . '\b/', $docComment);
	}

	/**
	 *
	 * Attempts to guess the location of a parameter based on its name and method
	 *
	 * @param string $paramName
	 * @param ReflectionMethod $method
	 * @return string
	 */
	private function guessParamLocation(string $paramName, ReflectionMethod $method): string
	{
		$docComment = $method->getDocComment();

		if ($docComment) {
			// Check if param appears in route path from @endpoint
			if (preg_match('/@endpoint\s+\w+\s+(\S+)/', $docComment, $matches)) {
				if (str_contains($matches[1], '{' . $paramName . '}')) {
					return 'path';
				}
			}

			// Check HTTP method for body params
			if (preg_match('/@endpoint\s+(\w+)/', $docComment, $matches)) {
				$httpMethod = strtoupper($matches[1]);

				if (in_array($httpMethod, ['POST', 'PUT', 'PATCH'], true)) {
					return 'body';
				}
			}
		}

		return 'query';
	}

	/**
	 * Converts a string to a URL-friendly slug
	 *
	 * @param string $name
	 * @return string
	 */
	private function slugify(string $name): string
	{
		return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
	}
}