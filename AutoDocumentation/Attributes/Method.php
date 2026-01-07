<?php

namespace AutoDocumentation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
#[Documentable(
    "Describes a method",
	"method",
	"Attributes"
)]
class Method
{
	public function __construct(
		#[Property(
			"Describes the method",
		)]
		public string $description,
		#[Property(
			"Sets a version number",
		)]
		public string $version = "1.0.0",
		#[Property(
			"Creates an example of the code used",
		)]
		public ?string $example = null,
		#[Property(
			"Whether the method is deprecated",
		)]
		public bool $deprecated = false
	) {}
}