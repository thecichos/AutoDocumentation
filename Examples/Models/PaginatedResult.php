<?php

namespace Examples\Models;

/**
 * Wrapper for paginated API responses
 *
 * @group Core
 */
class PaginatedResult
{
	/**
	 * Array of items for current page
	 * @var mixed[]
	 */
	public array $data;

	/**
	 * Current page number
	 * @var int
	 * @example 1
	 */
	public int $page;

	/**
	 * Items per page
	 * @var int
	 * @example 20
	 */
	public int $perPage;

	/**
	 * Total number of items
	 * @var int
	 * @example 150
	 */
	public int $total;

	/**
	 * Total number of pages
	 * @var int
	 * @example 8
	 */
	public int $totalPages;
}