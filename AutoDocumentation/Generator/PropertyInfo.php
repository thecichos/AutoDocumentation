<?php

namespace AutoDocumentation\Generator;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Param;

#[Documentable(
	description: 'Represents metadata about a class property for documentation purposes',
	slug: 'property-info',
	group: 'Generator'
)]
readonly class PropertyInfo
{
	public function __construct(
		#[Param(description: 'The name of the property', example: 'email')]
		public string $name,
		#[Param(description: 'The type of the property as a string', example: 'string')]
		public string $type,
		#[Param(description: 'Whether the property can be null')]
		public bool $nullable,
		#[Param(description: 'Human-readable description of the property')]
		public string $description,
		#[Param(description: 'Example value for the property', required: false)]
		public mixed $example = null,
		#[Param(description: 'Whether the property is deprecated', required: false)]
		public bool $deprecated = false,
		#[Param(description: 'Visibility level of the property', required: false)]
		public Accessibility $accessibility = Accessibility::Public,
	) {}
}