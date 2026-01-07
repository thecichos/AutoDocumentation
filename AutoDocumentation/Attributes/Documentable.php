<?php

namespace AutoDocumentation\Attributes;
use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
#[Documentable(
    "Describes in overall terms a class",
	"doc",
	"Attributes",
)]
class Documentable
{
	public function __construct(
		#[Property(
			description: "A string describing the given class",
		)]
		public string $description,
		#[Property(
			description: "A url friendly path for use in docs",
		)]
		public ?string $slug = null,
		#[Property(
			description: "Determines the what group the class will show up in, in the docs",
		)]
		public string $group = 'Models',
	) {}
}