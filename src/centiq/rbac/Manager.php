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
	protected $root_role_id = 'root';

	/**
	 * Root Permission identifier
	 */
	protected $root_permission_id = 'root';

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
	 * @param \PDO  $store Storage Object
	 * @param Centiq\RBAC\Interfaces\Cache  $cache Cache Object
	 */
	public function __construct(\PDO $store)
	{
		/**
		 * Set the storage interface
		 */
		$this->store = new Store($store);

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
	 * Get a Role entity
	 * @param  Integer|String $identity Either the ID or the name
	 * @return Entities\Role
	 */
	public function getRole($identity)
	{
		return new Entities\Role($this, $identity);
	}

	/**
	 * Get a Permission entity
	 * @param  Integer|String $identity Either the ID or the name
	 * @return Entities\Role
	 */
	public function getPermission($identity)
	{
		return new Entities\Permission($this, $identity);
	}

	/**
	 * Fetch an account class
	 * @param  Int $identity Account Identification
	 * @return Entities\Account An account object
	 */
	public function getAccount($identity, $context = null)
	{
		return new Entities\Account($this, $identity, $context);
	}
}