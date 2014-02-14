<?php
/**
 * RBAC (NIST-L2) implementation
 * @copyright   Copyright (C) 2013 Centiq Limited, +44 (0)115 951 9666
 * @license     All rights reserved
 * @author      Robert Pitt <rpitt@centiq.co.uk>
 * @version     0.0.1
 * @package     Centiq\RBAC
 */
namespace Centiq\RBAC\Collections;

/**
 * Base node class, used primaraly for roles and permissions
 */
class Nodes extends Collection
{
	/**
	 * Constructor
	 */
	public function __construct(array $nodes)
	{
		/**
		 * Loop over the nodes
		 */
		foreach ($nodes as $node)
		{
			if(!($node instanceof \Centiq\RBAC\Entities\Node))
			{
				throw new Exception("Node must be an instance of Entities\\Node");
			}

			$this[$node->id()] = $node;
		}
	}

	/**
	 * Fetch a node
	 * @param  String|Integer $identity Index or name key
	 * @return Node|Null
	 */
	public function get($identity)
	{
		if(is_numeric($identity))
		{
			return $this[$id];
		}

		/**
		 * Locate the entity by name
		 */
		foreach ($this as $key => $node) 
		{
			if($node->name() == $identity)
			{
				return $node;
			}
		}
	}

	public function has($identity)
	{
		return $this->get($identity) !== null;
	}
}