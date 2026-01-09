<?php

namespace AutoDocumentation\Generator;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * Registry for tracking documented types and resolving type links
 *
 * @group Generator
 */
class TypeRegistry
{
	/** @var array<class-string, TypeInfo> */
	private array $types = [];

	/**
	 * Registers a class for documentation.
	 * Extracts metadata from PHPDoc comments.
	 *
	 * PHPDoc tags supported on classes:
	 * - @slug custom-slug (URL-friendly identifier)
	 * - @group GroupName (category for grouping in docs)
	 * - Description from the docblock summary
	 */
	public function register(ReflectionClass $class): void
	{
		$docComment = $class->getDocComment() ?: '';
		
		$this->types[$class->getName()] = new TypeInfo(
			fqcn: $class->getName(),
			shortName: $class->getShortName(),
			slug: $this->parseTag($docComment, 'slug') ?? $this->slugify($class->getShortName()),
			group: $this->parseTag($docComment, 'group') ?? 'Models',
			description: $this->parseDescription($docComment),
			properties: $this->extractProperties($class),
			methods: $this->extractMethods($class),
		);
	}

	/**
	 * Resolves a type by its fully-qualified class name or short name
	 */
	public function resolve(string $typeName): ?TypeInfo
	{
		// Direct FQCN match
		if (isset($this->types[$typeName])) {
			return $this->types[$typeName];
		}

		// Short name match (e.g., "User" → "App\Models\User")
		foreach ($this->types as $info) {
			if ($info->shortName === $typeName) {
				return $info;
			}
		}

		return null;
	}

	/**
	 * Checks whether a type name can be linked in documentation
	 */
	public function isLinkable(string $typeName): bool
	{
		return $this->resolve($typeName) !== null;
	}

	/**
	 * @return array<class-string, TypeInfo>
	 */
	public function getAll(): array
	{
		return $this->types;
	}

	/**
	 * @return array<string, TypeInfo[]> Grouped by category
	 */
	public function getAllGrouped(): array
	{
		$grouped = [];

		foreach ($this->types as $type) {
			$grouped[$type->group][] = $type;
		}

		ksort($grouped);
		return $grouped;
	}

	/**
	 * @return PropertyInfo[]
	 */
	private function extractProperties(ReflectionClass $class): array
	{
		$properties = [];

		$__construct = $class->getConstructor()?->getDocComment();

		foreach ($class->getProperties() as $prop) {
			if ($prop->getDocComment()) {
				$docComment = $prop->getDocComment();
			} elseif ($__construct) {
				$docComment = self::parseParamDescription($__construct, $prop->getName()) ?? '';
			} else {
				$docComment = '';
			}
			
			$type = $prop->getType();
			$typeName = $this->getTypeString($type) ?? 'mixed';
			$nullable = $type?->allowsNull() ?? true;

			// Check for array type in docblock via @var
			$docType = $this->parseVarType($docComment);

			$properties[] = new PropertyInfo(
				name: $prop->getName(),
				type: $docType ?? $type,
				nullable: $nullable,
				description: $this->parseDescription($docComment),
				example: $this->parseExample($docComment),
				deprecated: $this->hasTag($docComment, 'deprecated'),
				accessibility: match (true) {
					$prop->isPublic() => Accessibility::Public,
					$prop->isProtected() => Accessibility::Protected,
					default => Accessibility::Private,
				},
			);
		}

		return $properties;
	}

	/**
	 * Converts a ReflectionType to a string representation
	 * Handles named types, union types, and intersection types
	 */
	private function getTypeString(?ReflectionType $type): ?string
	{
		if ($type === null) {
			return null;
		}

		if ($type instanceof ReflectionNamedType) {
			return $type->getName();
		}

		if ($type instanceof ReflectionUnionType) {
			$types = array_map(
				fn(ReflectionType $t) => $this->getTypeString($t),
				$type->getTypes()
			);
			return implode('|', $types);
		}

		if ($type instanceof ReflectionIntersectionType) {
			$types = array_map(
				fn(ReflectionType $t) => $this->getTypeString($t),
				$type->getTypes()
			);
			return implode('&', $types);
		}

		return 'mixed';
	}

	/**
	 * Resolves special type keywords (self, static, parent) to actual class names
	 * Handles both simple types and union/intersection types
	 */
	private function resolveSpecialTypes(string $typeName, ReflectionClass $class): string
	{
		// Handle union types: Type1|Type2
		if (str_contains($typeName, '|')) {
			$parts = explode('|', $typeName);
			$resolved = array_map(
				fn($part) => $this->resolveSpecialType(trim($part), $class),
				$parts
			);
			return implode('|', $resolved);
		}

		// Handle intersection types: Type1&Type2
		if (str_contains($typeName, '&')) {
			$parts = explode('&', $typeName);
			$resolved = array_map(
				fn($part) => $this->resolveSpecialType(trim($part), $class),
				$parts
			);
			return implode('&', $resolved);
		}

		return $this->resolveSpecialType($typeName, $class);
	}

