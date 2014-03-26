<?php

namespace JoshuaJabbour\Authorizable\Tests;

use PHPUnit_Framework_TestCase;
use JoshuaJabbour\Authorizable\Authorizable;
use JoshuaJabbour\Authorizable\Rule;
use JoshuaJabbour\Authorizable\Privilege;
use JoshuaJabbour\Authorizable\Restriction;

class AuthorizableTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->auth = new Authorizable;
    }

    public function tearDown()
    {
        //
    }

    public function testCanStoreNewPrivilege()
    {
        $rule1 = $this->auth->allow('read', 'User');
        $rule2 = $this->auth->deny('update', 'User');
        $this->assertCount(2, $this->auth->getRules());
        // $this->assertContains($rule, $this->auth->getRules());
        // $this->assertInstanceOf('JoshuaJabbour\Authorizable\Privilege', $rule1);
    }

    public function testCanStoreNewRestriction()
    {
        $rule1 = $this->auth->deny('create', 'User');
        $rule2 = $this->auth->allow('read', 'User');
        $this->assertCount(2, $this->auth->getRules());
        // $this->assertContains($rule, $this->auth->getRules());
        // $this->assertInstanceOf('JoshuaJabbour\Authorizable\Restriction', $rule1);
    }

    public function testCanFetchAllRulesForAction()
    {
        $this->auth->deny('create', 'User');
        $this->auth->allow('read', 'User');
        $this->auth->deny('update', 'User');
        $this->auth->deny('delete', 'User');
        $this->auth->deny('read', 'User');

        $this->assertCount(2, $this->auth->getRulesFor('read', 'User'));
        $this->assertCount(5, $this->auth->getRules());
    }
}
