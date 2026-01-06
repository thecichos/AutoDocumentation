<?php

namespace AutoDocumentation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ApiDoc
{
	public function __construct(
		public string $description,
		public array $tags,
		public string $version = "1.0.0",
		public bool $deprecated = false
	) {}
}