<?php

namespace AutoDocumentation\Generator;

readonly class MethodInfo
{
	public function __construct(
		public string $name,
		public string $description,
		public array $parameters,
		public ?string $returnType,
		public ?string $returnDescription,
		public ?string $example,
		public bool $deprecated,
		public bool $isStatic,
		public Accessibility $accessibility = Accessibility::Public,
	) {}
}