	/**
	 * Resolves a single special type keyword to an actual class name
	 */
	private function resolveSpecialType(string $typeName, ReflectionClass $class): string
	{
		return match (strtolower($typeName)) {
			'self', 'static' => $class->getName(),
			'parent' => $class->getParentClass() ? $class->getParentClass()->getName() : $typeName,
			default => $typeName,
		};
	}

	/**
	 * Extracts all public methods (excluding magic methods) from a class
	 */
	private function extractMethods(ReflectionClass $class): array
	{
		$methods = [];

		foreach ($class->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
			// Skip magic methods and constructor
			if (str_starts_with($method->getName(), '__')) {
				continue;
			}

			// Skip methods from parent classes
			if ($method->getDeclaringClass()->getName() !== $class->getName()) {
				continue;
			}

			$docComment = $method->getDocComment() ?: '';

			$parameters = [];
			foreach ($method->getParameters() as $param) {
				$type = $param->getType();
				$docType = $this->parseParamType($docComment, $param->getName());

				$parameters[] = new MethodParamInfo(
					name: $param->getName(),
					type: (array)$docType,
					nullable: $type?->allowsNull() ?? true,
					hasDefault: $param->isDefaultValueAvailable(),
					default: $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
				);
			}

			$returnType = $method->getReturnType();
			$docReturn = $this->parseReturnType($docComment);
			
			// Get return type string and resolve special types
			$returnTypeName = $docReturn ?? $this->getTypeString($returnType);
			if ($returnTypeName !== null) {
				$returnTypeName = $this->resolveSpecialTypes($returnTypeName, $class);
			}

			$methods[] = new MethodInfo(
				name: $method->getName(),
				description: $this->parseDescription($docComment),
				parameters: $parameters,
				returnType: $returnTypeName,
				returnDescription: $this->parseReturnDescription($docComment),
				example: $this->parseTag($docComment, 'example'),
				deprecated: $this->hasTag($docComment, 'deprecated'),
				isStatic: $method->isStatic(),
				accessibility: match (true) {
					$method->isPublic() => Accessibility::Public,
					$method->isProtected() => Accessibility::Protected,
					default => Accessibility::Private,
				},
			);
		}

		return $methods;
	}

	/**
	 * Parses the description (summary) from a docblock
	 */
	private function parseDescription(string $docComment): string
	{
		if (empty($docComment)) {
			return '';
		}

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
	 * Parses a specific tag value from a docblock
	 */
	private function parseTag(string $docComment, string $tag): ?string
	{
		if (preg_match('/@' . preg_quote($tag, '/') . '\s+(.+)$/m', $docComment, $matches)) {
			return trim($matches[1]);
		}

		return null;
	}

	/**
	 * Checks if a docblock contains a specific tag
	 */
	private function hasTag(string $docComment, string $tag): bool
	{
		return (bool)preg_match('/@' . preg_quote($tag, '/') . '\b/', $docComment);
	}

	/**
	 * Parses @var type from a property docblock
	 */
	private function parseVarType(string $docComment): ?string
	{
		if (preg_match('/@var\s+(\S+)/', $docComment, $matches)) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Parses @param type for a specific parameter from a method docblock
	 */
	private function parseParamType(string $docComment, string $paramName): ?string
	{
		$pattern = '/@param\s+(\S+)\s+\$' . preg_quote($paramName, '/') . '/';

		if (preg_match($pattern, $docComment, $matches)) {
			return $matches[1];
		}

		return null;
	}

	private function parseParamDescription(string $docComment, string $paramName): ?string
	{
		$pattern = '/@param\s+\S+\s+\$' . preg_quote($paramName, '/') . '\s+(.+)$/m';
		if (preg_match($pattern, $docComment, $matches)) {
			return trim($matches[1]);
		}

		return null;
	}

	/**
	 * Parses @return type from a method docblock
	 */
	private function parseReturnType(string $docComment): ?string
	{
		if (preg_match('/@return\s+(\S+)/', $docComment, $matches)) {
			return $matches[1];
		}

		return null;
	}

	/**
	 * Parses @return description from a method docblock
	 */
	private function parseReturnDescription(string $docComment): ?string
	{
		if (preg_match('/@return\s+\S+\s+(.+)$/m', $docComment, $matches)) {
			return trim($matches[1]);
		}

		return null;
	}

	/**
	 * Parses @example from a docblock
	 */
	private function parseExample(string $docComment): mixed
	{
		$example = $this->parseTag($docComment, 'example');
		
		if ($example === null) {
			return null;
		}

		// Try to decode as JSON for complex examples
		$decoded = json_decode($example, true);
		return $decoded !== null ? $decoded : $example;
	}

	/**
	 * Converts a PascalCase class name to a URL-friendly kebab-case slug
	 */
	private function slugify(string $name): string
	{
		return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
	}
}