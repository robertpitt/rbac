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
	//Resolve an identifer into a primary idnetifer
	public function resolve($type, $identifer);

	// Roles
	public function createRole($name, $description, $parent);
	public function getRole($id);
	public function updateRole($id, $title, $description);
	public function deleteRole($id);

	//Permissions
	public function createPermission($name, $description, $parent);
	public function getPermission($id);
	public function updatePermission($id, $title, $description);
	public function deletePermission($id);
}