<?php

namespace JoshuaJabbour\Authorizable\Tests;

use stdClass;
use Mockery;
use PHPUnit_Framework_TestCase;
use JoshuaJabbour\Authorizable\Rule;
use JoshuaJabbour\Authorizable\Rule\Privilege;
use JoshuaJabbour\Authorizable\Rule\Restriction;

class RuleTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->privilege = new Privilege('read', Mockery::mock('Object'));
        $this->restriction = new Restriction('update', Mockery::mock('Object'));
        $this->objectClass = get_class(Mockery::mock('Object'));
    }

    public function tearDown()
    {
        Mockery::close();
    }

    public function testCanCreateRule()
    {
        $privilege = new Privilege('read', 'Object1');
        $restriction = new Restriction('update', 'Object2');

        $this->assertEquals('read', $privilege->getAction());
        $this->assertEquals('Object1', $privilege->getResource());
        $this->assertEquals('update', $restriction->getAction());
        $this->assertEquals('Object2', $restriction->getResource());
    }

    public function testCanDetermineIfMatchesAction()
    {
        $this->assertTrue($this->privilege->matchesAction('read'));
        $this->assertFalse($this->restriction->matchesAction(['read', 'delete']));
    }

    public function testCanDetermineIfMatchesResource()
    {
        $this->assertTrue($this->privilege->matchesResource(Mockery::mock('Object')));
        $this->assertTrue($this->privilege->matchesResource($this->objectClass));
        $this->assertFalse($this->privilege->matchesResource('Object'));
    }

    public function testCanDetermineIfRelevant()
    {
        $this->assertTrue($this->privilege->isRelevant('read', Mockery::mock('Object')));
        $this->assertTrue($this->privilege->isRelevant(['read', 'write'], $this->objectClass));
        $this->assertFalse($this->privilege->isRelevant('update', $this->objectClass));
    }

    public function testCanCheckRule()
    {
        $privilege = new Privilege('read', 'Object1');
        $this->assertTrue($privilege->check());
        $this->assertTrue($privilege());

        $restriction = new Restriction('update', 'Object2');
        $this->assertFalse($restriction->check());
        $this->assertFalse($restriction());
    }

    public function testCanCheckPrivilegeAgainstConditions()
    {
        $object1 = new stdClass;
        $object1->id = 1;

        $object2 = new stdClass;
        $object2->id = 2;

        $privilege = new Privilege('read', 'stdClass', function ($object) {
            return $object->id == 1;
        });

        $this->assertTrue($privilege->check($object1));
        $this->assertFalse($privilege->check($object2));
    }

    public function testCanCheckRestrictionAgainstConditions()
    {
        $object1 = new stdClass;
        $object1->id = 1;

        $object2 = new stdClass;
        $object2->id = 2;

        $restriction = new Restriction('read', 'stdClass', function ($object) {
            return $object->id == 1;
        });

        $this->assertFalse($restriction->check($object1));
        $this->assertTrue($restriction->check($object2));
    }
}
