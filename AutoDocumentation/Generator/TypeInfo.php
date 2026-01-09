<?php

namespace AutoDocumentation\Generator;

/**
 * Represents metadata about a class or interface for documentation purposes
 *
 * @group Generator
 */
readonly class TypeInfo
{
	/**
	 *
	 * @param string $fqcn Fully qualified class name of the type
	 * @param string $shortName Short class name without namespace
	 * @param string $slug URL-friendly identifier for the type
	 * @param string $group Category group for organizing types in documentation
	 * @param string $description Human-readable description of the type
	 * @param PropertyInfo[] $properties List of property definitions for this type
	 * @param MethodInfo[] $methods List of method definitions for this type
	 */
	public function __construct(
		public string $fqcn,
		public string $shortName,
		public string $slug,
		public string $group,
		public string $description,
		public array $properties = [],
		public array $methods = []
	) {}

	/**
	 * Returns a Markdown anchor for this type
	 */
	public function getAnchor(): string
	{
		return '#type-' . $this->slug;
	}

	/**
	 * Returns a URL for this type in the documentation
	 */
	public function getUrl(): string
	{
		return '/docs/types/' . $this->slug;
	}
}