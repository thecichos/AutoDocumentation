<?php

namespace AutoDocumentation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ApiDoc
{
	public function __construct(
		public string $description,
		public string $version = "1.0.0",
		public array $tags,
		public bool $deprecated = false
	) {}
}