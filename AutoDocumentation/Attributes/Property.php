<?php

namespace AutoDocumentation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Property {
	public function __construct(
		public string $description,
		public mixed $example = null
	) {}
}