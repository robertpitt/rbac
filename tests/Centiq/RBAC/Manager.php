<?php

class ManagerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        /**
         * Create a PDO Object
         */
        $this->pdo = new PDO('mysql:host=localhost;dbname=rbac_main', "root", "root");

        /**
         * Set exceptions on
         */
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        /**
         * Install tables
         */
        // if(!$this->pdo->exec(file_get_contents(__DIR__ . "/../../../sql/mysql.sql")))
        // {
        //     throw new Exception("Unable to import sqlite DB");
        // }

        /**
         * Reset the increment
         */
        foreach (array("roles", "user_roles", "role_permissions", "permissions") as $key)
        {
            /**
             * Delete teh roles, this should cascade
             */
            $this->pdo->query("DELETE FROM rbac_{$key}")->execute();
            $this->pdo->query("ALTER TABLE rbac_{$key} AUTO_INCREMENT=1")->execute();
        }

        /**
         * Create the initial root entity
         */
        $statement = $this->pdo->prepare("INSERT INTO rbac_roles (`name`, `description`, `left`, `right`) VALUES (?,?,?,?)");
        $statement->execute(array("root", "Root Entity", 0, 1));

        $statement = $this->pdo->prepare("INSERT INTO rbac_permissions (`name`, `description`, `left`, `right`) VALUES (?,?,?,?)");
        $statement->execute(array("root", "Root Entity", 0, 1));

        /**
         * Create a new Manager instance
         */
        $this->manager = new \Centiq\RBAC\Manager($this->pdo);
    }

    /**
     * Validate and test the root role object
     */
    public function testRootRole()
    {
        /**
         * Make we have a Role Object
         */
        $this->assertInstanceOf("\\Centiq\\RBAC\\Entities\\Role", $this->manager->getRootRole());

        /**
         * Validate that the ID we inserted is what we get back
         */
        $this->assertEquals(1, $this->manager->getRootRole()->id());

        /**
         * Caldiate name
         */
        $this->assertEquals("root", $this->manager->getRootRole()->name());

        /**
         * Validate Description
         */
        $this->assertEquals("Root Entity", $this->manager->getRootRole()->description());

        /**
         * Validate l-pos
         */
        $this->assertEquals(0, $this->manager->getRootRole()->left());

        /**
         * Valdiate r-pos
         */
        $this->assertEquals(1, $this->manager->getRootRole()->right());

        /**
         * We should have no children
         */
        $this->assertEquals(0, $this->manager->getRootRole()->getChildCount());

        /**
         * Valdiate that the root node is a final node (currently, will change later in tests)
         */
        $this->assertEquals(true, $this->manager->getRootRole()->isLeaf());

        /**
         * We are not expecting children, we was never even trying :/
         */
        $this->assertEquals(0, $this->manager->getRootRole()->getChildCount());
    }


    /**
     * Setup the roles
     * @depends testRootRole
     */
    public function testFirstChildRole()
    {
        /**
         * Create a new child from root
         */
        $child = $this->manager->getRootRole()->createChild("a", "Child of root");

        /**
         * Validate that the left and right values have changed from the
         * @testRootRole call.
         */
        $root = $this->manager->getRole("root");

        /**
         * The left position of the child should be + 1 of the parents left
         */
        $this->assertEquals($root->left() + 1, $child->left());

        /**
         * The child's right position should be - 1 if the parents right position
         */
        $this->assertEquals($root->right() - 1, $child->right());

        /**
         * The root role should be able to detect if the child is actually a child
         * of a parent node
         */
        $this->assertTrue($root->isAncestorOf($child));

        /**
         * The child should be able to detect it's parent node
         */
        $this->assertTrue($child->isDescendantOf($root));

        /**
         * Validate that the root node has one child
         */
        $this->assertEquals(1, $root->getChildCount());

        /**
         * Validate the parent node is not a leaf
         */
        $this->assertFalse($root->isLeaf());

        /**
         * Validate the child is a leaf node
         */
        $this->assertTrue($child->isLeaf());
    }

    /**
     * @depends testFirstChildRole
     */
    public function testMultipleChildRoles()
    {
        /**
         * Create a new node to spawn the child nodes from.
         */
        $node = $this->manager->getRole("root");
        for ($i=0; $i <= 20; $i++)
        {
            /**
             * Create a child node
             */
            $node = $node->createChild("test_" . $i, "Child Node", $node);

            /**
             * Valdiate that that the node is a child of root
             */
            $this->assertTrue($this->manager->getRole("root")->isAncestorOf($node));
        }
    }

    /**
     * Test the root permissions
     */
    public function testRootPermission()
    {
        /**
         * Make we have a Role Object
         */
        $this->assertInstanceOf("\\Centiq\\RBAC\\Entities\\Permission", $this->manager->getRootPermission());

        /**
         * Validate that the ID we inserted is what we get back
         */
        $this->assertEquals(1, $this->manager->getRootPermission()->id());

        /**
         * Caldiate name
         */
        $this->assertEquals("root", $this->manager->getRootPermission()->name());

        /**
         * Validate Description
         */
        $this->assertEquals("Root Entity", $this->manager->getRootPermission()->description());

        /**
         * Validate l-pos
         */
        $this->assertEquals(0, $this->manager->getRootPermission()->left());

        /**
         * Valdiate r-pos
         */
        $this->assertEquals(1, $this->manager->getRootPermission()->right());

        /**
         * We should have no children
         */
        $this->assertEquals(0, $this->manager->getRootPermission()->getChildCount());

        /**
         * Valdiate that the root node is a final node (currently, will change later in tests)
         */
        $this->assertEquals(true, $this->manager->getRootPermission()->isLeaf());

        /**
         * We are not expecting children, we was never even trying :/
         */
        $this->assertEquals(0, $this->manager->getRootPermission()->getChildCount());
    }

    /**
     * Setup the roles
     * @depends testRootPermission
     */
    public function testFirstChildPermission()
    {
        /**
         * Create a new child from root
         */
        $child = $this->manager->getRootPermission()->createChild("a", "Child of root");

        /**
         * Validate that the left and right values have changed from the
         * @testRootRole call.
         */
        $root = $this->manager->getPermission("root");

        /**
         * The left position of the child should be + 1 of the parents left
         */
        $this->assertEquals($root->left() + 1, $child->left());

        /**
         * The child's right position should be - 1 if the parents right position
         */
        $this->assertEquals($root->right() - 1, $child->right());

        /**
         * The root role should be able to detect if the child is actually a child
         * of a parent node
         */
        $this->assertTrue($root->isAncestorOf($child));

        /**
         * The child should be able to detect it's parent node
         */
        $this->assertTrue($child->isDescendantOf($root));

        /**
         * Validate that the root node has one child
         */
        $this->assertEquals(1, $root->getChildCount());

        /**
         * Validate the parent node is not a leaf
         */
        $this->assertFalse($root->isLeaf());

        /**
         * Validate the child is a leaf node
         */
        $this->assertTrue($child->isLeaf());
    }

    /**
     * 
     * @depends testFirstChildRole
     */
    public function testMultipleChildPermissions()
    {
        /**
         * Create a new node to spawn the child nodes from.
         */
        $node = $this->manager->getPermission("root");
        for ($i=1; $i <= 20; $i++)
        {
            /**
             * Create a child node
             */
            $node = $node->createChild("test_" . $i, "Child Node", $node);

            /**
             * Valdiate that that the node is a child of root
             */
            $this->assertTrue($this->manager->getPermission("root")->isAncestorOf($node));
        }
    }
}