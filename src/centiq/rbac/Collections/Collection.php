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
class Collection implements \Iterator, \ArrayAccess, \Countable
{
	/**
	 * Objects
	 */
	protected $__entities__ = array();

	/**
	 * 
	 */
    public function rewind()
    {
        return reset($this->__entities__);
    }

    public function current()
    {
        return current($this->__entities__);
    }

    public function key()
    {
        return key($this->__entities__);
    }

    public function next()
    {
        return next($this->__entities__);
    }

    public function valid()
    {
    	return $this->key() !== null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset))
        {
            $this->__entities__[] = $value;
        }
        else
        {
            $this->__entities__[$offset] = $value;
        }
    }
    public function offsetExists($offset)
    {
        return isset($this->__entities__[$offset]);
    }

    public function offsetUnset($offset)
    {
        unset($this->__entities__[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->__entities__[$offset]) ? $this->__entities__[$offset] : null;
    }

	public function count()
	{
		return count($this->__entities__);
	}
}