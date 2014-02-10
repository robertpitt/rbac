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

class Permission
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

	/**
	 * Manager Object
	 * @var \Centiq\RBAC\Manager
	 */
	protected $mananger;

	/**
	 * Permission ID
	 * @var Integer
	 */
	protected $id;

	/**
	 * Left position of Permission
	 * @var Integer
	 */
	protected $left;

	/**
	 * Right position of Permission
	 * @var Integer
	 */
	protected $right;

	/**
	 * Permission title
	 * @var String
	 */
	protected $title;

	/**
	 * Permission Descriotion
	 * @var String
	 */
	protected $description;

	/**
	 * Permission constructor
	 */
	public function __construct(\Centiq\RBAC\Manager $manager, $permission_id)
	{
		/**
		 * Set the ID
		 */
		$this->id = $permission_id;

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
		 * Fetch the permission from the storage
		 */
		$permission = $this->manager->getStore()->getPermission($this->id());

		/**
		 * Set the parameters
		 */
		$this->id 			= (int)$permission['id'];
		$this->left 		= (int)$permission['left'];
		$this->right 		= (int)$permission['right'];
		$this->title 		= $permission['title'];
		$this->description 	= $permission['description'];
	}

	/**
	 * Return the permission identification
	 * @return Integer
	 */
	public function id()
	{
		return $this->id;
	}

	/**
	 * Return the left position for the permission tree
	 * @return Integer
	 */
	public function left()
	{
		return $this->left;
	}

	/**
	 * Return the right position for the permission tree
	 * @return Integer
	 */
	public function right()
	{
		return $this->right;
	}

	/**
	 * Permission Title
	 * @return String
	 */
	public function title()
	{
		return $this->title;
	}

	/**
	 * Permission Description
	 * @return String
	 */
	public function description()
	{
		return $this->description;
	}

	/**
	 * Check to see if the permission has children
	 * @return boolean
	 */
	public function isLeaf()
	{
		return ($this->left() - $this->right()) === 1;
	}
}