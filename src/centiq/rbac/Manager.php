<?php
/**
 * RBAC (NIST-L2) implementation
 * @copyright   Copyright (C) 2013 Centiq Limited, +44 (0)115 951 9666
 * @license     All rights reserved
 * @author      Robert Pitt <rpitt@centiq.co.uk>
 * @version     0.0.1
 * @package     Centiq\RBAC
 */
namespace Centiq\RBAC;

/**
 * RBAC Manager Class
 * @since 0.0.1
 */
class Manager
{
	/**
	 * Storage Handler
	 * @var Centiq\RBAC\Interfaces\Store
	 */
	protected $store;

	/**
	 * Storage Handler
	 * @var Centiq\RBAC\Interfaces\Cache
	 */
	protected $cache;

	/**
	 * Root Role identifier
	 */
	protected $root_role_id = 1;

	/**
	 * Root Permission identifier
	 */
	protected $root_permission_id = 1;

	/**
	 * Root Role
	 * @var Entities\Role
	 */
	protected $root_role;

	/**
	 * Root Permission
	 * @var Entities\Permission
	 */
	protected $root_permission;

	/**
	 * RBAC Manager Constructor
	 * @param Centiq\RBAC\Interfaces\Store  $store Storage Object
	 * @param Centiq\RBAC\Interfaces\Cache  $cache Cache Object
	 */
	public function __construct(Interfaces\Store $store, Interfaces\Cache $cache = null)
	{
		/**
		 * Set the storage interface
		 */
		$this->store = $store;

		/**
		 * Set the cache object if we have one
		 */
		$this->cache = $cache ? $cache : null; //force null for strict comparisons

		/**
		 * Get the root Role object
		 */
		$this->root_role = new Entities\Role($this, $this->root_role_id);

		/**
		 * Get the root Permission object
		 */
		$this->root_permission = new Entities\Permission($this, $this->root_permission_id);
	}

	/**
	 * Return the storage object
	 * @return Centiq\RBAC\Interfaces\Store
	 */
	public function getStore()
	{
		return $this->store;
	}

	/**
	 * Return the cache object
	 * @return Centiq\RBAC\Interfaces\Cache
	 */
	public function getCache()
	{
		return $this->cache;
	}

	/**
	 * Return true if there is a caching object
	 * @return boolean
	 */
	public function hasCache()
	{
		return $this->cache !== null;
	}

	/**
	 * Return the Role object for the Root entity
	 * @return \Centiq\RBAC\Entities\Role
	 */
	public function getRootRole()
	{
		return $this->root_role;
	}

	/**
	 * Return the Permission object for the Root entity
	 * @return \Centiq\RBAC\Entities\Permission
	 */
	public function getRootPermission()
	{
		return $this->root_permission;
	}

	/**
	 * Create a new role
	 * @param  String  $title       			Title of the role
	 * @param  String  $description 			Description of the role
	 * @param  Integer $parent      			Parent role id.
	 * @return Centiq\RBAC\Entities\Role
	 */
	public function createRole($title, $description, Entities\Role $parent = null)
	{
		return Entities\Role::create($this, $title, $description, $parent ? $parent : $this->getRootRole());
	}

	/**
	 * Get a Role entity
	 * @param  Integer|String $id Either the ID or the name
	 * @return Entities\Role
	 */
	public function getRole($id)
	{
		return Entities\Role::resolve($id);
	}

	/**
	 * Create a new Permission
	 * @param  String 				$title 			Title of the permission
	 * @param  String 				$description 	Description of the permission
	 * @param  Entities\Permission 	$parent 		Parent permission id
	 * @return Centiq\RBAC\Entities\Permission
	 */
	public function createPermission($title, $description, Entities\Permission $parent = null)
	{
		return Entities\Permission::create($this, $title, $description, $parent ? $parent : $this->getRootPermission());
	}

	/**
	 * Get a Permission entity
	 * @param  Integer|String $id Either the ID or the name
	 * @return Entities\Role
	 */
	public function getPermission($id)
	{
		return Entities\Permission::resolve($id);
	}

	/**
	 * Fetch an account class
	 * @param  Int $account_id Account Identification
	 * @return Entities\Account An account object
	 */
	public function getAccount($account_id)
	{
		return new Entities\Account($this, $account_id);
	}
}