<?php

namespace AutoDocumentation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Response
{
	public function __construct(
		public int $statusCode,
		public string $description,
		public ?string $type = null
	){}
}