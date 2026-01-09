<?php

namespace AutoDocumentation\Generator;

/**
 * Represents metadata about a method for documentation purposes
 *
 * @group Generator
 */
readonly class MethodInfo
{
	/**
	 *
	 * @param string $name The name of the method
	 * @param string $description A description of the method
	 * @param array $parameters An array of MethodParamInfo objects representing the parameters of the method
	 * @param string|null $returnType The return type of the method as a string
	 * @param string|null $returnDescription A description of the return value of the method
	 * @param string|null $example An example of how to use the method
	 * @param bool $deprecated Whether the method is deprecated
	 * @param bool $isStatic Whether the method is static
	 * @param Accessibility $accessibility The accessibility of the method
	 */
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