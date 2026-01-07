<?php

namespace AutoDocumentation\Generator;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Method;
use AutoDocumentation\Attributes\Param;
use AutoDocumentation\Attributes\Property;

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
		#[Property(description: 'Fully qualified class name of the type')]
		public string $fqcn,
		#[Property(description: 'Short class name without namespace')]
		public string $shortName,
		#[Property(description: 'URL-friendly identifier for the type')]
		public string $slug,
		#[Property(description: 'Category group for organizing types in documentation')]
		public string $group,
		#[Property(description: 'Human-readable description of the type')]
		public string $description,
		#[Property(description: 'List of property definitions for this type', example: '[]')]
		public array $properties = [],
		#[Property(description: 'List of method definitions for this type', example: '[]')]
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