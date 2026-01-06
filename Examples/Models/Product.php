<?php

namespace Examples\Models;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Property;

#[Documentable('A product in the catalog', group: 'Shop')]
class Product
{
	#[Property('Unique product ID', example: 101)]
	public int $id;

	#[Property('Product name', example: 'Wireless Keyboard')]
	public string $name;

	#[Property('Product description')]
	public string $description;

	#[Property('Price in cents', example: 4999)]
	public int $priceInCents;

	#[Property('Stock quantity', example: 150)]
	public int $stock;

	#[Property('Product category')]
	public Category $category;

	#[Property('Product is active', example: true)]
	public bool $isActive;

	#[Property('Old field, use priceInCents instead', deprecated: true)]
	public ?float $price;
}