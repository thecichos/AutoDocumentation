<?php

namespace AutoDocumentation\Generator;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * Renders PHP type information as HTML or markdown with links to documented types
 *
 * @group Generator
 */
class TypeRenderer
{
	public function __construct(
		private TypeRegistry $registry
	) {}

	/**
	 * Renders a ReflectionType as an HTML string, handling union, intersection, and named types
	 *
	 * @return string HTML representation of the type
	 */
	public function render(?ReflectionType $type): string
	{
		if ($type === null) {
			return $this->wrapBuiltin('mixed');
		}

		if ($type instanceof ReflectionUnionType) {
			$parts = array_map(
				fn($t) => $this->renderNamedType($t),
				$type->getTypes()
			);
			return implode('|', $parts);
		}

		if ($type instanceof ReflectionIntersectionType) {
			$parts = array_map(
				fn($t) => $this->renderNamedType($t),
				$type->getTypes()
			);
			return implode('&', $parts);
		}

		if ($type instanceof ReflectionNamedType) {
			return $this->renderNamedType($type);
		}

		return $this->wrapBuiltin('mixed');
	}

	/**
	 * Renders a type from its string representation
	 * Supports nullable, arrays, generics, and unions
	 *
	 * @param string $typeName Type name (e.g. "User", "?string", "User[]")
	 * @return string HTML representation
	 */
	public function renderFromString(string $typeName): string
	{
		// Handle nullable: ?Type
		if (str_starts_with($typeName, '?')) {
			$inner = $this->renderFromString(substr($typeName, 1));
			return '?' . $inner;
		}

		// Handle arrays: Type[]
		if (preg_match('/^(.+)\[\]$/', $typeName, $matches)) {
			$inner = $this->renderFromString($matches[1]);
			return $inner . '[]';
		}

		// Handle generics: Collection<Type> or array<Key, Type>
		if (preg_match('/^(\w+)<(.+)>$/', $typeName, $matches)) {
			$outer = $matches[1];
			$innerTypes = $this->parseGenericInner($matches[2]);
			$renderedInner = implode(', ', array_map(
				fn($t) => $this->renderFromString(trim($t)),
				$innerTypes
			));
			return htmlspecialchars($outer) . '&lt;' . $renderedInner . '&gt;';
		}

		// Handle union: Type|OtherType
		if (str_contains($typeName, '|')) {
			$parts = explode('|', $typeName);
			return implode('|', array_map(
				fn($t) => $this->renderFromString(trim($t)),
				$parts
			));
		}

		return $this->renderTypeName($typeName);
	}

	/**
	 * Renders a ReflectionNamedType with nullable prefix if applicable
	 */
	private function renderNamedType(ReflectionNamedType $type): string
	{
		$name = $type->getName();
		$prefix = $type->allowsNull() && $name !== 'null' ? '?' : '';

		return $prefix . $this->renderTypeName($name);
	}

	/**
	 * Renders a type name as a link if documented, otherwise as a builtin type span
	 */
	private function renderTypeName(string $name): string
	{
		$typeInfo = $this->registry->resolve($name);

		if ($typeInfo !== null) {
			return sprintf(
				'<a href="%s" class="type-link" title="%s">%s</a>',
				htmlspecialchars($typeInfo->getAnchor()),
				htmlspecialchars($typeInfo->description),
				htmlspecialchars($typeInfo->shortName)
			);
		}

		return $this->wrapBuiltin($name);
	}

	/**
	 * Wraps a builtin type name in an HTML span element
	 */
	private function wrapBuiltin(string $name): string
	{
		return sprintf(
			'<span class="type-builtin">%s</span>',
			htmlspecialchars($name)
		);
	}

	/**
	 * Parse generic inner types, handling nested generics
	 *
	 * @return string[]
	 */
	private function parseGenericInner(string $inner): array
	{
		$types = [];
		$current = '';
		$depth = 0;

		for ($i = 0; $i < strlen($inner); $i++) {
			$char = $inner[$i];

			if ($char === '<') {
				$depth++;
				$current .= $char;
			} elseif ($char === '>') {
				$depth--;
				$current .= $char;
			} elseif ($char === ',' && $depth === 0) {
				$types[] = $current;
				$current = '';
			} else {
				$current .= $char;
			}
		}

		if ($current !== '') {
			$types[] = $current;
		}

		return $types;
	}
}