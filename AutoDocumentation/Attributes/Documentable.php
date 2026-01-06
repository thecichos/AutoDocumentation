<?php

namespace AutoDocumentation\Attributes;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Documentable
{
	public function __construct(
		public string $description,
		public ?string $slug = null,
		public string $group = 'Models',
	) {}
}