<?php

namespace Examples\Models;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Method;
use AutoDocumentation\Attributes\Property;
use AutoDocumentation\Attributes\Returns;

#[Documentable('Product category for organization', group: 'Shop')]
class Category
{
	#[Property('The blue number', example: 42)]
	protected int $theBlue;
	#[Property('The red string', example: 'foo')]
	private string $theRed;

	#[Property('Category ID', example: 5)]
	public int $id;

	#[Property('Category name', example: 'Electronics')]
	public string $name;

	#[Property('URL-friendly slug', example: 'electronics')]
	public string $slug;

	#[Property('Parent category for nesting')]
	public ?Category $parent;

	#[Method('Private method for internal use')]
	#[Returns('void')]
	private function doingTheThing() : void {}

	#[Method('Get the category itself')]
	public function getCategory(): Category {
		return $this;
	}
}