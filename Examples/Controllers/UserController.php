<?php

namespace Examples\Controllers;

use Examples\Models\PaginatedResult;
use Examples\Models\User;

/**
 * Manage user accounts and profiles
 *
 * @api-doc
 * @tags users, accounts
 * @version 2.0
 */
class UserController
{
	/**
	 * List all users
	 *
	 * @endpoint GET /users
	 * @summary List all users
	 * @param int $page Page number [in:query] [optional] [example:1]
	 * @param int $limit Items per page [in:query] [optional] [example:20]
	 * @param bool|null $verified Filter by verification status [in:query] [optional]
	 * @returns User[] Paginated list of users
	 * @response 401 Unauthorized - Invalid or missing token
	 */
	public function index(
		int $page = 1,
		int $limit = 20,
		?bool $verified = null
	): PaginatedResult {
		// Implementation...
	}

	/**
	 * Retrieves detailed information about a specific user including their roles and profile data.
	 *
	 * @endpoint GET /users/{id}
	 * @summary Get user by ID
	 * @param int $id User ID [in:path] [required] [example:42]
	 * @returns User The requested user
	 * @response 404 User not found
	 * @response 401 Unauthorized
	 */
	public function show(int $id): User {
		// Implementation...
	}

	/**
	 * Create a new user
	 *
	 * @endpoint POST /users
	 * @summary Create a new user
	 * @param string $email User email address [in:body] [required] [example:newuser@example.com]
	 * @param string $password User password (min 8 chars) [in:body] [required]
	 * @param string|null $name Display name [in:body] [optional] [example:Jane Doe]
	 * @returns User The created user @status 201
	 * @response 422 Validation error [ValidationError]
	 * @response 409 Email already exists
	 */
	public function store(
		string $email,
		string $password,
		?string $name = null
	): User {
		// Implementation...
	}

	/**
	 * Update user
	 *
	 * @endpoint PUT /users/{id}
	 * @summary Update user
	 * @param int $id User ID [in:path] [required] [example:42]
	 * @param string|null $email New email address [in:body] [optional]
	 * @param string|null $name New display name [in:body] [optional]
	 * @returns User The updated user
	 * @response 404 User not found
	 * @response 422 Validation error
	 */
	public function update(
		int $id,
		?string $email = null,
		?string $name = null
	): User {
		// Implementation...
	}

	/**
	 * Permanently removes a user account. This action cannot be undone.
	 *
	 * @endpoint DELETE /users/{id}
	 * @summary Delete user
	 * @param int $id User ID [in:path] [required] [example:42]
	 * @response 204 User deleted successfully
	 * @response 404 User not found
	 * @response 403 Cannot delete your own account
	 */
	public function destroy(int $id): void {
		// Implementation...
	}

	/**
	 * Get user roles
	 *
	 * @endpoint GET /users/{id}/roles
	 * @summary Get user roles
	 * @param int $id User ID [in:path] [required] [example:42]
	 * @returns Role[] List of assigned roles
	 */
	public function roles(int $id): array {
		// Implementation...
	}
}