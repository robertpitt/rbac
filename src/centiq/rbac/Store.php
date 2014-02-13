<?php
/**
 * RBAC (NIST-L2) implementation
 * @copyright   Copyright (C) 2013 Centiq Limited, +44 (0)115 951 9666
 * @license     All rights reserved
 * @author      Robert Pitt <rpitt@centiq.co.uk>
 * @version     0.0.1
 * @package     Centiq\RBAC
 */
namespace Centiq\RBAC;

/**
 * RBAC Manager Class
 * @since 0.0.1
 */
class Store implements Interfaces\Store
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
	 * @param PDO $database
	 */
	public function __construct(\PDO $database)
	{
		/**
		 * Set the database
		 * @var \PDO
		 */
		$this->database = $database;
	}

	/**
	 * Get the connection
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


		return is_numeric($identifier) ? (int)$identifier : $this->getEntity($table, $identifier, "title", "id")['id'];
	}

	/**
	 * Resolve a role name to a role id
	 * @param  String $name Role Name
	 * @return Integer      Role ID
	 */
	public function resolveRole($identifier)
	{
		return $this->resolve("roles", $identifier);
	}

	public function resolvePermission($identifier)
	{
		return $this->resolve("permissions", $identifier);
	}

	/**
	 * [createRole description]
	 * @param  [type] $title       [description]
	 * @param  [type] $description [description]
	 * @param  [type] $parent      [description]
	 * @return [type]              [description]
	 */
	public function createRole($title, $description, $parent)
	{
		return $this->nestedCreate("roles", $parent, array(
			"description" 	=> $description,
			"title" 		=> $title
		));
	}

	/**
	 * Return a new role
	 * @param  Integer $role Role id or role name.
	 * @return Array
	 */
	public function getRole($id)
	{
		return $this->getEntity("roles", $id);
	}

	public function getChildRoles($id, $inc_self = false)
	{
		$statement = $this->database->prepare("
			SELECT c.*
			FROM {$this->prefix}roles as p
			JOIN {$this->prefix}roles as c on (c.`left` " . ($inc_self ? ">=" : ">") . " p.`left` and c.`right` ". ($inc_self ? "<=" : "<") ." p.`right`)
			WHERE p.id = :id
    	");

    	/**
    	 * Bind the parent node identity
    	 */
    	$statement->bindParam(":id", $id);

    	/**
    	 * Execute
    	 */
    	$statement->execute();

    	/**
    	 * Return the list of arrays
    	 */
    	return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	public function getChildPermissions($id, $inc_self = false)
	{
		$statement = $this->database->prepare("
			SELECT c.*
			FROM {$this->prefix}permissions as p
			JOIN {$this->prefix}permissions as c on (c.`left` " . ($inc_self ? ">=" : ">") . " p.`left` and c.`right` ". ($inc_self ? "<=" : "<") ." p.`right`)
			WHERE p.id = :id
    	");

    	/**
    	 * Bind the parent node identity
    	 */
    	$statement->bindParam(":id", $id);

    	/**
    	 * Execute
    	 */
    	$statement->execute();

    	/**
    	 * Return the list of arrays
    	 */
    	return $statement->fetchAll(\PDO::FETCH_ASSOC);
	}

	/**
	 * Delete a role
	 * @param  Intiger|String $role Role id or role name
	 * @return Boolean
	 */
	public function deleteRole($role){}

	/**
	 * Update a role
	 * @param  Integer|String $role 
	 */
	public function updateRole($role, $name, $description){}

	/**
	 * Update a role
	 */
	public function createPermission($title, $description, $parent)
	{
		return $this->nestedCreate("permissions", $parent, array(
			"description" 	=> $description,
			"title" 		=> $title
		));
	}

	/**
	 * Fetch a permission
	 */
	public function getPermission($id)
	{
		return $this->getEntity("permissions", $id);
	}

	/**
	 * Delete a permission
	 * @return [type] [description]
	 */
	public function deletePermission($id){}
	public function updatePermission($id, $title, $description){}

	/**
	 * Return an entity, such as permission or role
	 * @param  Ineger $role_id Role Identifier
	 * @return Array          Role data
	 */
	protected function getEntity($table, $entity_id, $pk = 'id', $columns = '*')
	{
		/**
		 * Prepare the statement
		 */
		$statement = $this->database->prepare("SELECT {$columns} FROM {$this->prefix}{$table} WHERE {$pk} = :id");

		/**
		 * Bind the role id to the query
		 */
		$statement->bindParam(":id", $entity_id);

		/**
		 * Execute
		 */
		try
		{
			$statement->execute();
		}
		catch(\PDOException $e)
		{
			throw new Exceptions\Store("Unable to locate entity {$table}({$entity_id})", 0, $e);
		}

		/**
		 * Return teh object
		 */
		return $statement->fetch(\PDO::FETCH_ASSOC);
	}

	/**
	 * Lock a table
	 * @return Boolean
	 */
	protected function lock($table, $mode = "WRITE")
	{
		try
		{
			if($this->database->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'sqlite')
			{
				return true;
			}

			return $this->database->query("LOCK TABLE {$this->prefix}{$table} {$mode}")->execute();
		}
		catch(\PDOException $e)
		{
			throw new Exceptions\Store("Unable to LOCK table ({$table}) in ({$mode}) mode", 0, $e);
		}
	}

	/**
	 * Unlock the tables
	 * @return Boolean
	 */
	protected function unlock()
	{
		try
		{
			if($this->database->getAttribute(\PDO::ATTR_DRIVER_NAME) == 'sqlite')
			{
				return true;
			}
			
			return $this->database->query("UNLOCK TABLES")->execute();
		}
		catch(\PDOException $e)
		{
			throw new Exceptions\Store("Unable to UNLOCK tables.", 0, $e);
		}
	}

	/**
	 * Create a nested element within a nested set table
	 * @param  String  $table   Table identifer
	 * @param  Integer $parent  Parent identifer
	 * @param  array   $params  Parameters such as description
	 */
	protected function nestedCreate($table, $parent, array $params = array())
	{
		try
		{
			/**
			 * Remove left / right values if existing
			 */
			unset($params['left'], $params['right']);

			/**
			 * Get the keys
			 */
			$keys 	= array_keys($params);
			$values = array_values($params);

			/**
			 * Validate we have keys
			 */
			if(count($keys) === 0) throw new Exception("Invalid parameters passed");

			/**
			 * Fetch the parent role
			 */
			$parent = $this->getEntity($table, $parent);

			/**
			 * Start a new transaction for this.
			 */
			$this->database->beginTransaction();

			/**
			 * Lock the tables
			 */
			$this->lock($table);

			/**
			 * Repositon the l/r values
			 */
			$sql = "UPDATE {$this->prefix}{$table} SET `left` = `left` + 2 WHERE `left` >= ?";
			$b = $this->database->prepare($sql)->execute(array($parent['right']));

			$sql = "UPDATE {$this->prefix}{$table} SET `right` = `right` + 2 WHERE `right` >= ?";
			$a = $this->database->prepare($sql)->execute(array($parent['right']));

			/**
			 * Implode the keys
			 */
			$fields = implode(", ", $keys);
			$placeholders = implode(", ", array_fill(0, count($keys), "?"));

			/**
			 * Create the update statement
			 * @var PDOStatement
			 */
			$statement = $this->database->prepare("INSERT INTO {$this->prefix}{$table} ({$fields}, `left`, `right`) VALUES ({$placeholders}, ?, ?)");

			/**
			 * Push the positionals into the array
			 */
			$values[] = ((int)$parent['right']); // left
			$values[] = ((int)$parent['right'] + 1); // right

			/**
			 * Execute
			 */
			$created = $statement->execute($values);

			/**
			 * Fetch the insert ID
			 * @var Ineger
			 */
			$id = $this->database->lastInsertId();

			/**
			 * Valiadte the row was created
			 */
			if(!$created || !$id)
			{
				throw new Exception("Unable to created entity of type ({$table})");
				
			}

			/**
			 * Finally commit
			 */
			$b = $this->database->commit();

			/**
			 * Unlock the tables
			 */
			$this->unlock();

			/**
			 * Return the insert id
			 */
			return (int)$id;
		}
		catch(\Exception $e)
		{
			$this->database->rollBack();

			/**
			 * Unlock the table
			 */
			$this->unlock();

			/**
			 * Pass the exception up to the handler
			 */
			throw new Exceptions\Store($e->getMessage(), $e->getCode(), $e);
		}
	}
}