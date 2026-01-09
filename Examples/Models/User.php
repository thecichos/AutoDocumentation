<?php

namespace Examples\Models;

/**
 * Represents a registered user in the system
 *
 * @group Core
 */
class User
{
	/**
	 * Unique identifier
	 * @var int
	 * @example 42
	 */
	public int $id;

	/**
	 * User email address
	 * @var string
	 * @example "john@example.com"
	 */
	public string $email;

	/**
	 * Display name
	 * @var string
	 * @example "John Doe"
	 */
	public string $name;

	/**
	 * Profile avatar URL
	 * @var string|null
	 */
	public ?string $avatarUrl;

	/**
	 * Account creation timestamp
	 * @var string
	 * @example "2024-01-15T10:30:00Z"
	 */
	public string $createdAt;

	/**
	 * Whether email is verified
	 * @var bool
	 */
	public bool $isVerified;

	/**
	 * Assigned roles
	 * @var Roles[]
	 */
	public array $roles;
}