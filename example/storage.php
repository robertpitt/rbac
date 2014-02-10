<?php
/**
 * @see http://falsinsoft.blogspot.co.uk/2013/01/tree-in-sql-database-nested-set-model.html
 * @see http://www.artfulsoftware.com/mysqlbook/sampler/mysqled1ch20.html#nested_set_model
 * @see https://github.com/donks122/NestedSet/
 */
class ExampleStore extends PDO implements \Centiq\RBAC\Interfaces\Store
{
	/**
	 * Table Prefix
	 * @var string
	 */
	protected $prefix = "rbac_";

	/**
	 * [__construct description]
	 */
	public function __construct()
	{
		/**
		 * Connect to the database
		 */
		parent::__construct("mysql:dbname=rbac;host=127.0.0.1", "root", "root");
		$this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Reset teh schema back to its default state.
	 */
	public function reset()
	{
		/**
		 * Reset the increment
		 */
		foreach (array("roles", "user_roles", "role_permissions", "permissions") as $key)
		{
			/**
			 * Delete teh roles, this should cascade
			 */
			$this->query("DELETE FROM {$this->prefix}{$key}")->execute();
			$this->query("ALTER TABLE {$this->prefix}{$key} AUTO_INCREMENT=1")->execute();
		}

		/**
		 * Create the initial root entity
		 */
		$statement = $this->prepare("INSERT INTO {$this->prefix}roles (`title`, `description`, `left`, `right`) VALUES (?,?,?,?)");
		$statement->execute(array("root", "Root Entity", 1, 2));

		$statement = $this->prepare("INSERT INTO {$this->prefix}permissions (`title`, `description`, `left`, `right`) VALUES (?,?,?,?)");
		$statement->execute(array("root", "Root Entity", 1, 2));
	}

	/**
	 * Lock a table
	 * @return Boolean
	 */
	public function lock($table, $mode = "WRITE")
	{

		return $this->query("LOCK TABLE {$this->prefix}{$table} {$mode}")->execute();
	}

	/**
	 * Unlock the tables
	 * @return Boolean
	 */
	public function unlock()
	{

		return $this->query("UNLOCK TABLES")->execute();
	}

	public function resolveRole($name)
	{
		return $this->getEntity("role", $name, "name", "id")['id'];
	}

	public function resolvePermission($name)
	{
		return $this->getEntity("permission", $name, "name", "id")['id'];
	}

	/**
	 * Return a role entity
	 * @param  Ineger $role_id Role Identifier
	 * @return Array          Role data
	 */
	public function getEntity($table, $entity_id, $pk = 'id', $columns = '*')
	{
		/**
		 * Prepare the statement
		 */
		$statement = $this->prepare("SELECT {$columns} FROM {$this->prefix}{$table} WHERE {$pk} = :id");

		/**
		 * Bind the role id to the query
		 */
		$statement->bindParam(":id", $entity_id);

		/**
		 * Execute
		 */
		$statement->execute();

		/**
		 * Return teh object
		 */
		return $statement->fetch(PDO::FETCH_ASSOC);
	}

	public function nestedCreate($table, $parent, $params = array())
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
		 * Lock the tables
		 */
		$this->lock($table);

		/**
		 * the new role
		 */
		$sql = "UPDATE {$this->prefix}{$table} SET `right` = `right` + 2 WHERE `right` >= ?";
		$this->prepare($sql)->execute(array($parent['right']));

		$sql = "UPDATE {$this->prefix}{$table} SET `left` = `left` + 2 WHERE `left` > ?";
		$this->prepare($sql)->execute(array($parent['left']));

		/**
		 * Implode the keys
		 */
		$fields = implode(", ", $keys);
		$placeholders = implode(", ", array_fill(0, count($keys), "?"));

		/**
		 * Create the update statement
		 * @var PDOStatement
		 */
		$statement = $this->prepare("INSERT INTO {$this->prefix}{$table} ({$fields}, `right`, `left`) VALUES ({$placeholders}, ?, ?)");

		/**
		 * Push the positionals into the array
		 */
		$values[] = $parent['right'];
		$values[] = $parent['left'] + 1;

		/**
		 * Execute
		 */
		$statement->execute($values);

		/**
		 * Fetch the insert ID
		 * @var Ineger
		 */
		$id = $this->lastInsertId();

		/**
		 * Unlock the tables
		 */
		$this->unlock();

		/**
		 * Return the insert id
		 */
		return $id;
	}

