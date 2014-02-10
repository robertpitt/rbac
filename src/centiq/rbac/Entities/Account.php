<?php
/**
 * RBAC (NIST-L2) implementation
 * @copyright   Copyright (C) 2013 Centiq Limited, +44 (0)115 951 9666
 * @license     All rights reserved
 * @author      Robert Pitt <rpitt@centiq.co.uk>
 * @version     0.0.1
 * @package     Centiq\RBAC
 */
namespace Centiq\RBAC\Entities;

class Account
{
	protected $identity;

	/**
	 * Constructor
	 * @param RBAC    $rbac   Core RBAC Instnace
	 * @param Integer $id User edentity
	 */
	public function __construct(\Centiq\RBAC\Manager $manager, $id)
	{
		/**
		 * Set the manager object.
		 * @var Centiq\RBAC\Manager
		 */
		$this->manager = $manager;

		/**
		 * Set the account id as the account identity
		 * @var Integer
		 */
		$this->id = $id;
	}

	/**
	 * Fetch the account id
	 * @return Integer
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * Fetch the roles
	 * @return Array<Role> Returns an array of roles
	 */
	public function getRoles()
	{
		return $this->manager->getStore()->getRoles($this->id);
	}

	/**
	 * Check to see if the account has access to a role, or is within it it's parents.
	 * @return boolean
	 */
	public function hasRole(Role $role_id)
	{
		return $this->manager->getStore()->hasRole($this->id, $role_id);
	}

	/**
	 * Return a list of permissions for this account
	 * @return Array<Permission>
	 */
	public function getPermissions()
	{

	}

	public function hasPermission()
	{
		
	}
}