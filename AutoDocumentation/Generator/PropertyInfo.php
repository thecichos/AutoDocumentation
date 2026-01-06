<?php

namespace AutoDocumentation\Generator;

readonly class PropertyInfo
{
	public function __construct(
		public string $name,
		public string $type,
		public bool $nullable,
		public string $description,
		public mixed $example = null,
		public bool $deprecated = false
	) {}
}