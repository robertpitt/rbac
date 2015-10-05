<?php
/**
 * RBAC (NIST-L2) implementation
 * @copyright   Copyright (C) 2013 Centiq Limited, +44 (0)115 951 9666
 * @license     All rights reserved
 * @author      Robert Pitt <rpitt@centiq.co.uk>
 * @version     0.0.1
 * @package     \Centiq\RBAC
 * @link(_blank, http://www.evanpetersen.com/item/nested-sets.html) JUST ANOTHER HIERARCHICAL MODEL
 */
namespace Centiq\RBAC;

/**
 * RBAC Manager Class
 * @since 0.0.1
 */
class Store
{
	/**
	 * Database Instance
	 * @var \PDO
	 */
	protected $database;

	/**
	 * Table prefix
	 * @var string
	 */
	protected $prefix = "rbac_";

	/**
	 * Store constructor
	 * @param \PDO $database
	 */
	public function __construct(\PDO $database)
	{
		/**
		 * Set the database
		 * @var \PDO
		 */
		$this->database = $database;
		$this->database->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Get the raw \PDO conenction
	 * @return \PDO
	 */
	public function getConnection()
	{
		return $this->database;
	}


	/**
	 * Resolve an identifier for an entity to a primary identifer
	 * @param  String           $table      What entity table are we looking in
	 * @param  String|Integer   $identifier Either a name or record number.
	 * @throws Exceptions\Store If          the identifer is invalid.
	 * @return Integer
	 */
	public function resolve($table, $identifier)
	{
		if($identifier === null || !is_numeric($identifier) && !is_string($identifier))
		{
			throw new Exceptions\Store("Entity identifer ({$identifier}) must be a string or number");
		}

		if(is_numeric($identifier))
		{
			return (int)$identifier;
		}

		if(($node = $this->getRow($table, "name", $identifier, "id")))
		{
			return $node->id;
		}
	}

	/**
	 * Retrive a single row from the database, this method is just a utility
	 * @param  String $table   Table we are looking into
	 * @param  String $column  Column that we are using to identify the row
	 * @param  String $value   Value to be compared to the column
	 * @param  String $columns Columns to return, defaults to *
	 * @return stdClass        Returns a stdclass of the request.
	 */
	public function getRow($table, $column, $value, $columns = "*")
	{
		/**
		 * Prepare a statement
		 */
		$statement = $this->database->prepare("SELECT {$columns} FROM {$this->prefix}{$table} WHERE {$column} = :value");

		/**
		 * Bind
		 */
		$statement->bindParam(":value", $value);

		/**
		 * Execute
		 */
		$statement->execute();

		/**
		 * Return the row
		 */
		return $statement->fetch(\PDO::FETCH_OBJ);
	}

	/**
	 * Return the id of the first child (left-most) of a node
	 * @param  String  $table Table we are looking into.
	 * @param  Integer $id    Nodes identifer
	 * @return Integer        Left most sub node's ID
	 */
	public function getFirstChildId($table, $id)
	{
		$statement = $this->database->prepare("
			SELECT c.id
			FROM {$this->prefix}{$table} as p
			JOIN {$this->prefix}{$table} as c on (c.`left` = p.`left` + 1 and c.`right` < p.`right`)
			WHERE p.id = :id
		");

		/**
		 * Bind
		 */
		$statement->bindParam(":id", $id);

		/**
		 * Execute
		 */
		$statement->execute();

		/**
		 * Return the row
		 */
		return $statement->fetchColumn(0);
	}

	/**
	 * Return the id of the last child (right-most) of a node
	 * @param  String  $table Table we are looking into.
	 * @param  Integer $id    Nodes identifer
	 * @return Integer        Left most sub node's ID
	 */
	public function getLastChildId($table, $id)
	{
		$statement = $this->database->prepare("
			SELECT c.id
			FROM {$this->prefix}{$table} as p
			JOIN {$this->prefix}{$table} as c on (c.`left` > p.`left` and c.`right` = p.`right` - 1)
			WHERE p.id = :id
		");

		/**
		 * Bind
		 */
		$statement->bindParam(":id", $id);

		/**
		 * Execute
		 */
		$statement->execute();

		/**
		 * Return the row
		 */
		return $statement->fetchColumn(0);
	}

	/**
	 * Return a list of child identifers for a node
	 * @param  String  $table Table we are looking into.
	 * @param  Integer $id    Nodes identifer
	 * @return Array          Array of identifiers
	 */
	public function getChildNodes($table, $id)
	{
		$statement = $this->database->prepare("
			SELECT c.id
			FROM {$this->prefix}{$table} as p
			JOIN {$this->prefix}{$table} as c on (c.`left` > p.`left` and c.`right` < p.`right`)
			WHERE p.id = :id
			ORDER BY c.`left` ASC
		");

		/**
		 * Bind
		 */
		$statement->bindParam(":id", $id);

		/**
		 * Execute
		 */
		$statement->execute();

		/**
		 * Return the row
		 */
		return $statement->fetchAll(\PDO::FETCH_COLUMN, 0);
	}

	/**
	 * Return a list of parent identifers for a node up the tree to root
	 * @param  String  $table Table we are looking into.
	 * @param  Integer $id    Nodes identifer
	 * @return Array          Array of identifiers
	 */
	public function getAncestorNodes($table, $id)
	{
		$statement = $this->database->prepare("
			SELECT c.id
			FROM {$this->prefix}{$table} as p
			JOIN {$this->prefix}{$table} as c on (c.`left` < p.`left` and c.`right` > p.`right`)
			WHERE p.id = :id
			ORDER BY c.`left` ASC
		");

		/**
		 * Bind
		 */
		$statement->bindParam(":id", $id);

		/**
		 * Execute
		 */
		$statement->execute();

		/**
		 * Return the row
		 */
		return $statement->fetchAll(\PDO::FETCH_COLUMN, 0);
	}

	/**
	 * Create a new node as a child of a node id.
	 * @param  String $table        Table we are looking into.
	 * @param  String $name         Childs name
	 * @param  String $description  Childs Description
	 * @param  Integer $parent_id   Parent identifier
	 * @return Integer 				New nodes identifer
	 */
	public function createNode($table, $name, $description, $parent_id)
	{
		/**
		 * Fetch the parent node
		 */
		$parent = $this->getRow($table, "id", $parent_id);

		/**
		 * Validate that the parent exists
		 */
		if(!$parent)
		{
			throw new Exceptions\Store("Parent node for ({$node}) does not exists");
		}


		/**
		 * Repositon the l/r values
		 */
		$sql = "UPDATE {$this->prefix}{$table} SET `left` = `left` + 2 WHERE `left` >= ?";
		$this->database->prepare($sql)->execute(array($parent->right));

		$sql = "UPDATE {$this->prefix}{$table} SET `right` = `right` + 2 WHERE `right` >= ?";
		$this->database->prepare($sql)->execute(array($parent->right));

		/**
		 * slot hte new node in place
		 * @var PDOStatement
		 */
		$statement = $this->database->prepare("INSERT INTO {$this->prefix}{$table} (name, description, `left`, `right`) VALUES (?, ?, ?, ?)");

		/**
		 * Execute the statement
		 */
		$success = $statement->execute(array(
			$name,
			$description,
			$parent->right,
			$parent->right + 1
		));

		return $this->database->lastInsertId();
	}

	/**
	 * Remove a node from a tree
	 * @param  String  $table             Table we are looking into.
	 * @param  Integer  $id               Node id we want to remove
	 * @param  boolean $preserve_children Child preservation, this sets all children to the parent of
	 *                                    the nodes we are deleting, otherwise we delete the whole subtree
	 * @return boolean
	 */
	public function deleteNode($table, $id, $preserve_children = true)
	{
		/**
		 * Fetch the node
		 */
		$node = $this->getRow($table, "id", $id);

		if($preserve_children)
		{
			//Shift the left positions
			$sql = "UPDATE {$this->prefix}{$table} SET `left` = `left` - 2 WHERE `left` > ?";
			$a = $this->database->prepare($sql)->execute(array($node->left));

			//Shift the right positions
			$sql = "UPDATE {$this->prefix}{$table} SET `right` = `right` - 2 WHERE `right` > ?";
			$b = $this->database->prepare($sql)->execute(array($node->right));

			//Remove the node
			$c = $this->database->prepare("DELETE FROM {$this->prefix}{$table} WHERE id = ?")->execute(array($node->id));
			return $a && $b && $c;
		}
	}

	/**
	 * Link permission identifer to a role identifer
	 * @param  Integer $permission_id 	Permission ID
	 * @param  Integer $role_id    		Role ID
	 * @return boolean             		Insert success
	 */
	public function connectPermissionToRole($permission_id, $role_id)
	{
		/**
		 * Create the statement
		 */
		$statement = $this->database->prepare("INSERT IGNORE INTO {$this->prefix}role_permissions (permission_id, role_id) VALUES (:pid, :rid)");

		/**
		 * Bind parameters
		 */
		$statement->bindParam(":pid", $permission_id);
		$statement->bindParam(":rid", $role_id);

		/**
		 * Execute
		 */
		return $statement->execute();
	}

	/**
	 * Link account identifer to a role identifer
	 * @param  Integer $account_id Account Id
	 * @param  Integer $role_id    Role ID
	 * @return boolean             Insert success
	 */
	public function connectAccountToRole($account_id, $role_id, $context_id = null)
	{
		/**
		 * Create the statement
		 */
		$statement = $this->database->prepare("INSERT IGNORE INTO {$this->prefix}user_roles (account_id, role_id, context_id) VALUES (:aid, :rid, :cid)");

		/**
		 * Bind parameters
		 */
		$statement->bindParam(":aid", $account_id);
		$statement->bindParam(":rid", $role_id);
		$statement->bindParam(":cid", $context_id);

		// die(implode("|", array($account_id, $role_id, $context_id)));

		/**
		 * Execute
		 */
		return $statement->execute();
	}

	/**
	 * Link account identifer to a role identifer
	 * @param  Integer $account_id Account Id
	 * @param  Integer $role_id    Role ID
	 * @return boolean             Insert success
	 */
	public function disconnectAccountToRole($account_id, $role_id, $context_id = null)
	{
		/**
		 * Create the statement
		 */
		$statement = $this->database->prepare("DELETE FROM {$this->prefix}user_roles WHERE account_id = :aid AND role_id = :rid AND context_id = :cid");

		/**
		 * Bind parameters
		 */
		$statement->bindParam(":aid", $account_id);
		$statement->bindParam(":rid", $role_id);
		$statement->bindParam(":cid", $context_id);

		// die(implode("|", array($account_id, $role_id, $context_id)));

		/**
		 * Execute
		 */
		return $statement->execute();
	}

	/**
	 * Check to see if an account is conencted to a role
	 * @param  Integer $account_id Account ID
	 * @param  Integer $role_id    Role ID
	 * @return boolean
	 * @todo We need to search the role tree to see if there is a parent role the account id.
	 */
	public function accountInRole($account_id, $role_id, $context_id = null)
	{
		/**
		 * Create statement
		 */
		$statement = $this->database->prepare("SELECT account_id FROM {$this->prefix}user_roles WHERE account_id = :aid AND role_id = :rid AND context_id = :cid");

		/**
		 * Bind parameters
		 */
		$statement->bindParam(":aid", $account_id);
		$statement->bindParam(":rid", $role_id);
		$statement->bindParam(":cid", $context_id);

		/**
		 * Execute
		 */
		$statement->execute();

		/**
		 * Return we have a match
		 */
		return $statement->fetchColumn() == $account_id;
	}

	/**
	 * Get permissions assigned to an account
	 * @return Array
	 */
	public function getAccountPermissions($account_id, $context_id = null)
	{
		$context = $context_id ? '= :cid' : 'IS NULL';
		$statement = $this->database->prepare(
			"
				SELECT permissions.*
				FROM {$this->prefix}user_roles                AS u_roles
				INNER JOIN {$this->prefix}roles               AS p_roles     ON (p_roles.id = u_roles.role_id)
				INNER JOIN {$this->prefix}roles               AS c_roles     ON (c_roles.`left` >= p_roles.`left` AND c_roles.`right` <= p_roles.`right`)
				INNER JOIN {$this->prefix}role_permissions    AS r_p         ON (r_p.role_id = c_roles.id)
				INNER JOIN {$this->prefix}permissions         AS p_perms     ON (r_p.permission_id = p_perms.id)
				INNER JOIN {$this->prefix}permissions         AS permissions ON (permissions.`left` >= p_perms.`left` AND permissions.`right` <= p_perms.`right`)
				WHERE u_roles.account_id = :aid AND u_roles.context_id $context
			"
		);

		/**
		 * Bind account id
		 */
		$statement->bindParam(":aid", $account_id);
		$context_id == null ?: $statement->bindParam(":cid", $context_id);

		/**
		 * Execute the statement
		 */
		$statement->execute();

		/**
		 * Return teh list
		 */
		return $statement->fetchAll(\PDO::FETCH_OBJ);
	}

	/**
	 * Get permissions assigned to an account
	 * @return Array
	 */
	public function getAccountRoles($account_id, $context_id = null)
	{
		$context = $context_id ? '= :cid' : 'IS NULL';
		$statement = $this->database->prepare(
			"
				SELECT rbac_roles.*
				FROM rbac_roles
				LEFT JOIN rbac_user_roles
				ON rbac_user_roles.role_id = rbac_roles.id
				WHERE account_id = :aid AND rbac_user_roles.context_id {$context};
			"
		);

		/**
		 * Bind account id
		 */
		$statement->bindParam(":aid", $account_id);
		$context_id == null ?: $statement->bindParam(":cid", $context_id);

		/**
		 * Execute the statement
		 */
		$statement->execute();

		/**
		 * Return teh list
		 */
		return $statement->fetchAll(\PDO::FETCH_OBJ);
	}

	/**
	 * Lock the table
	 * @param  String $table Table we are locking
	 * @param  string $mode  LOCK Mode
	 * @return void
	 */
	protected function lock($table, $mode = "WRITE")
	{
		try
		{
			return $this->database->query("LOCK TABLE {$this->prefix}{$table} {$mode}")->execute();
		}
		catch(\PDOException $e)
		{
			throw new Exceptions\Store("Unable to LOCK table ({$table}) in ({$mode}) mode", 0, $e);
		}
	}

	/**
	 * Unlock all tables
	 * @return void
	 */
	protected function unlock()
	{
		try
		{
			return $this->database->query("UNLOCK TABLES")->execute();
		}
		catch(\PDOException $e)
		{
			throw new Exceptions\Store("Unable to UNLOCK tables.", 0, $e);
		}
	}
}