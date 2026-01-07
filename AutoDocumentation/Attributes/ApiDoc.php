<?php

namespace AutoDocumentation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
#[Documentable("Attribute describing a controller for endpoints", "api-doc", "Attributes")]
class ApiDoc
{
	public function __construct(
		#[Property(
			description: "Description of the controller",
		)]
		public string $description,
		#[Property(
			description: "Tags for the controller",
		)]
		public array $tags,
		#[Property(
			description: "Version of the controller",
		)]
		public string $version = "1.0.0",
		#[Property(
			description: "Whether it has been deprecated",
		)]
		public bool $deprecated = false
	) {}
}