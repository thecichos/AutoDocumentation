<?php

namespace AutoDocumentation\Generator;

use AutoDocumentation\Attributes\Documentable;

#[Documentable(
	"Enum that makes it more streamlined to handle accessibility rules",
	"accessibility",
	"Generator"
)]
enum Accessibility: int
{
	case Public = 0;
	case Protected = 1;
	case Private = 2;

	public function html(): string {
		return match ($this) {
			self::Public => "<span class='badge badge-public'>public</span>",
			self::Protected => "<span class='badge badge-protected'>protected</span>",
			self::Private => "<span class='badge badge-private'>private</span>",
		};
	}
}
