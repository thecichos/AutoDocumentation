<?php

namespace AutoDocumentation\Generator;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Method;
use AutoDocumentation\Attributes\Param;

#[Documentable(
	description: 'Represents metadata and structure information about a documented type',
	slug: 'type-info',
	group: 'Generator'
)]
readonly class TypeInfo
{
	/**
	 * @param PropertyInfo[] $properties
	 */
	public function __construct(
		#[Param(description: 'Fully qualified class name of the type')]
		public string $fqcn,
		#[Param(description: 'Short class name without namespace')]
		public string $shortName,
		#[Param(description: 'URL-friendly identifier for the type')]
		public string $slug,
		#[Param(description: 'Category group for organizing types in documentation')]
		public string $group,
		#[Param(description: 'Human-readable description of the type')]
		public string $description,
		#[Param(description: 'List of property definitions for this type', required: false, example: '[]')]
		public array $properties = [],
		#[Param(description: 'List of method definitions for this type', required: false, example: '[]')]
		public array $methods = []
	) {}

	#[Method(description: 'Returns the HTML anchor link for this type', example: '#type-user')]
	public function getAnchor(): string
	{
		return '#type-' . $this->slug;
	}

	#[Method(description: 'Returns the full documentation URL path for this type', example: '/docs/types/user')]
	public function getUrl(): string
	{
		return '/docs/types/' . $this->slug;
	}
}