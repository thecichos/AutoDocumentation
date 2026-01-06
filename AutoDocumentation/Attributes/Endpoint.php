<?php

namespace AutoDocumentation\Attributes;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Endpoint
{
	public function __construct(
		public string $method,
		public string $path,
		public string $summary,
		public array $responses = [],
		public bool $deprecated = false
	) {}
}