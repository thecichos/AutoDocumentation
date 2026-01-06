<?php

namespace Examples\Models;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Property;

#[Documentable('Wrapper for paginated API responses', group: 'Core')]
class PaginatedResult
{
	/** @var mixed[] */
	#[Property('Array of items for current page')]
	public array $data;

	#[Property('Current page number', example: 1)]
	public int $page;

	#[Property('Items per page', example: 20)]
	public int $perPage;

	#[Property('Total number of items', example: 150)]
	public int $total;

	#[Property('Total number of pages', example: 8)]
	public int $totalPages;
}