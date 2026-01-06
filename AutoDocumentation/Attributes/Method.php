<?php

namespace AutoDocumentation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Method
{
	public function __construct(
		public string $description,
		public string $version = "1.0.0",
		public ?string $example = null,
		public bool $deprecated = false
	) {}
}