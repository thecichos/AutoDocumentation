<?php

namespace AutoDocumentation\Attributes;
use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
#[Documentable(
    "Describes an endpoint",
	"endpoint",
	"Attributes"
)]
class Endpoint
{
	public function __construct(
		#[Property(
			"what http method the endpoint can be called with",
			"eg: post, get, etc."
		)]
		public string $method,
		#[Property(
			"What path the method can be called with",
		)]
		public string $path,
		#[Property(
			"A short description of the endpoint",
		)]
		public string $summary,
		#[Property(
			"A list of response codes"
		)]
		public array $responses = [],
		#[Property(
			"Whether the endpoint is deprecated",
		)]
		public bool $deprecated = false,
		#[Property(
			"A longer description of the endpoint",
		)]
		public ?string $description = null
	) {}
}