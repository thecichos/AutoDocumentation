<?php

namespace AutoDocumentation\Generator;

/**
 * Represents metadata about a class property for documentation purposes
 *
 * @group Generator
 */
readonly class PropertyInfo
{
	/**
	 *
	 * @param string $name The name of the property
	 * @param string $type The type of the property as a string
	 * @param bool $nullable Whether the property can be null
	 * @param string $description Human-readable description of the property
	 * @param mixed $example Example value for the property
	 * @param bool $deprecated Whether the property is deprecated
	 * @param Accessibility $accessibility Visibility level of the property
	 */
	public function __construct(
		public string $name,
		public string $type,
		public bool $nullable,
		public string $description,
		public mixed $example = null,
		public bool $deprecated = false,
		public Accessibility $accessibility = Accessibility::Public,
	) {}
}