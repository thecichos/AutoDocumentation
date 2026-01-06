<?php

namespace AutoDocumentation\Generator;

readonly class MethodParamInfo
{
	public function __construct(
		public string $name,
		public string $type,
		public bool $nullable,
		public bool $hasDefault,
		public mixed $default,
	) {}
}