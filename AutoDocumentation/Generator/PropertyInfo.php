<?php

namespace AutoDocumentation\Generator;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Param;
use AutoDocumentation\Attributes\Property;

#[Documentable(
	description: 'Represents metadata about a class property for documentation purposes',
	slug: 'property-info',
	group: 'Generator'
)]
readonly class PropertyInfo
{
	public function __construct(
		#[Property(description: 'The name of the property', example: 'email')]
		public string $name,
		#[Property(description: 'The type of the property as a string', example: 'string')]
		public string $type,
		#[Property(description: 'Whether the property can be null')]
		public bool $nullable,
		#[Property(description: 'Human-readable description of the property')]
		public string $description,
		#[Property(description: 'Example value for the property')]
		public mixed $example = null,
		#[Property(description: 'Whether the property is deprecated')]
		public bool $deprecated = false,
		#[Property(description: 'Visibility level of the property')]
		public Accessibility $accessibility = Accessibility::Public,
	) {}
}