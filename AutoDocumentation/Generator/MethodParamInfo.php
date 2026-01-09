<?php

namespace AutoDocumentation\Generator;

/**
 * Represents metadata about a method parameter for documentation purposes
 *
 * @group Generator
 */
readonly class MethodParamInfo
{

	/**
	 *
	 * @param string $name The name of the parameter
	 * @param array $type The type of the parameter as an array of strings
	 * @param bool $nullable Whether the parameter can be null
	 * @param bool $hasDefault Whether the parameter has a default value
	 * @param mixed $default The default value of the parameter, or null if none
	 */
	public function __construct(
		public string $name,
		public array $type,
		public bool $nullable,
		public bool $hasDefault,
		public mixed $default,
	) {}
}