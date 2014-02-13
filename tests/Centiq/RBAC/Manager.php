<?php

class ManagerTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        /**
         * Create a PDO Object
         */
        $this->pdo = new PDO('mysql:host=localhost;dbname=rbac_units', "root", "root");

        /**
         * Set exceptions on
         */
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        /**
         * Install tables
         */
        if(!$this->pdo->exec(file_get_contents(__DIR__ . "/../../../sql/mysql.sql")))
        {
            throw new Exception("Unable to import sqlite DB");
        }

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
        $statement = $this->pdo->prepare("INSERT INTO rbac_roles (`title`, `description`, `left`, `right`) VALUES (?,?,?,?)");
        $statement->execute(array("root", "Root Entity", 0, 1));

        $statement = $this->pdo->prepare("INSERT INTO rbac_permissions (`title`, `description`, `left`, `right`) VALUES (?,?,?,?)");
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
         * Caldiate title
         */
        $this->assertEquals("root", $this->manager->getRootRole()->title());

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
        $this->assertEquals(0, $this->manager->getRootRole()->childrenLength());

        /**
         * Valdiate that the root node is a final node (currently, will change later in tests)
         */
        $this->assertEquals(true, $this->manager->getRootRole()->isLeaf());

        /**
         * We are not expecting children, we was never even trying :/
         */
        $this->assertEquals(0, $this->manager->getRootRole()->childrenLength());
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
        $this->manager->getRootRole()->update();

        /**
         * The left position of the child should be + 1 of the parents left
         */
        $this->assertEquals($this->manager->getRootRole()->left() + 1, $child->left());

        /**
         * The child's right position should be - 1 if the parents right position
         */
        $this->assertEquals($this->manager->getRootRole()->right() - 1, $child->right());

        /**
         * The root role should be able to detect if the child is actually a child
         * of a parent node
         */
        $this->assertTrue($this->manager->getRootRole()->isAncestorOf($child));

        /**
         * The child should be able to detect it's parent node
         */
        $this->assertTrue($child->isDescendantOf($this->manager->getRootRole()));

        /**
         * Validate that the root node has one child
         */
        $this->assertEquals(1, $this->manager->getRootRole()->childrenLength());

        /**
         * Validate the parent node is not a leaf
         */
        $this->assertFalse($this->manager->getRootRole()->isLeaf());

        /**
         * Validate the child is a leaf node
         */
        $this->assertTrue($child->isLeaf());
        
    }

    /**
     * 
     * @depends testFirstChildRole
     */
    public function testMultipleChildNodes()
    {
        /**
         * Create a new node to spawn the child nodes from.
         */
        $node = null;
        for ($i=0; $i <= 20; $i++)
        {
            /**
             * Create a child node
             */
            $node = $this->manager->createRole("test_" . $i, "Child Node", $node);

            /**
             * Update the root node as the set layout has changed
             */
            $this->manager->getRootRole()->update();

            /**
             * Valdiate that that the node is a child of root
             */
            $this->assertTrue($this->manager->getRootRole()->isAncestorOf($node));
        }
    }
}