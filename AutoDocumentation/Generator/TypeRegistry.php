<?php

namespace AutoDocumentation\Generator;

use AutoDocumentation\Attributes\Method;
use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Property;
use ReflectionClass;
use ReflectionProperty;
use ReflectionMethod;

class TypeRegistry
{
	/** @var array<class-string, TypeInfo> */
	private array $types = [];

	public function register(ReflectionClass $class): void
	{
		$attr = $class->getAttributes(Documentable::class)[0] ?? null;

		if (!$attr) {
			return;
		}

		$doc = $attr->newInstance();

		$this->types[$class->getName()] = new TypeInfo(
			fqcn: $class->getName(),
			shortName: $class->getShortName(),
			slug: $doc->slug ?? $this->slugify($class->getShortName()),
			group: $doc->group,
			description: $doc->description,
			properties: $this->extractProperties($class),
			methods: $this->extractMethods($class),
		);
	}

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

		foreach ($class->getProperties() as $prop) {

			$attr = $prop->getAttributes(Property::class)[0] ?? null;
			$propDoc = $attr?->newInstance();

			$type = $prop->getType()?->getName() ?? 'mixed';
			$nullable = $prop->getType()?->allowsNull() ?? true;

			// Check for array type in docblock
			$docType = $this->parseDocBlockType($prop);

			$properties[] = new PropertyInfo(
				name: $prop->getName(),
				type: $docType ?? $type,
				nullable: $nullable,
				description: $propDoc?->description ?? '',
				example: $propDoc?->example,
				deprecated: $propDoc?->deprecated ?? false,
				accessibility: match (true) {
					$prop->isPublic() => Accessibility::Public,
					$prop->isProtected() => Accessibility::Protected,
					default => Accessibility::Private,
				},
			);
		}

		return $properties;
	}

	private function extractMethods(ReflectionClass $class): array {
		$methods = [];

		foreach ($class->getMethods() as $method) {
			// Skip magic methods and constructor
			if (str_starts_with($method->getName(), '__')) {
				continue;
			}

			$attr = $method->getAttributes(Method::class)[0] ?? null;

			// Only document methods with #[Method] attribute
			if ($attr === null) {
				continue;
			}

			$methodDoc = $attr->newInstance();

			$parameters = [];
			foreach ($method->getParameters() as $param) {
				$type = $param->getType();
				$docType = $this->parseMethodParamDocBlock($method, $param->getName());

				$parameters[] = new MethodParamInfo(
					name: $param->getName(),
					type: $docType ?? $type?->getName() ?? 'mixed',
					nullable: $type?->allowsNull() ?? true,
					hasDefault: $param->isDefaultValueAvailable(),
					default: $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
				);
			}

			$returnType = $method->getReturnType();
			$docReturn = $this->parseMethodReturnDocBlock($method);

			$methods[] = new MethodInfo(
				name: $method->getName(),
				description: $methodDoc->description,
				parameters: $parameters,
				returnType: $docReturn ?? $returnType?->getName(),
				returnDescription: $this->parseMethodReturnDescription($method),
				example: $methodDoc->example,
				deprecated: $methodDoc->deprecated,
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

	private function parseMethodParamDocBlock(ReflectionMethod $method, string $paramName): ?string
	{
		$docComment = $method->getDocComment();

		if (!$docComment) {
			return null;
		}

		$pattern = '/@param\s+([^\s]+)\s+\$' . preg_quote($paramName, '/') . '/';

		if (preg_match($pattern, $docComment, $matches)) {
			return $matches[1];
		}

		return null;
	}

	private function parseMethodReturnDocBlock(ReflectionMethod $method): ?string
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

	private function parseMethodReturnDescription(ReflectionMethod $method): ?string
	{
		$docComment = $method->getDocComment();

		if (!$docComment) {
			return null;
		}

		if (preg_match('/@return\s+[^\s]+\s+(.+)$/m', $docComment, $matches)) {
			return trim($matches[1]);
		}

		return null;
	}

	private function parseDocBlockType(ReflectionProperty $prop): ?string
	{
		$docComment = $prop->getDocComment();

		if (!$docComment) {
			return null;
		}

		// Match @var Type or @var Type[]
		if (preg_match('/@var\s+([^\s]+)/', $docComment, $matches)) {
			return $matches[1];
		}

		return null;
	}

	private function slugify(string $name): string
	{
		return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
	}
}