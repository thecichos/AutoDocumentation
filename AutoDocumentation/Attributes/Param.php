<?php

namespace AutoDocumentation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::IS_REPEATABLE)]
class Param
{
	public function __construct(
		public string $description,
		public bool $required = true,
		public mixed $example = null,
		public string $in = 'query'
	) {}
}