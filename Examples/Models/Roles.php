<?php

namespace Examples\Models;

use AutoDocumentation\Attributes\Documentable;
use AutoDocumentation\Attributes\Property;

#[Documentable('User role for access control', group: 'Core')]
class Roles
{
	#[Property('Role identifier', example: 'admin')]
	public string $id;

	#[Property('Human-readable name', example: 'Administrator')]
	public string $name;

	/** @var string[] */
	#[Property('List of permissions', example: ['users.read', 'users.write'])]
	public array $permissions;
}