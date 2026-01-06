<?php

namespace Examples\Controllers;

use AutoDocumentation\Attributes\ApiDoc;
use AutoDocumentation\Attributes\Endpoint;
use AutoDocumentation\Attributes\Param;
use AutoDocumentation\Attributes\Response;
use AutoDocumentation\Attributes\Returns;
use Examples\Models\PaginatedResult;
use Examples\Models\User;

#[ApiDoc(
	description: 'Manage user accounts and profiles',
	version: '2.0',
	tags: ['users', 'accounts'],
	deprecated: false
)]
class UserController
{
	#[Endpoint('GET', '/users', 'List all users')]
	#[Returns('User[]', 'Paginated list of users')]
	#[Response(401, 'Unauthorized - Invalid or missing token')]
	public function index(
		#[Param('Page number', required: false, example: 1, in: 'query')]
		int $page = 1,
		#[Param('Items per page', required: false, example: 20, in: 'query')]
		int $limit = 20,
		#[Param('Filter by verification status', required: false, in: 'query')]
		?bool $verified = null
	): PaginatedResult {
		// Implementation...
	}

	#[Endpoint('GET', '/users/{id}', 'Get user by ID', description: 'Retrieves detailed information about a specific user including their roles and profile data.')]
	#[Returns('User', 'The requested user')]
	#[Response(404, 'User not found')]
	#[Response(401, 'Unauthorized')]
	public function show(
		#[Param('User ID', example: 42, in: 'path')]
		int $id
	): User {
		// Implementation...
	}

	#[Endpoint('POST', '/users', 'Create a new user')]
	#[Returns('User', 'The created user', statusCode: 201)]
	#[Response(422, 'Validation error', type: 'ValidationError')]
	#[Response(409, 'Email already exists')]
	public function store(
		#[Param('User email address', example: 'newuser@example.com', in: 'body')]
		string $email,
		#[Param('User password (min 8 chars)', in: 'body')]
		string $password,
		#[Param('Display name', required: false, example: 'Jane Doe', in: 'body')]
		?string $name = null
	): User {
		// Implementation...
	}

	#[Endpoint('PUT', '/users/{id}', 'Update user')]
	#[Returns('User', 'The updated user')]
	#[Response(404, 'User not found')]
	#[Response(422, 'Validation error')]
	public function update(
		#[Param('User ID', example: 42, in: 'path')]
		int $id,
		#[Param('New email address', required: false, in: 'body')]
		?string $email = null,
		#[Param('New display name', required: false, in: 'body')]
		?string $name = null
	): User {
		// Implementation...
	}

	#[Endpoint('DELETE', '/users/{id}', 'Delete user', description: 'Permanently removes a user account. This action cannot be undone.')]
	#[Response(204, 'User deleted successfully')]
	#[Response(404, 'User not found')]
	#[Response(403, 'Cannot delete your own account')]
	public function destroy(
		#[Param('User ID', example: 42, in: 'path')]
		int $id
	): void {
		// Implementation...
	}

	#[Endpoint('GET', '/users/{id}/roles', 'Get user roles')]
	#[Returns('Role[]', 'List of assigned roles')]
	public function roles(
		#[Param('User ID', example: 42, in: 'path')]
		int $id
	): array {
		// Implementation...
	}
}