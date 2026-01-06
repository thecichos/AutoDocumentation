<?php

namespace AutoDocumentation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class ApiDoc
{
	public function __construct(
		public string $description,
		public string $version,
		public array $tags,
		public bool $deprecated
	) {}
}