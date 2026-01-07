<?php

namespace AutoDocumentation\Generator;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Param;

#[Documentable(
	description: 'Represents metadata about a method parameter for documentation purposes',
	slug: 'method-param-info',
	group: 'Generator'
)]

readonly class MethodParamInfo
{
	public function __construct(
		#[Param(description: 'The name of the parameter', example: 'userId')]
		public string $name,
		#[Param(description: 'The type of the parameter as a string', example: 'int')]
		public string $type,
		#[Param(description: 'Whether the parameter can be null')]
		public bool $nullable,
		#[Param(description: 'Whether the parameter has a default value')]
		public bool $hasDefault,
		#[Param(description: 'The default value of the parameter, or null if none')]
		public mixed $default,
	) {}
}