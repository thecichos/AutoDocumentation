<?php

namespace AutoDocumentation\Generator;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

class TypeRenderer
{
	public function __construct(
		private TypeRegistry $registry
	) {}

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

	private function renderNamedType(ReflectionNamedType $type): string
	{
		$name = $type->getName();
		$prefix = $type->allowsNull() && $name !== 'null' ? '?' : '';

		return $prefix . $this->renderTypeName($name);
	}

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

	private function wrapBuiltin(string $name): string
	{
		return sprintf(
			'<span class="type-builtin">%s</span>',
			htmlspecialchars($name)
		);
	}

	/**
	 * Parse generic inner types, handling nested generics
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