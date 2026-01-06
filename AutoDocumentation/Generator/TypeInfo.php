<?php

namespace AutoDocumentation\Generator;

readonly class TypeInfo
{
	/**
	 * @param PropertyInfo[] $properties
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

	public function getAnchor(): string
	{
		return '#type-' . $this->slug;
	}

	public function getUrl(): string
	{
		return '/docs/types/' . $this->slug;
	}
}