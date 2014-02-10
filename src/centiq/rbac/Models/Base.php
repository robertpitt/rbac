<?php
/**
 * RBAC (NIST-L2) implementation
 * @copyright   Copyright (C) 2013 Centiq Limited, +44 (0)115 951 9666
 * @license     All rights reserved
 * @author      Robert Pitt <rpitt@centiq.co.uk>
 * @version     0.0.1
 * @package     Centiq\RBAC
 */
namespace Centiq\RBAC\Models;

/**
 * Base Model
 */
class Base
{
	/**
	 * Database connection handle
	 * @var \PDO
	 */
	protected $store;

	/**
	 * Prefix String
	 * @var string
	 */
	protected $prefix = 'rbac_';

	/**
	 * Left Field identifier
	 * @var string
	 */
	protected $left_field = 'left';

	/**
	 * Right Field identifier
	 * @var string
	 */
	protected $right_field = 'right';

	/**
	 * Model Constructor
	 */
	public function __construct(\Centiq\RBAC\Manager $manager)
	{
		/**
		 * Set the connection
		 * @var \PDO
		 */
		$this->store = $manager->getStore();

		/**
		 * Assure that the store throws exceptions
		 */
		$this->store->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		/**
		 * Only return objects for fetches.
		 */
		$this->store->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
	}

	/**
	 * Get a lsit of nodes given the type of tree, such as roles or permissions
	 * @param  String $type Table suffix, such as permissions or roles.
	 * @return Array        Returns an array of object, each object being a node.
	 */
	protected function getAllNodes($type)
	{
		/**
		 * Create a new select statement
		 * @var \PDOStatement
		 */
		$nodes = $this->store->prepare(
			"SELECT * FROM {$this->prefix}{$type} ORDER BY {$this->left_field} ASC"
		);

		/**
		 * Execute the statement
		 */
		$nodes->execute();

		/**
		 * Return teh nodes
		 */
		return $nodes->fetchAll();
	}

	/**
	 * Get an individual node from a tree
	 * @param  String $type Table suffix, such as permissions or roles.
	 * @param  [type] $node_id [description]
	 * @return [type]          [description]
	 */
	protected function getNode($type, $node_id)
	{
		/**
		 * Create a new select statement
		 * @var \PDOStatement
		 */
		$node = $this->store->prepare(
			"SELECT * FROM {$this->prefix}{$type} WHERE `{$this->left_field}` = :node_id"
		);

		/**
		 * Bind the node identifer
		 */
		$node->bindParam($node_id);

		/**
		 * Fetch the object
		 */
		return $node->fetch();
	}

	/**
	 * Add a node
	 */
	protected function addNode($type, $name, $description, $parent_id)
	{
		/**
		 * Fetch hte parent node
		 */
		$parent_node = $this->getNode($type, $parent_id);

		/**
		 * Extract the left and right widths
		 */
		$parent_left  = $parent_node[$this->left_field];
		$parent_right = $parent_node[$this->right_field];

		/**
		 * Shift the nodes.
		 */
		$this->_shiftNode($type, $this->left_field,  2, ">",  $parent_left);
		$this->_shiftNode($type, $this->right_field, 2, ">=", $parent_right);

		/**
		 * Insert the new node
		 */
		$node = $this->prepare(
			"INSERT INTO `{$this->prefix}{$type}` " .
			"(" . implode(",", array("name", "description", $this->left_field, $this->right_field)) . ") " .
			"VALUES (:name, :desc, :left, :right)"
		);

		$node->bindParam(":name",  $name);
		$node->bindParam(":desc",  $description);
		$node->bindParam(":left",  $parent_right);
		$node->bindParam(":right", $parent_right + 1);

		/**
		 * Return the execute status
		 */
		return $node->execute();
	}

	/**
	 * Shift a node's left or right position
	 */
	protected function _shiftNode($type, $field, $inc, $clause_operator, $clause_value)
	{
		/**
		 * SQL Structure
		 * @var string
		 */
		$sql = "UPDATE `{$this->prefix}{$type}` SET `{field}` = `{field}` + `$inc` WHERE `{$field}` $clause_operator :value";

		/**
		 * Prepare the statement
		 */
		$statement = $this->prepare($sql);

		/**
		 * Bind the value
		 */
		$statement->bindParam(":value", $clause_value);

		/**
		 * Return the exeucte status
		 */
		return $statement->execute();
	}
}