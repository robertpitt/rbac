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
 * 
 */
abstract class Node
{
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
	 * Node constructor
	 * @param \Centiq\RBAC\Manager $manager       [description]
	 * @param [type]            $permission_id [description]
	 */
	public function __construct(\Centiq\RBAC\Manager $manager, $node_id)
	{
		/**
		 * Set the manager object
		 */
		$this->manager = $manager;

		/**
		 * Set the ID
		 */
		$this->id = $node_id;
	}

	/**
	 * Abstract method for updating the node, higher level specific
	 */
	abstract public function update();
	abstract public function createChild($title, $description);
	abstract public function getChildren();

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
	 * Returnt the amount of children
	 * @return Integer
	 */
	public function childrenLength()
	{
		return (($this->right() - $this->left()) - 1) / 2;
	}

	/**
	 * Check to see if the permission has children
	 * @return boolean
	 */
	public function isLeaf()
	{
		return $this->childrenLength() === 0;
	}

	/**
	 * Check to see if this node is a descendant of another node
	 * @param  Node    $node Other Node
	 * @return boolean
	 */
	public function isDescendantOf(Node $node)
	{
		return $node->left() < $this->left() && $this->left() < $node->right();
	}

	/**
	 * Check to see if this node is a ancestor of another node
	 * @param  Node    $node Other Node
	 * @return boolean
	 */
	public function isAncestorOf(Node $node)
	{
		return $this->left() < $node->left() && $node->left() < $this->right();
	}
}