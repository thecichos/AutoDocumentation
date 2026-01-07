<?php

namespace Examples\Controllers;

use AutoDocumentation\Attributes\ApiDoc;
use AutoDocumentation\Attributes\Endpoint;
use AutoDocumentation\Attributes\Param;
use AutoDocumentation\Attributes\Response;
use AutoDocumentation\Attributes\Returns;
use Examples\Models\PaginatedResult;
use Examples\Models\Product;

#[ApiDoc(
	description: 'Product catalog management',
	tags: ['products', 'shop'],
	version: '1.5',
	deprecated: false
)]
class ProductController
{
	#[Endpoint('GET', '/products', 'List products', description: 'Retrieve a paginated list of products with optional filtering.')]
	#[Returns('Product[]', 'List of products')]
	public function index(
		#[Param('Page number', required: false, example: 1, in: 'query')]
		int $page = 1,
		#[Param('Filter by category ID', required: false, example: 5, in: 'query')]
		?int $categoryId = null,
		#[Param('Minimum price in cents', required: false, in: 'query')]
		?int $minPrice = null,
		#[Param('Maximum price in cents', required: false, in: 'query')]
		?int $maxPrice = null,
		#[Param('Only show in-stock items', required: false, example: true, in: 'query')]
		bool $inStock = false
	): PaginatedResult {
		// Implementation...
	}

	#[Endpoint('GET', '/products/{id}', 'Get product details')]
	#[Returns('Product')]
	#[Response(404, 'Product not found')]
	public function show(
		#[Param('Product ID', example: 101, in: 'path')]
		int $id
	): Product {
		// Implementation...
	}

	#[Endpoint('POST', '/products', 'Create product')]
	#[Returns('Product', 'Created product', statusCode: 201)]
	#[Response(422, 'Validation failed')]
	public function store(
		#[Param('Product name', example: 'Mechanical Keyboard', in: 'body')]
		string $name,
		#[Param('Product description', in: 'body')]
		string $description,
		#[Param('Price in cents', example: 12999, in: 'body')]
		int $priceInCents,
		#[Param('Category ID', example: 5, in: 'body')]
		int $categoryId,
		#[Param('Initial stock quantity', required: false, example: 100, in: 'body')]
		int $stock = 0
	): Product {
		// Implementation...
	}

	#[Endpoint('PATCH', '/products/{id}/stock', 'Update stock level')]
	#[Returns('Product')]
	#[Response(404, 'Product not found')]
	public function updateStock(
		#[Param('Product ID', example: 101, in: 'path')]
		int $id,
		#[Param('Stock adjustment (positive or negative)', example: -5, in: 'body')]
		int $adjustment
	): Product {
		// Implementation...
	}

	#[Endpoint('DELETE', '/products/{id}', 'Delete product', deprecated: true)]
	#[Response(204, 'Product deleted')]
	#[Response(404, 'Product not found')]
	public function destroy(
		#[Param('Product ID', example: 101, in: 'path')]
		int $id
	): void {
		// Implementation...
	}
}