	/**
	 * ---------------------------------------------------------
	 * --------          Interface Inhertance.          --------
	 * ---------------------------------------------------------
	 */
	public function getRole($id)
	{
		return $this->getEntity("roles", $id);
	}

	public function getRoleChildren($role_id)
	{
		$statement = $this->prepare("
			SELECT
				child.id
			FROM
				{$this->prefix}roles AS parent
			JOIN
				{$this->prefix}roles AS child
			ON
				child.`left` BETWEEN parent.`left` AND parent.`right`
			WHERE
				parent.`left`  > (SELECT `left`  FROM {$this->prefix}roles WHERE id = :id)
			AND parent.`right` < (SELECT `right` FROM {$this->prefix}roles WHERE id = :id)
			GROUP BY child.id;
		");

		/**
		 * Bind the parameters
		 */
		$statement->bindParam(":id", $role_id);

		/**
		 * Execute
		 */
		$statement->execute();

		/**
		 * Return an associative array
		 */
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 */
	public function createRole($title, $description, $parent)
	{
		return $this->nestedCreate("roles", $parent, array(
			"description" 	=> $description,
			"title" 		=> $title
		));
	}

	/**
	 */
	public function assignRole($account_id, $role_id)
	{
		/**
		 * Create the statement
		 */
		$statement = $this->prepare("INSERT INTO {$this->prefix}user_roles (account_id, role_id) VALUES(:acc, :role)");;

		/**
		 * Bind parameters
		 */
		$statement->bindParam(":acc", $account_id);
		$statement->bindParam(":role", $role_id);

		/**
		 * Return teh executed status
		 */
		return $statement->execute();
	}

	/**
	 */
	public function hasRole($account_id, $role_id)
	{
		$sql = "
			SELECT COUNT(a.role_id)
			FROM {$this->prefix}user_roles AS a
			JOIN {$this->prefix}roles AS b ON (b.id = a.role_id)
			JOIN {$this->prefix}roles AS c ON (c.left BETWEEN b.left AND b.right)
			WHERE a.account_id = :account_id
			AND c.id = :role_id
		";

		$statement = $this->prepare($sql);

		/**
		 * Bind parameters
		 */
		$statement->bindParam(":account_id", $account_id, PDO::PARAM_INT);
		$statement->bindParam(":role_id", $role_id, PDO::PARAM_INT);

		/**
		 * Execute
		 */
		$statement->execute();

		/**
		 * Validate that we have found the row
		 */
		return (int)$statement->fetchColumn(0) > 0;
	}

	public function getPermission($id)
	{
		return $this->getEntity("permissions", $id);
	}

	public function getPermissionChildren($permission_id)
	{
		$statement = $this->prepare("
			SELECT
				child.id
			FROM
				{$this->prefix}permissions AS parent
			JOIN
				{$this->prefix}permissions AS child
			ON
				child.`left` BETWEEN parent.`left` AND parent.`right`
			WHERE
				parent.`left` > (SELECT `left` FROM {$this->prefix}permissions WHERE id = :id)
			AND parent.`right` < (SELECT `right` FROM {$this->prefix}permissions WHERE id = :id)
			GROUP BY child.id;
		");

		/**
		 * Bind the parameters
		 */
		$statement->bindParam(":id", $permission_id);

		/**
		 * Execute
		 */
		$statement->execute();

		/**
		 * Return an associative array
		 */
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	}

	/**
	 */
	public function createPermission($title, $description, $parent)
	{
		return $this->nestedCreate("permissions", $parent, array(
			"description" 	=> $description,
			"title" 		=> $title
		));
	}

	/**
	 */
	public function assignPermission($role_id, $permission_id)
	{
		/**
		 * Create the statement
		 */
		$statement = $this->prepare("INSERT INTO {$this->prefix}role_permissions (role_id, permission_id) VALUES(:role, :permission)");

		/**
		 * Bind parameters
		 */
		$statement->bindParam(":role", $role_id);
		$statement->bindParam(":permission", $permission_id);

		/**
		 * Return teh executed status
		 */
		return $statement->execute();
	}

	/**
	 */
	public function hasPermission($account_id, $permission_id)
	{
	}
}