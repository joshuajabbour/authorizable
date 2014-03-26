<?php

namespace JoshuaJabbour\Authorizable\Tests;

use Mockery;
use PHPUnit_Framework_TestCase;
use JoshuaJabbour\Authorizable\Rule;
use JoshuaJabbour\Authorizable\Privilege;
use JoshuaJabbour\Authorizable\Restriction;

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

    public function testCanCreateRules()
    {
        $rule1 = new Privilege('read', 'Object1');
        $rule2 = new Restriction('update', 'Object2');

        $this->assertEquals('read', $rule1->getAction());
        $this->assertEquals('Object1', $rule1->getResource());
        $this->assertEquals('update', $rule2->getAction());
        $this->assertEquals('Object2', $rule2->getResource());
    }

    public function testMatchesAction()
    {
        $this->assertTrue($this->privilege->matchesAction('read'));
        $this->assertFalse($this->restriction->matchesAction(['read', 'delete']));
    }

    public function testMatchesResource()
    {
        $this->assertTrue($this->privilege->matchesResource(Mockery::mock('Object')));
        $this->assertTrue($this->privilege->matchesResource($this->objectClass));
        $this->assertFalse($this->privilege->matchesResource('Object'));
    }

    public function testCanDetermineRelevance()
    {
        $this->assertTrue($this->privilege->isRelevant('read', Mockery::mock('Object')));
        $this->assertTrue($this->privilege->isRelevant(['read', 'write'], $this->objectClass));
        $this->assertFalse($this->privilege->isRelevant('update', $this->objectClass));
    }
}
