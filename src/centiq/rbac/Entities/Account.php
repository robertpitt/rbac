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

/**
 * Account wrapper object
 */
class Account
{
	/**
	 * Account Identifer
	 * @var Integer
	 */
	protected $identity;

	/**
	 * Context id
	 * @var Integer
	 */
	protected $context;

	/**
	 * Permissions list
	 * @var \Centiq\RBAC\Collections\Permissions
	 */
	protected $permissions;

	/**
	 * Roles list
	 * @var \Centiq\RBAC\Collections\Roles
	 */
	protected $roles;

	/**
	 * Constructor
	 * @param RBAC    $rbac   Core RBAC Instnace
	 * @param Integer $id User edentity
	 */
	public function __construct(\Centiq\RBAC\Manager $manager, $id,  $context = null)
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

		/**
		 * Set hte context
		 * @var Integer
		 */
		$this->context = $context;

		/**
		 * Fetch the permissions
		 */
		$this->permissions = new \Centiq\RBAC\Collections\Permissions(
			$this->getManager(),
			$this->getManager()->getStore()->getAccountPermissions($this->id(), $this->context())
		);

		/**
		 * Fetch the roles
		 */
		$this->roles = new \Centiq\RBAC\Collections\Roles(
			$this->getManager(),
			$this->getManager()->getStore()->getAccountRoles($this->id(), $this->context())
		);
	}

	/**
	 * Fetch the account identifer
	 * @return Integer
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * Fetch the context
	 * @return Integer
	 */
	public function context()
	{
		return $this->context;
	}

	/**
	 * Return the manager object
	 * @return \Centiq\RBAC\Manager
	 */
	public function getManager()
	{
		return $this->manager;
	}

	/**
	 * Check to see if this account has a role
	 * @param  Role    $role Role Object
	 * @return boolean
	 */
	public function hasRole(Node $role)
	{
		return $this->getManager()->getStore()->accountInRole($this->id(), $role->id(), $this->context());
	}

	public function assignRole(Role $role)
	{
		return $this->getManager()->getStore()->connectAccountToRole($this->id(), $role->id());
	}

	public function getPermissions()
	{
		return $this->permissions;
	}

	public function getRoles()
	{
		return $this->roles;
	}

	public function getPermission($identity)
	{
		return $this->getPermissions()->get($identity);
	}

	public function hasPermission($identity)
	{
		return $this->getPermissions()->has($identity);
	}
}