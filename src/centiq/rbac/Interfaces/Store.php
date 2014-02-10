<?php
/**
 * RBAC (NIST-L2) implementation
 * @copyright   Copyright (C) 2013 Centiq Limited, +44 (0)115 951 9666
 * @license     All rights reserved
 * @author      Robert Pitt <rpitt@centiq.co.uk>
 * @version     0.0.1
 * @package     Centiq\RBAC
 */
namespace Centiq\RBAC\Interfaces;

/**
 * Storage interface
 */
interface Store
{
	//Resolvers
	public function resolveRole($name);
	public function resolvePermission($name);

	// Roles
	public function getRole($role_id);
	public function getRoleChildren($role_id);
	public function createRole($name, $description, $parent);
	public function assignRole($account_id, $role_id);

	//Permissions
	public function getPermission($permissions_id);
	public function getPermissionChildren($permissions_id);
	public function createPermission($title, $description, $parent);
	public function assignPermission($role_id, $permissions_id);
}