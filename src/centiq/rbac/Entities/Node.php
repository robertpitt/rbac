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
 * Base node class, used primaraly for roles and permissions
 */
class Node implements \JsonSerializable
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
	 * @var Integer
	 */
	protected $left;

	/**
	 * Right position
	 * @var Integer
	 */
	protected $right;

	/**
	 * Name of this node
	 * @var String
	 */
	protected $name;

	/**
	 * Description of this node
	 * @var String
	 */
	protected $description;

	/**
	 * Constructor
	 * @param CentiqRBACManager  $manager 	Manager Object
	 * @param String             $type		Node Type / Table Name
	 * @param Integer            $identifer Node Identifer
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
	 * Return the first child of this node
	 * @return Node Returns a new node object
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

	/**
	 * Return the last child (right-most) of this node.
	 * @return Node
	 */
	public function getLastChild()
	{
		if($this->isLeaf())
		{
			return null;
		}

		/**
		 * Fetch the node
		 * @var Integer|Null
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

	/**
	 * Return the direct children of this node
	 * @todo currently returns full tree, splice top level
	 * @return Array
	 */
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
		$results = array_map(function($node){

			return new self($this->getManager(), $this->type(), $node);

		}, $this->getManager()->getStore()->getChildNodes($this->type(), $this->id()));

		/**
		 * If we have depth then return the depth
		 */
		if(!is_numeric($depth))
		{
			return $results;
		}
		

		return $this->filterNodeDepth($results, $depth);
	}

	public function filterNodeDepth($nodes, $depth)
	{
	    if(empty($nodes) || $depth === 0)
	    {
	        return array();
	    }

	    $newNodes = array();
	    $stack = array();
	    $level = 0;

	    foreach($nodes as $node)
	    {
	        $parent = end($stack);
	        while($parent && $node->left() > $parent->right())
	        {
	            array_pop($stack);
	            $parent = end($stack);
	            $level--;
	        }

	        if($level < $depth)
	        {
	            $newNodes[] = $node;
	        }

	        if(($node->right() - $node->left()) > 1)
	        {
	            array_push($stack, $node);
	            $level++;
	        }
	    }

	    return $newNodes;
	}

	/**
	 * Return a list of ancestors for this node
	 * @todo implement depth
	 * @return Array<Node>
	 */
	public function getAncestors()
	{
		/**
		 * Map the list of identifers into new objects
		 */
		return array_map(function($node){

			return new self($this->getManager(), $this->type(), $node);

		}, $this->getManager()->getStore()->getAncestorNodes($this->type(), $this->id()));
	}

	/**
	 * Return the parent node
	 * @return Node
	 */
	public function getParent()
	{
		$ancestors = $this->getAncestors();

		return end($ancestors);
	}

	/**
	 * Fetch the path of this node
	 * @param  string  $separator    [description]
	 * @param  boolean $include_self [description]
	 * @return [type]                [description]
	 */
	public function getPath($include_self = true, $separator = '/')
	{
		/**
		 * Create a new path container
		 */
		$path = array();

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

	/**
	 * Create a child node below the current node
	 * @param  String $name        Childs name
	 * @param  String $description Childs description
	 * @return Node                New node
	 */
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
	 * @param boolean $preserve_children This we move th children to the parent
	 *                                   of the deleting node.
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

	public function jsonSerialize()
	{
		return array(
			"id"			=> $this->id(),
			"name" 			=> $this->name(),
			"description"	=> $this->description(),
			"type" 			=> $this->type(),
			"root" 			=> $this->isRoot(),
			"path" 			=> $this->getPath(),
			"parent" 		=> $this->getParent()->id(),
			"children" 		=> $this->getChildCount()
		);
	}
}