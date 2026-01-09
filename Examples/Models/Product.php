<?php

namespace Examples\Models;

/**
 * A product in the catalog
 *
 * @group Shop
 */
class Product
{
	/**
	 * Unique product ID
	 * @var int
	 * @example 101
	 */
	public int $id;

	/**
	 * Product name
	 * @var string
	 * @example "Wireless Keyboard"
	 */
	public string $name;

	/**
	 * Product description
	 * @var string
	 */
	public string $description;

	/**
	 * Price in cents
	 * @var int
	 * @example 4999
	 */
	public int $priceInCents;

	/**
	 * Stock quantity
	 * @var int
	 * @example 150
	 */
	public int $stock;

	/**
	 * Product category
	 * @var Category
	 */
	public Category $category;

	/**
	 * Product is active
	 * @var bool
	 * @example true
	 */
	public bool $isActive;

	/**
	 * Old field, use priceInCents instead
	 * @var float|null
	 * @deprecated Use priceInCents instead
	 */
	public ?float $price;
}