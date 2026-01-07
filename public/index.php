<?php

require_once '../vendor/autoload.php';

use AutoDocumentation\AutoDocumentation;
use Examples\Controllers\ProductController;
use Examples\Controllers\UserController;
use Examples\Models\Category;
use Examples\Models\PaginatedResult;
use Examples\Models\Product;
use Examples\Models\Roles;
use Examples\Models\User;

$docs = new AutoDocumentation();

// Register all documentable types (models/DTOs)
$docs->registerTypes([
	User::class,
	Roles::class,
	Product::class,
	Category::class,
	PaginatedResult::class,
]);

// Register API controllers
$docs->registerControllers([
	UserController::class,
	ProductController::class,
]);

// Check for format parameter
$format = $_GET['format'] ?? 'html';

if ($format === 'json') {
	header('Content-Type: application/json');
	echo $docs->toOpenApi([
		'title' => 'Example API',
		'version' => '2.0.0',
		'description' => 'Auto-generated API documentation demo'
	]);
} elseif ($format === "md") {
	header('Content-Type: text/markdown; charset=utf-8');
	echo $docs->toMarkdown();
} else {
	header('Content-Type: text/html; charset=utf-8');
	echo $docs->toHtml();
}