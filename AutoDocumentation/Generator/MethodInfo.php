<?php

namespace AutoDocumentation\Generator;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Param;

#[Documentable(
	description: 'Represents metadata about a class method for documentation purposes',
	slug: 'method-info',
	group: 'Generator'
)]
readonly class MethodInfo
{
	public function __construct(
		#[Param(description: 'The name of the method', example: 'getUserById')]
		public string $name,
		#[Param(description: 'Human-readable description of what the method does')]
		public string $description,
		#[Param(description: 'Array of MethodParamInfo objects describing the method parameters')]
		public array $parameters,
		#[Param(description: 'The return type as a string, or null if void/untyped', required: false)]
		public ?string $returnType,
		#[Param(description: 'Description of what the method returns', required: false)]
		public ?string $returnDescription,
		#[Param(description: 'Example usage or return value of the method', required: false)]
		public ?string $example,
		#[Param(description: 'Whether the method is marked as deprecated')]
		public bool $deprecated,
		#[Param(description: 'Whether the method is static')]
		public bool $isStatic,
		#[Param(description: 'Visibility level of the method', required: false)]
		public Accessibility $accessibility = Accessibility::Public,
	) {}
}