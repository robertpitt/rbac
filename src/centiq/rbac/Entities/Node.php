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
 * Base node class
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
	 * Update the class with data from the arguments
	 * @param  Array $node_data Node Data
	 * @todo Throw exception if data is currupt.
	 */
	public function update($node_data)
	{
		/**
		 * Set the parameters
		 */
		$this->id 			= (int)$node_data['id'];
		$this->left 		= (int)$node_data['left'];
		$this->right 		= (int)$node_data['right'];
		$this->title 		= $node_data['title'];
		$this->description 	= $node_data['description'];
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