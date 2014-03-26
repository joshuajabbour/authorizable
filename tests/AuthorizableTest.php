<?php

namespace JoshuaJabbour\Authorizable\Tests;

use stdClass;
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

    public function testCanEvaluateRulesWithAction()
    {
        $this->auth->allow('create', 'User');
        $this->auth->allow('read', 'User');
        $this->auth->deny('update', 'User');

        $this->assertTrue($this->auth->can('create', 'User'));
        $this->assertFalse($this->auth->can('update', 'User'));
        $this->assertFalse($this->auth->can('undefined', 'User'));

        $this->assertFalse($this->auth->cannot('read', 'User'));
        $this->assertTrue($this->auth->cannot('update', 'User'));
        $this->assertTrue($this->auth->cannot('undefined', 'User'));
    }

    public function testCanEvaluateRulesWithObject()
    {
        $object1 = new stdClass;
        $object1->id = 1;

        $object2 = new stdClass;
        $object2->id = 2;

        $this->auth->allow('read', 'stdClass', function ($object) {
            return $object->id == 1;
        });

        $this->auth->deny('update', 'stdClass', function ($object) {
            return $object->id != 1;
        });

        $this->assertTrue($this->auth->can('read', $object1));
        $this->assertFalse($this->auth->can('read', $object2));

        $this->assertTrue($this->auth->can('update', $object1));
        $this->assertFalse($this->auth->can('update', $object2));

        $this->assertFalse($this->auth->cannot('update', $object1));
        $this->assertTrue($this->auth->cannot('update', $object2));
    }
}
