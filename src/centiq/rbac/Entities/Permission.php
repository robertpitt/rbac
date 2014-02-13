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

class Permission extends Node
{
	/**
	 * Create a new permission entity
	 * @param  Centiq\RBAC\Manager $manager     RBAC Manager
	 * @param  String              $name        Name of the leaf
	 * @param  String              $description Description
	 * @param  Integer             $parent      Parent permission
	 * @return Centiq\RBAC\Entities\Permission  Permission class
	 */
	public static function create(
		\Centiq\RBAC\Manager $manager, $name, $description, Permission $parent)
	{
		/**
		 * Add the Permission to the database
		 */
		$permission_id = $manager->getStore()->createPermission($name, $description, $parent->id());

		/**
		 * return a new instance of the Permission class
		 */
		return new self($manager, $permission_id);
	}

	/**
	 * Try and resolve a Permission given a name
	 */
	public static function resolve($name)
	{
		/**
		 * Respolve a Permission name into a Permission id
		 * @var Integer
		 */
		$permission_id = $manager->getStore()->resolvePermission($name);

		/**
		 * return a new instance of the Permission class
		 */
		return new self($manager, $permission_id);
	}

	public function __construct(\Centiq\RBAC\Manager $manager, $id, $data = null)
	{
		/**
		 * Set the initial manager and identifer
		 */
		parent::__construct($manager, $id);

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
			$data = $this->manager->getStore()->getPermission($this->id());
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

	public function createChild($title, $description)
	{
		/**
		 * Insert the entity into the tree
		 */
		$role = $this->manager->getStore()->createPermission($title, $description, $this->id());

		/**
		 * Return a new instance of the Role object
		 */
		return new self($this->manager, $role);
	}

	public function getChildren()
	{
		/**
		 * Fetch the permissions
		 */
		$permissions = $this->manager->getStore()->getChildPermissions($this->id(), true);

		/**
		 * Map the roles into new instances
		 */
		return array_map(function($r){
			return new Permission($this->manager, $r['id'], $r);
		}, $permissions);
	}
}