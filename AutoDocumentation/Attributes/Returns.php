<?php

namespace AutoDocumentation\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Returns {
	public function __construct(
		public string $type,          // "User", "User[]", "PaginatedResult<User>"
		public string $description = '',
		public int $statusCode = 200
	) {}
}