<?php

namespace Examples\Models;

/**
 * User role for access control
 *
 * @group Core
 */
class Roles
{
	/**
	 * Role identifier
	 * @var string
	 * @example "admin"
	 */
	public string $id;

	/**
	 * Human-readable name
	 * @var string
	 * @example "Administrator"
	 */
	public string $name;

	/**
	 * List of permissions
	 * @var string[]
	 * @example ["users.read", "users.write"]
	 */
	public array $permissions;
}