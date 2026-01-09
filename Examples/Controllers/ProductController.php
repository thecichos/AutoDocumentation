<?php

namespace Examples\Controllers;

use Examples\Models\PaginatedResult;
use Examples\Models\Product;

/**
 * @group Products
 */
class ProductController
{

	/**
	 * @param int $page
	 * @param int|null $categoryId
	 * @param int|null $minPrice
	 * @param int|null $maxPrice
	 * @param bool $inStock
	 * @return PaginatedResult
	 */
	public function index(
		int $page = 1,
		?int $categoryId = null,
		?int $minPrice = null,
		?int $maxPrice = null,
		bool $inStock = false
	): PaginatedResult {
		// Implementation...
	}

	/**
	 * @param int $id
	 * @return Product
	 */
	public function show(
		int $id
	): Product {
		// Implementation...
	}

	/**
	 * @param string $name
	 * @param string $description
	 * @param int $priceInCents
	 * @param int $categoryId
	 * @param int $stock
	 * @return Product
	 */
	public function store(
		string $name,
		string $description,
		int $priceInCents,
		int $categoryId,
		int $stock = 0
	): Product {
		// Implementation...
	}

	/**
	 * @param int $id
	 * @param int $adjustment
	 * @return Product
	 */
	public function updateStock(
		int $id,
		int $adjustment
	): Product {
		// Implementation...
	}

	/**
	 * @param int $id
	 * @deprecated
	 */
	public function destroy(
		int $id
	): void {
		// Implementation...
	}
}