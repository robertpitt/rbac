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

class Role extends Node
{
	/**
	 * Create a new role entity
	 * @param  Centiq\RBAC\Manager $manager   RBAC Manager
	 * @param  String              $name        Name of the leaf
	 * @param  String              $description Description
	 * @param  Integer             $parent      Parent role
	 * @return Centiq\RBAC\Entities\Role         Role class
	 */
	public static function create(\Centiq\RBAC\Manager $manager, $name, $description, Role $parent)
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

	public function __construct(\Centiq\RBAC\Manager $manager, $id, $data = null)
	{
		/**
		 * Set the initial manager and identifer
		 */
		parent::__construct($manager, $id, "roles");

		/**
		 * Load the data
		 */
		$this->update($data);
	}

	public function update(array $data = null)
	{
		/**
		 * If we don't have any initialization datam fetch it from the id
		 */
		if($data === null)
		{
			$data = $this->manager->getStore()->getRole($this->id());
		}

		/**
		 * Set the parameters
		 */
		$this->id 			= (int)$data['id'];
		$this->left 		= (int)$data['left'];
		$this->right 		= (int)$data['right'];
		$this->title 		= $data['title'];
		$this->description 	= $data['description'];
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
		return $this->manager->getStore()->assignRole($account->id(), $this->id());
	}

	/**
	 * Link an account to this Role
	 */
	public function addPermission(Permission $permission)
	{
		return $this->manager->getStore()->assignPermission($permission->id(), $this->id());
	}

	/**
	 * Create a new new child object
	 */
	public function createChild($title, $description)
	{
		/**
		 * Insert the entity into the tree
		 */
		$role = $this->manager->getStore()->createRole($title, $description, $this->id());

		/**
		 * Return a new instance of the Role object
		 */
		return new self($this->manager, $role);
	}

	public function getChildren()
	{
		/**
		 * Fetch the roles
		 */
		$roles = $this->manager->getStore()->getChildRoles($this->id(), true);

		/**
		 * Map the roles into new instances
		 */
		return array_map(function($r){
			return new Role($this->manager, $r['id'], $r);
		}, $roles);
	}
}