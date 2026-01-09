<?php

namespace Examples\Controllers;

use Examples\Models\PaginatedResult;
use Examples\Models\Product;

/**
 *
 * Manage products in the catalog
 *
 * @api-doc
 * @tags products, accounts
 * @version 2.0
 *
 * @group Products
 */
class ProductController
{

	/**
	 *
	 * @endpoint GET /products
	 * @summary List products
	 * @param int $page
	 * @param int|null $categoryId
	 * @param int|null $minPrice
	 * @param int|null $maxPrice
	 * @param bool $inStock
	 * @return PaginatedResult
	 * @response 401 Unauthorized - Invalid page
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
	 * @endpoint GET /products/{id}
	 * @summary Get product details
	 * @param int $id
	 * @return Product
	 */
	public function show(
		int $id
	): Product {
		// Implementation...
	}

	/**
	 * @endpoint POST /products
	 * @summary Create a new product
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
	 * @endpoint PUT /products/{id}/stock
	 * @summary Adjust product stock
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
	 * @endpoint DELETE /products/{id}
	 * @summary Delete product
	 * @param int $id
	 * @deprecated
	 */
	public function destroy(
		int $id
	): void {
		// Implementation...
	}
}