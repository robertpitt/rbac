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
class Node
{
	/**
	 * Manager Instance
	 * @var \Centiq\RBAC\Manager
	 */
	protected $manager;

	/**
	 * Node Type
	 * @var String
	 */
	protected $type;

	/**
	 * Entity ID
	 * @var Integer
	 */
	protected $id;

	/**
	 * Left position
	 */
	protected $left;

	/**
	 * Right position
	 */
	protected $right;

	/**
	 * Name
	 */
	protected $name;

	/**
	 * Description
	 */
	protected $description;

	/**
	 * Constructor
	 * @param CentiqRBACManager $manager 	[description]
	 * @param [type]            $type		[description]
	 * @param [type]            $identifer 	[description]
	 */
	public function __construct(\Centiq\RBAC\Manager $manager, $type, $identifer)
	{
		/**
		 * Set the Manager Object
		 */
		$this->manager = $manager;

		/**
		 * Set the type
		 */
		$this->type = $type;

		/**
		 * If the identifer is not a numeric identifer, assume it's a name value
		 */
		if(is_numeric($identifer) === false)
		{
			$identifer = $this->manager->getStore()->resolve($type, $identifer);
		}

		/**
		 * Set the identifer
		 */
		$this->id = (int)$identifer;

		/**
		 * Fetch the node's information
		 */
		$node = $this->manager->getStore()->getRow($type, "id", $identifer);

		/**
		 * Validate that we was able to retrive a node.
		 */
		if(!$node)
		{
			throw new \Exception("Node does not exists");
		}

		/**
		 * Set the values
		 */
		$this->left 		= $node->left;
		$this->right 		= $node->right;
		$this->name 		= $node->name;
		$this->description 	= $node->description;
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
	public function name()
	{
		return $this->name;
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
	 * Return the node type
	 * @return String
	 */
	public function type()
	{
		return $this->type;
	}

	/**
	 * Return the Manager Object
	 * @return \Centiq\RBAC\Manager
	 */
	public function getManager()
	{
		return $this->manager;
	}

	/**
	 * Returnt the amount of children
	 * @return Integer
	 */
	public function getChildCount()
	{
		return (($this->right() - $this->left()) - 1) / 2;
	}

	/**
	 * Check to see if the permission has children
	 * @return boolean
	 */
	public function isLeaf()
	{
		return $this->getChildCount() === 0;
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

	/**
	 * Check to see if hte current node is a root node
	 * @return boolean
	 */
	public function isRoot()
	{
		return $this->left() == 0;
	}

	/**
	 * Simple node validation, is right bigger than left.
	 * @return boolean
	 */
	public function isValid()
	{
		return $this->right() > $this->left();
	}

	/**
	 * Check to see if the node has children
	 * @return boolean
	 */
	public function hasChildren()
	{
		return $this->getChildCount() > 0;
	}

	/**
	 * Check to see if a node is a root node
	 * @return boolean
	 */
	public function hasParant()
	{
		return !$this->isRoot();
	}

	/**
	 */
	public function getFirstChild()
	{
		if($this->isLeaf())
		{
			return null;
		}

		/**
		 * Fetch the node
		 */
		$node = $this->getManager()->getStore()->getFirstChildId($this->type(), $this->id());

		/**
		 * If we have a negative response, expect that the tree has changed
		 */
		if($node)
		{
			return new self($this->getManager(), $this->type(), $node);
		}
	}

	public function getLastChild()
	{
		if($this->isLeaf())
		{
			return null;
		}

		/**
		 * Fetch the node
		 */
		$node = $this->getManager()->getStore()->getLastChildId($this->type(), $this->id());

		/**
		 * If we have a negative response, expect that the tree has changed
		 */
		if($node)
		{
			return new self($this->getManager(), $this->type(), $node);
		}
	}

	public function getChildren()
	{
		/**
		 * @todo splice the return results from the descendants
		 */
		return $this->getDescendants(1);
	}

	/**
	 * Return the descendants of this node
	 * @param  Integer $depth number of descendants, null for unlimited
	 * @todo Implement depth slicing
	 * @todo cache the results in runtime
	 */
	public function getDescendants($depth = null)
	{
		/**
		 * Map the list of identifers into new objects
		 */
		return array_map(function($node){

			return new self($this->getManager(), $this->type(), $node);

		}, $this->getManager()->getStore()->getChildNodes($this->type(), $this->id()));
	}

	public function getAncestors()
	{
		/**
		 * Map the list of identifers into new objects
		 */
		return array_map(function($node){

			return new self($this->getManager(), $this->type(), $node);

		}, $this->getManager()->getStore()->getAncestorNodes($this->type(), $this->id()));
	}

	public function getParent()
	{
		return end($this->getAncestors());
	}

	/**
	 * Fetch the path of this node
	 * @param  string  $separator    [description]
	 * @param  boolean $include_self [description]
	 * @return [type]                [description]
	 */
	public function getPath($include_self = false, $separator = '.')
	{
		/**
		 * Create a new path container
		 */
		$path = [];

		$ancestors = $this->getAncestors();
		if($ancestors)
		{
			foreach($ancestors as $ancestor)
			{
				$path[] = $ancestor->name();
			}
		}

		if($include_self)
		{
			$path[] = $this->name();
		}

		return implode($separator, $path);
	}

	public function createChild($name, $description)
	{
		/**
		 * Create a new child node
		 */
		$node = $this->getManager()->getStore()->createNode($this->type(), $name, $description, $this->id());

		/**
		 * Return a wrapper class for the new node
		 */
		return new self($this->getManager(), $this->type(), $node);
	}

	/**
	 * Delete this node from its tree
	 * @return boolean
	 */
	public function delete($preserve_children = true)
	{
		/**
		 * Validate that the node is not a root node
		 */
		if($this->isRoot() === true)
		{
			throw new \Exception("Cannot remove root node.");
		}

		return $this->getManager()->getStore()->deleteNode($this->type(), $this->id(), true);
	}
}