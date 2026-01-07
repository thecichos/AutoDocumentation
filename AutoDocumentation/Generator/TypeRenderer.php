<?php

namespace AutoDocumentation\Generator;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;
use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Method;
use AutoDocumentation\Attributes\Param;

#[Documentable(
	description: 'Renders PHP type information as HTML with links to documented types',
	slug: 'type-renderer',
	group: 'Generator'
)]
class TypeRenderer
{
	public function __construct(
		#[Param(description: 'Registry containing all documented types for resolving links')]
		private TypeRegistry $registry
	) {}

	#[Method(
		description: 'Renders a ReflectionType as an HTML string, handling union, intersection, and named types',
		example: '<a href="#type-user">User</a>|<span class="type-builtin">null</span>'
	)]
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

	#[Method(
		description: 'Renders a type from its string representation, supporting nullable, arrays, generics, and unions',
		example: 'User[] → <a href="#type-user">User</a>[]'
	)]
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

	#[Method(
		description: 'Renders a ReflectionNamedType with nullable prefix if applicable',
		example: '?<a href="#type-user">User</a>'
	)]
	private function renderNamedType(ReflectionNamedType $type): string
	{
		$name = $type->getName();
		$prefix = $type->allowsNull() && $name !== 'null' ? '?' : '';

		return $prefix . $this->renderTypeName($name);
	}

	#[Method(
		description: 'Renders a type name as a link if documented, otherwise as a builtin type span',
		example: '<a href="#type-user" class="type-link" title="User model">User</a>'
	)]
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

	#[Method(
		description: 'Wraps a builtin type name in an HTML span element',
		example: '<span class="type-builtin">string</span>'
	)]
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
	#[Method(
		description: 'Parses generic inner types from a string, correctly handling nested generics',
		example: 'array<string, Collection<User>> → ["string", "Collection<User>"]'
	)]
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