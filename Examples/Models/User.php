<?php

namespace Examples\Models;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Property;

#[Documentable('Represents a registered user in the system', group: 'Core')]
class User
{
	#[Property('Unique identifier', example: 42)]
	public int $id;

	#[Property('User email address', example: 'john@example.com')]
	public string $email;

	#[Property('Display name', example: 'John Doe')]
	public string $name;

	#[Property('Profile avatar URL')]
	public ?string $avatarUrl;

	#[Property('Account creation timestamp', example: '2024-01-15T10:30:00Z')]
	public string $createdAt;

	#[Property('Whether email is verified')]
	public bool $isVerified;

	/** @var Roles[] */
	#[Property('Assigned roles')]
	public array $roles;
}