<?php

namespace Examples\Models;

/**
 * Product category for organization
 *
 * @group Shop
 */
class Category
{
	/**
	 * The blue number
	 * @var int
	 * @example 42
	 */
	protected int $theBlue;

	/**
	 * The red string
	 * @var string
	 * @example "foo"
	 */
	private string $theRed;

	/**
	 * Category ID
	 * @var int
	 * @example 5
	 */
	public int $id;

	/**
	 * Category name
	 * @var string
	 * @example "Electronics"
	 */
	public string $name;

	/**
	 * URL-friendly slug
	 * @var string
	 * @example "electronics"
	 */
	public string $slug;

	/**
	 * Parent category for nesting
	 * @var Category|null
	 */
	public ?Category $parent;

	/**
	 * Private method for internal use
	 */
	private function doingTheThing(): void {}

	/**
	 * Get the category itself
	 *
	 * @return Category
	 */
	public function getCategory(): Category
	{
		return $this;
	}
}