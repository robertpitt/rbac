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

class Role
{
	/**
	 * Create a new role entity
	 * @param  Centiq\RBAC\Manager $manager   RBAC Manager
	 * @param  String              $name        Name of the leaf
	 * @param  String              $description Description
	 * @param  Integer             $parent      Parent role
	 * @return Centiq\RBAC\Entities\Role         Role class
	 */
	public static function create(
		\Centiq\RBAC\Manager $manager, $name, $description, Role $parent)
	{
		/**
		 * Add the role to the database
		 */
		$role_id = $manager->getStore()->createRole($name, $description, $parent->id());

		/**
		 * return a new instance of the role class
		 */
		return new self($manager, $role_id);
	}

	/**
	 * Try and resolve a Role given a name
	 */
	public static function resolve($name)
	{
		/**
		 * Respolve a role name into a role id
		 * @var Integer
		 */
		$role_id = $manager->getStore()->resolveRole($name);

		/**
		 * return a new instance of the role class
		 */
		return new self($manager, $role_id);
	}

	/**
	 * Manager Object
	 * @var \Centiq\RBAC\Manager
	 */
	protected $mananger;

	/**
	 * Role ID
	 * @var Integer
	 */
	protected $id;

	/**
	 * Left position of role
	 * @var Integer
	 */
	protected $left;

	/**
	 * Right position of role
	 * @var Integer
	 */
	protected $right;

	/**
	 * Role title
	 * @var String
	 */
	protected $title;

	/**
	 * Role Descriotion
	 * @var String
	 */
	protected $description;

	/**
	 * Role constructor
	 */
	public function __construct(\Centiq\RBAC\Manager $manager, $role_id)
	{
		/**
		 * Set the ID
		 */
		$this->id = $role_id;

		/**
		 * Set the manager object
		 */
		$this->manager = $manager;

		/**
		 * Populate
		 */
		$this->update();
	}

	public function update()
	{
		/**
		 * Fetch the role from the storage
		 */
		$role = $this->manager->getStore()->getRole($this->id());

		/**
		 * Set the parameters
		 */
		$this->id 			= (int)$role['id'];
		$this->left 		= (int)$role['left'];
		$this->right 		= (int)$role['right'];
		$this->title 		= $role['title'];
		$this->description 	= $role['description'];
	}

	/**
	 * Return the role identification
	 * @return Integer
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * Return the left position for the role tree
	 * @return Integer
	 */
	public function left()
	{
		return $this->left;
	}

	/**
	 * Return the right position for the role tree
	 * @return Integer
	 */
	public function right()
	{
		return $this->right;
	}

	/**
	 * Role Title
	 * @return String
	 */
	public function title()
	{
		return $this->title;
	}

	/**
	 * Role Description
	 * @return String
	 */
	public function description()
	{
		return $this->description;
	}

	/**
	 * Check to see if the role has children
	 * @return boolean
	 */
	public function isLeaf()
	{
		return ($this->left() - $this->right()) === 1;
	}

	public function getDecendents()
	{
		return $this->manager->getStore()->getRoleChildren($this->id());
	}

	/**
	 * Link an account to this Role
	 */
	public function addAccount(Account $account)
	{
		return $this->manager->getStorage()->assignRole($account->id(), $this->id());
	}

	/**
	 * Link an account to this Role
	 */
	public function addPermission(Permission $permission)
	{
		return $this->manager->getStorage()->assignPermission($permission->id(), $this->id());
	}
}